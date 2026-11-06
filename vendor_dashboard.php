<?php
require_once 'db.php'; require_role('vendor');
$id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT COUNT(*) AS pending FROM orders WHERE vendor_id=? AND status='pending'"); $stmt->bind_param('i',$id); $stmt->execute(); $pending=(int)$stmt->get_result()->fetch_assoc()['pending'];
$stmt = $conn->prepare('SELECT COUNT(*) AS items FROM menu_items WHERE vendor_id=?'); $stmt->bind_param('i',$id); $stmt->execute(); $items=(int)$stmt->get_result()->fetch_assoc()['items'];
$stmt = $conn->prepare("SELECT COALESCE(SUM(total),0) AS sales FROM orders WHERE vendor_id=? AND status='completed' AND DATE(created_at)=CURDATE()"); $stmt->bind_param('i',$id); $stmt->execute(); $sales=$stmt->get_result()->fetch_assoc()['sales'];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Vendor workspace | Mama Ntilie</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><a class="brand" href="index.php">🍲 Mama Ntilie</a><span>Vendor workspace</span><a href="logout.php">Sign out</a></header>
<main class="page-wrap"><span class="eyebrow">YOUR BUSINESS AT A GLANCE</span><h1>Welcome, <?= e(current_user_name()) ?>.</h1><p class="muted">Manage today's menu, orders and business records.</p>
<div class="stat-grid"><div class="stat-card"><span>Menu items</span><strong><?= $items ?></strong><small>Meals listed by you</small></div><div class="stat-card"><span>Pending orders</span><strong><?= $pending ?></strong><small>Need your attention</small></div><div class="stat-card"><span>Today's completed sales</span><strong><?= money($sales) ?></strong><small>Completed orders only</small></div></div>
<div class="action-grid"><a class="action-card" href="menu.php"><b>🍲 Manage menu</b><span>Add meals, update availability and prices →</span></a><a class="action-card" href="vendor_orders.php"><b>📦 Customer orders <i class="badge"><?= $pending ?></i></b><span>Review and update incoming orders →</span></a><a class="action-card" href="expenses.php"><b>🧾 Business expenses</b><span>Record ingredient and operating costs →</span></a><a class="action-card" href="report.php"><b>📊 Sales report</b><span>Review sales, expenses and net result →</span></a></div></main></body></html>
