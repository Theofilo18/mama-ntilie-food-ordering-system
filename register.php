<?php
require_once 'db.php';
if (!empty($_SESSION['user_id'])) redirect(($_SESSION['role'] ?? '') === 'vendor' ? 'vendor_dashboard.php' : 'customer_dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $location = trim($_POST['location'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || !in_array($role, ['customer','vendor'], true)) {
        $error = 'Enter a name, valid email, password of at least 8 characters, and account type.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name,email,password,role,location) VALUES (?,?,?,?,?)');
            $stmt->bind_param('sssss', $name, $email, $hash, $role, $location);
            $stmt->execute();
            set_flash('success', 'Account created. You can now sign in.');
            redirect('login.php');
        } catch (mysqli_sql_exception $ex) {
            $error = $ex->getCode() === 1062 ? 'That email is already registered.' : 'We could not create your account. Please try again.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Create account | Mama Ntilie</title><link rel="stylesheet" href="style.css"></head>
<body class="auth-bg"><main class="auth-card"><a class="back-link" href="index.php">← Home</a><span class="eyebrow">WELCOME TO MAMA NTILIE</span><h1>Create your account</h1><p class="muted">Join as a customer or a food vendor.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Full name<input name="name" value="<?= e($_POST['name'] ?? '') ?>" autocomplete="name" required></label>
<label>Email address<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email" required></label>
<label>Password<input type="password" name="password" minlength="8" autocomplete="new-password" required><small>Use at least 8 characters.</small></label>
<label>Account type<select name="role"><option value="customer">Customer</option><option value="vendor" <?= ($_POST['role'] ?? '') === 'vendor' ? 'selected' : '' ?>>Food vendor</option></select></label>
<label class="full">Location <span class="muted">(optional)</span><input name="location" placeholder="e.g. Kigamboni" value="<?= e($_POST['location'] ?? '') ?>"></label>
<button class="btn btn-primary full" type="submit">Create account</button>
</form><p class="form-foot">Already registered? <a href="login.php">Sign in</a></p></main></body></html>
