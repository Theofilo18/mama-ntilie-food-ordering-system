<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Only administrators can access this page.
require_role('admin');

// Total registered users.
$totalUsers = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
)->fetch_assoc()['total'];

// Total vendors.
$totalVendors = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'vendor'"
)->fetch_assoc()['total'];

// Vendors awaiting verification.
$pendingVerifications = $conn->query(
    "SELECT COUNT(*) AS total
     FROM vendor_verifications
     WHERE verification_status = 'pending'"
)->fetch_assoc()['total'];

// Open complaints.
$openComplaints = $conn->query(
    "SELECT COUNT(*) AS total
     FROM complaints
     WHERE status IN ('open', 'under_review')"
)->fetch_assoc()['total'];

// Suspended and blocked accounts.
$suspendedAccounts = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE account_status IN ('suspended', 'blocked')"
)->fetch_assoc()['total'];

// Latest administrative activities.
$recentActivities = $conn->query(
    "SELECT admin_logs.action,
            admin_logs.reason,
            admin_logs.created_at,
            users.name AS admin_name
     FROM admin_logs
     INNER JOIN users
        ON admin_logs.admin_id = users.id
     ORDER BY admin_logs.created_at DESC
     LIMIT 5"
);

$flash = get_flash();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Mama Ntilie</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="container">

        <h1>Admin Dashboard</h1>

        <p>
            Welcome, <?= e(current_user_name()) ?>.
            Manage the Mama Ntilie platform from here.
        </p>

        <?php if ($flash): ?>
            <div class="alert <?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="dashboard-stats">

            <article class="stat-card">
                <h3>Total Users</h3>
                <p><?= (int) $totalUsers ?></p>
            </article>

            <article class="stat-card">
                <h3>Total Vendors</h3>
                <p><?= (int) $totalVendors ?></p>
            </article>

            <article class="stat-card">
                <h3>Pending Verifications</h3>
                <p><?= (int) $pendingVerifications ?></p>
            </article>

            <article class="stat-card">
                <h3>Open Complaints</h3>
                <p><?= (int) $openComplaints ?></p>
            </article>

            <article class="stat-card">
                <h3>Suspended Accounts</h3>
                <p><?= (int) $suspendedAccounts ?></p>
            </article>

        </section>

        <section class="admin-section">

            <h2>Recent Administrative Activity</h2>

            <?php if ($recentActivities->num_rows > 0): ?>

                <table>
                    <thead>
                        <tr>
                            <th>Administrator</th>
                            <th>Action</th>
                            <th>Reason</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while ($activity = $recentActivities->fetch_assoc()): ?>

                            <tr>
                                <td><?= e($activity['admin_name']) ?></td>
                                <td><?= e($activity['action']) ?></td>
                                <td><?= e($activity['reason']) ?></td>
                                <td><?= e($activity['created_at']) ?></td>
                            </tr>

                        <?php endwhile; ?>

                    </tbody>
                </table>

            <?php else: ?>

                <p>No administrative activities recorded yet.</p>

            <?php endif; ?>

        </section>

        <section class="admin-section">

            <h2>Administration</h2>

            <nav class="admin-navigation">
                <a href="admin_vendors.php">Manage Vendors</a>
                <a href="index.php">Visit Website</a>
                <a href="logout.php">Logout</a>
            </nav>

        </section>

    </main>

</body>
</html>