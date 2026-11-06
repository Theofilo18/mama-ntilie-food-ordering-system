<?php
require_once 'db.php'; require_role('vendor');
$vendor_id=(int)$_SESSION['user_id']; $error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf(); $action=$_POST['action']??'';
    if($action==='add') {
        $name=trim($_POST['name']??''); $description=trim($_POST['description']??'');
        $price=filter_input(INPUT_POST,'price',FILTER_VALIDATE_FLOAT); $stock=filter_input(INPUT_POST,'stock',FILTER_VALIDATE_INT);
        if($name==='' || $price===false || $price<=0 || $stock===false || $stock<0) $error='Enter a meal name, a price above zero and a stock quantity of zero or more.';
        else {
            $imagePath=null;
            if(!empty($_FILES['image']['name'])) {
                $file=$_FILES['image'];
                $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                if($file['error']!==UPLOAD_ERR_OK || !isset($allowed[$mime]) || $file['size']>2*1024*1024) $error='Upload a JPG, PNG or WEBP image smaller than 2 MB.';
                else {
                    if(!is_dir(__DIR__.'/uploads')) mkdir(__DIR__.'/uploads',0755,true);
                    $filename=bin2hex(random_bytes(12)).'.'.$allowed[$mime];
                    if(move_uploaded_file($file['tmp_name'],__DIR__.'/uploads/'.$filename)) $imagePath='uploads/'.$filename;
                    else $error='Image upload failed. Check the uploads folder permissions.';
                }
            }
            if(!$error) {
                $stmt=$conn->prepare('INSERT INTO menu_items (vendor_id,name,description,price,stock,image) VALUES (?,?,?,?,?,?)');
                $stmt->bind_param('issdis',$vendor_id,$name,$description,$price,$stock,$imagePath);
                $stmt->execute(); set_flash('success','Meal added to your menu.'); redirect('menu.php');
            }
        }
    } elseif($action==='availability') {
        $item_id=(int)($_POST['item_id']??0); $available=(int)($_POST['is_available']??0);
        $stmt=$conn->prepare('UPDATE menu_items SET is_available=? WHERE id=? AND vendor_id=?');
        $stmt->bind_param('iii',$available,$item_id,$vendor_id); $stmt->execute(); set_flash('success','Meal availability updated.'); redirect('menu.php');
    } elseif($action==='delete') {
        $item_id=(int)($_POST['item_id']??0);
        $stmt=$conn->prepare('UPDATE menu_items SET is_available=0 WHERE id=? AND vendor_id=?');
        $stmt->bind_param('ii',$item_id,$vendor_id); $stmt->execute(); set_flash('success','Meal removed from your public menu. Existing order history is preserved.'); redirect('menu.php');
    }
}
$stmt=$conn->prepare('SELECT * FROM menu_items WHERE vendor_id=? ORDER BY created_at DESC'); $stmt->bind_param('i',$vendor_id); $stmt->execute(); $items=$stmt->get_result(); $flash=get_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Manage menu | Mama Ntilie</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="vendor_dashboard.php">🍲 Mama Ntilie</a><a href="logout.php">Sign out</a></header><main class="page-wrap"><a class="back-link" href="vendor_dashboard.php">← Dashboard</a><span class="eyebrow">YOUR FOOD, YOUR MENU</span><h1>Manage your meals</h1><?php if($flash): ?><div class="alert success"><?= e($flash['message']) ?></div><?php endif; ?><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<section class="panel"><h2>Add a meal</h2><form method="post" enctype="multipart/form-data" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><label>Meal name<input name="name" placeholder="e.g. Pilau beef" required></label><label>Price (TZS)<input type="number" name="price" min="1" step="1" required></label><label>Quantity available<input type="number" name="stock" min="0" value="1" required></label><label>Meal photo <small>JPG, PNG or WEBP · max 2 MB</small><input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></label><label class="full">Description <span class="muted">(optional)</span><textarea name="description" rows="3" placeholder="Describe the meal and what comes with it"></textarea></label><button class="btn btn-primary full">Add meal</button></form></section>
<h2 class="section-heading">Your menu</h2><div class="table-wrap"><table><thead><tr><th>Meal</th><th>Price</th><th>Stock</th><th>Visibility</th><th>Actions</th></tr></thead><tbody><?php if($items->num_rows): while($it=$items->fetch_assoc()): ?><tr><td><?= e($it['name']) ?></td><td><?= money($it['price']) ?></td><td><?= (int)$it['stock'] ?></td><td><?= $it['is_available']?'Visible':'Hidden' ?></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>"><input type="hidden" name="action" value="availability"><input type="hidden" name="is_available" value="<?= $it['is_available']?0:1 ?>"><button class="btn btn-small"><?= $it['is_available']?'Hide':'Show' ?></button></form> <form method="post" class="inline-form" onsubmit="return confirm('Remove this meal from your public menu?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-small btn-danger">Remove</button></form></td></tr><?php endwhile; else: ?><tr><td colspan="5">You have not added any meals yet.</td></tr><?php endif; ?></tbody></table></div></main></body></html>
