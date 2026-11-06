<?php
require_once 'db.php'; require_role('vendor'); $vendor_id=(int)$_SESSION['user_id']; $error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf(); $order_id=(int)($_POST['order_id']??0); $status=$_POST['status']??'';
    if(!in_array($status,['pending','preparing','completed','cancelled'],true)) $error='Invalid order status.';
    else {
        $stmt=$conn->prepare('UPDATE orders SET status=? WHERE id=? AND vendor_id=?');
        $stmt->bind_param('sii',$status,$order_id,$vendor_id); $stmt->execute();
        set_flash('success','Order status updated.'); redirect('vendor_orders.php');
    }
}
$stmt=$conn->prepare('SELECT o.id,o.customer_id,o.quantity,o.total,o.status,o.created_at,u.name AS customer_name,m.name AS meal_name FROM orders o JOIN users u ON u.id=o.customer_id JOIN menu_items m ON m.id=o.menu_id WHERE o.vendor_id=? ORDER BY o.created_at DESC');
$stmt->bind_param('i',$vendor_id); $stmt->execute(); $orders=$stmt->get_result(); $flash=get_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Customer orders | Mama Ntilie</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="vendor_dashboard.php">🍲 Mama Ntilie</a><a href="logout.php">Sign out</a></header><main class="page-wrap"><a class="back-link" href="vendor_dashboard.php">← Dashboard</a><span class="eyebrow">KEEP SERVICE MOVING</span><h1>Customer orders</h1><?php if($flash): ?><div class="alert success"><?= e($flash['message']) ?></div><?php endif; ?><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if($orders->num_rows): ?><div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Meal</th><th>Qty</th><th>Total</th><th>Status</th><th>Update</th><th>Date</th></tr></thead><tbody><?php while($o=$orders->fetch_assoc()): ?><tr><td>#<?= (int)$o['id'] ?></td><td><?= e($o['customer_name']) ?></td><td><?= e($o['meal_name']) ?></td><td><?= (int)$o['quantity'] ?></td><td><?= money($o['total']) ?></td><td><span class="status <?= e($o['status']) ?>"><?= e(ucfirst($o['status'])) ?></span></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>"><select name="status"><?php foreach(['pending','preparing','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-small">Save</button></form></td><td><?= e($o['created_at']) ?></td></tr><?php endwhile; ?></tbody></table></div><?php else: ?><div class="empty-state">No orders have arrived yet.</div><?php endif; ?></main></body></html>
