<?php
require_once 'db.php';
$pageTitle = 'Fresh local food in Kigamboni';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mama Ntilie | Fresh food, close to home</title><link rel="stylesheet" href="style.css">
</head>
<body class="landing">
<header class="site-header"><a class="brand" href="index.php">🍲 Mama Ntilie</a><nav>
  <a href="about.php">About</a><a href="login.php">Sign in</a><a class="nav-cta" href="register.php">Join us</a>
</nav></header>
<main class="hero">
  <div class="hero-copy">
    <span class="eyebrow">KIGAMBONI • DAR ES SALAAM</span>
    <h1>Good food.<br><em>Made nearby.</em></h1>
    <p>Discover meals prepared by local food vendors, check what is available, and place your order in a few simple steps.</p>
    <div class="hero-actions"><a class="btn btn-primary" href="register.php">Get started</a><a class="btn btn-ghost" href="login.php">I already have an account</a></div>
    <div class="hero-note">Freshly prepared by local vendors · Prices in Tanzanian shillings</div>
  </div>
  <div class="hero-card"><div class="hero-card-image"></div><div class="hero-card-caption"><span>Made with care</span><strong>Local flavours, every day.</strong></div></div>
</main>
<footer class="site-footer">© <?= date('Y') ?> Mama Ntilie · Kigamboni, Tanzania</footer>
</body></html>
