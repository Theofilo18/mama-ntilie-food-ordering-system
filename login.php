<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/*
|--------------------------------------------------------------------------
| DASHBOARD REDIRECTION
|--------------------------------------------------------------------------
*/

function dashboard_for_role(string $role): string
{
    return match ($role) {
        'admin' => 'admin_dashboard.php',
        'vendor' => 'vendor_dashboard.php',
        'customer' => 'customer_dashboard.php',
        default => 'login.php',
    };
}

/*
|--------------------------------------------------------------------------
| CHECK EXISTING LOGIN SESSION
|--------------------------------------------------------------------------
*/

// If a user is already logged in, verify their current account status.
if (!empty($_SESSION['user_id'])) {

    $currentRole = $_SESSION['role'] ?? '';

    if (in_array($currentRole, ['admin', 'vendor', 'customer'], true)) {

        // Checks session, database role, and account status.
        require_role($currentRole);

        // Only active users reach their dashboards.
        redirect(dashboard_for_role($currentRole));
    }

    // Clear an invalid session role.
    unset(
        $_SESSION['user_id'],
        $_SESSION['user_name'],
        $_SESSION['role'],
        $_SESSION['account_status']
    );

    session_regenerate_id(true);
}

/*
|--------------------------------------------------------------------------
| LOGIN PROCESS
|--------------------------------------------------------------------------
*/

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Protect the login form against CSRF attacks.
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!is_string($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Email or password is incorrect.';

    } else {

        // Find the account using a prepared SQL statement.
        $stmt = $conn->prepare(
            'SELECT id, name, email, password, role, account_status
             FROM users
             WHERE email = ?
             LIMIT 1'
        );

        $stmt->bind_param('s', $email);

        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        // Verify the submitted password against the stored password hash.
        if ($user && password_verify($password, $user['password'])) {

            // Only active accounts can log in.
            if ($user['account_status'] !== 'active') {

                $error = 'Your account is suspended or blocked. Please contact support.';

            } else {

                // Regenerate the session ID after successful authentication.
                session_regenerate_id(true);

                // Store authenticated user information.
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['account_status'] = $user['account_status'];

                // Send the user to the appropriate dashboard.
                redirect(dashboard_for_role($user['role']));
            }

        } else {

            $error = 'Email or password is incorrect.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

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

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrf_token()) ?>"
            >

            <label class="full">

                Email address

                <input
                    type="email"
                    name="email"
                    autocomplete="email"
                    required
                >

            </label>

            <label class="full">

                Password

                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >

            </label>

            <button type="submit" class="btn btn-primary full">

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