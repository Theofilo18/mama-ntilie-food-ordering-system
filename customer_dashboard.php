<?php
require_once 'db.php'; require_role('customer');
$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM orders WHERE customer_id=?');
$stmt->bind_param('i', $_SESSION['user_id']); $stmt->execute(); $orders = (int)$stmt->get_result()->fetch_assoc()['total'];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Customer home | Mama Ntilie</title><link rel="stylesheet" href="style.css"></head><body>
<header class="topbar"><a class="brand" href="index.php">🍲 Mama Ntilie</a><span>Customer space</span><a href="logout.php">Sign out</a></header>
<main class="page-wrap"><span class="eyebrow">YOUR FOOD JOURNEY</span><h1>Karibu, <?= e(current_user_name()) ?>!</h1><p class="muted">Find something delicious from a local vendor.</p>
<div class="stat-grid"><div class="stat-card"><span>Your orders</span><strong><?= $orders ?></strong><small>Orders placed so far</small></div><div class="stat-card"><span>Local vendors</span><strong>🍽️</strong><small>Discover food around Kigamboni</small></div></div>
<div class="action-grid"><a class="action-card" href="vendors.php"><b>🍛 Explore vendors</b><span>Browse menus and available meals →</span></a><a class="action-card" href="orders.php"><b>📦 My orders</b><span>Track the status of your orders →</span></a></div></main></body></html>
