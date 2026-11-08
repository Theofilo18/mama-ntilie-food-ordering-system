<?php
require_once 'db.php';

// Redirect already authenticated users to their correct dashboard.
function dashboard_for_role(string $role): string
{
    return match ($role) {
        'admin' => 'admin_dashboard.php',
        'vendor' => 'vendor_dashboard.php',
        default => 'customer_dashboard.php',
    };
}

if (!empty($_SESSION['user_id'])) {
    redirect(dashboard_for_role($_SESSION['role'] ?? 'customer'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        'SELECT id, name, email, password, role, account_status
         FROM users
         WHERE email = ?
         LIMIT 1'
    );

    $stmt->bind_param('s', $email);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {

        if ($user['account_status'] !== 'active') {
            $error = 'Your account is suspended or blocked. Please contact support.';
        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['account_status'] = $user['account_status'];

            redirect(dashboard_for_role($user['role']));
        }

    } else {
        $error = 'Email or password is incorrect.';
    }
}

$flash = get_flash();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sign in | Mama Ntilie</title>

    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-bg">

    <main class="auth-card">

        <a class="back-link" href="index.php">&larr; Home</a>

        <span class="eyebrow">GOOD TO SEE YOU AGAIN</span>

        <h1>Sign in</h1>

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form-grid">

            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <label class="full">
                Email address
                <input type="email" name="email" autocomplete="email" required>
            </label>

            <label class="full">
                Password
                <input type="password" name="password" autocomplete="current-password" required>
            </label>

            <button class="btn btn-primary full">
                Sign in
            </button>

        </form>

        <p class="form-foot">
            New here?
            <a href="register.php">Create an account</a>
        </p>

    </main>

</body>

</html>