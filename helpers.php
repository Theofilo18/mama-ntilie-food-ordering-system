<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SESSION MANAGEMENT
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);

    session_start();
}

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config.php';

/*
|--------------------------------------------------------------------------
| SECURITY AND OUTPUT FUNCTIONS
|--------------------------------------------------------------------------
*/

// Safely display data in HTML.
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect users to another page.
function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

/*
|--------------------------------------------------------------------------
| USER AUTHENTICATION AND AUTHORIZATION
|--------------------------------------------------------------------------
*/

// Check whether a user is logged in and has the required role.
function require_role(string $role): void
{
    global $conn;

    // Check login session.
    if (empty($_SESSION['user_id'])) {
        set_flash('error', 'Please sign in to continue.');
        redirect('login.php');
    }

    // Check session role.
    if (($_SESSION['role'] ?? '') !== $role) {
        set_flash('error', 'You are not authorized to access this page.');
        redirect('login.php');
    }

    // Retrieve the latest account information.
    $userId = (int) $_SESSION['user_id'];

    $stmt = $conn->prepare(
        'SELECT role, account_status
         FROM users
         WHERE id = ?
         LIMIT 1'
    );

    $stmt->bind_param('i', $userId);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    // Check whether the account still exists and is active.
    if (
        !$user ||
        $user['role'] !== $role ||
        $user['account_status'] !== 'active'
    ) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        redirect('login.php');
    }
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGES
|--------------------------------------------------------------------------
*/

// Store a temporary message.
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Retrieve and remove a temporary message.
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    return $flash;
}

/*
|--------------------------------------------------------------------------
| CSRF PROTECTION
|--------------------------------------------------------------------------
*/

// Generate a security token for forms.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Verify submitted form tokens.
function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';

    if (
        !is_string($submitted) ||
        !hash_equals(csrf_token(), $submitted)
    ) {
        http_response_code(419);

        exit(
            'Your form session expired. Go back, refresh the page, and try again.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| GENERAL HELPER FUNCTIONS
|--------------------------------------------------------------------------
*/

// Format financial amounts in Tanzanian shillings.
function money(float|int|string|null $amount): string
{
    return 'TZS ' . number_format((float) $amount, 0);
}

// Get the currently logged-in user's name.
function current_user_name(): string
{
    return $_SESSION['user_name'] ?? 'Guest';
}