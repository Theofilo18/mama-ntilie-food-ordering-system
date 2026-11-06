<?php
require_once 'db.php'; require_role('customer');
$vendor_id = filter_input(INPUT_GET, 'vendor_id', FILTER_VALIDATE_INT) ?: 0;
$stmt=$conn->prepare("SELECT id,name,location FROM users WHERE id=? AND role='vendor'"); $stmt->bind_param('i',$vendor_id); $stmt->execute(); $vendor=$stmt->get_result()->fetch_assoc();
if (!$vendor) { http_response_code(404); exit('Vendor not found.'); }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $menu_id=(int)($_POST['menu_id']??0); $quantity=filter_input(INPUT_POST,'quantity',FILTER_VALIDATE_INT);
    if (!$quantity || $quantity < 1 || $quantity > 30) $error='Choose a quantity between 1 and 30.';
    else {
        try {
            $conn->begin_transaction();
            $stmt=$conn->prepare('SELECT id,price,stock FROM menu_items WHERE id=? AND vendor_id=? AND is_available=1 FOR UPDATE');
            $stmt->bind_param('ii',$menu_id,$vendor_id); $stmt->execute(); $item=$stmt->get_result()->fetch_assoc();
            if (!$item || (int)$item['stock'] < $quantity) throw new RuntimeException('That meal is unavailable in the requested quantity.');
            $total=(float)$item['price']*$quantity; $customer_id=(int)$_SESSION['user_id'];
            $stmt=$conn->prepare("INSERT INTO orders (customer_id,vendor_id,menu_id,quantity,total,status) VALUES (?,?,?,?,?,'pending')");
            $stmt->bind_param('iiiid',$customer_id,$vendor_id,$menu_id,$quantity,$total); $stmt->execute();
            $stmt=$conn->prepare('UPDATE menu_items SET stock=stock-? WHERE id=? AND vendor_id=?');
            $stmt->bind_param('iii',$quantity,$menu_id,$vendor_id); $stmt->execute();
            $conn->commit(); set_flash('success','Your order has been placed. The vendor will update its status.'); redirect('orders.php');
        } catch (Throwable $ex) {
            $conn->rollback(); $error=$ex instanceof RuntimeException ? $ex->getMessage() : 'We could not place your order. Please try again.';
        }
    }
}
$stmt=$conn->prepare('SELECT id,name,description,price,stock,image FROM menu_items WHERE vendor_id=? AND is_available=1 AND stock>0 ORDER BY name'); $stmt->bind_param('i',$vendor_id); $stmt->execute(); $menu=$stmt->get_result();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($vendor['name']) ?> menu | Mama Ntilie</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="vendors.php">🍲 Mama Ntilie</a><a href="orders.php">My orders</a></header><main class="page-wrap"><a class="back-link" href="vendors.php">← Vendors</a><span class="eyebrow">TODAY'S MENU</span><h1><?= e($vendor['name']) ?></h1><p class="muted">📍 <?= e($vendor['location'] ?: 'Kigamboni') ?></p><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><div class="meal-grid">
<?php if($menu->num_rows): while($item=$menu->fetch_assoc()): ?><article class="meal-card"><?php if($item['image']): ?><img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>"><?php else: ?><div class="meal-placeholder">🍲</div><?php endif; ?><div class="meal-content"><h2><?= e($item['name']) ?></h2><p><?= e($item['description'] ?? '') ?></p><strong><?= money($item['price']) ?></strong><small><?= (int)$item['stock'] ?> available</small><form method="post" class="order-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="menu_id" value="<?= (int)$item['id'] ?>"><label>Quantity<input type="number" name="quantity" min="1" max="<?= min(30,(int)$item['stock']) ?>" value="1" required></label><button class="btn btn-primary">Place order</button></form></div></article><?php endwhile; else: ?><div class="empty-state">No meals are available right now. Please check back later.</div><?php endif; ?></div></main></body></html>
