<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Only administrators can access this page.
require_role('admin');

// Retrieve vendors and their verification information.
$stmt = $conn->prepare(
    "SELECT
        u.id,
        u.name,
        u.email,
        u.location,
        u.account_status,
        u.created_at,
        COALESCE(vv.business_name, 'Not submitted') AS business_name,
        COALESCE(vv.verification_status, 'not_submitted') AS verification_status
     FROM users u
     LEFT JOIN vendor_verifications vv
        ON vv.vendor_id = u.id
     WHERE u.role = 'vendor'
     ORDER BY u.created_at DESC"
);

$stmt->execute();
$vendors = $stmt->get_result();

$flash = get_flash();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Vendors | Mama Ntilie</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .action-buttons form {
            margin: 0;
        }

        .action-buttons button,
        .action-buttons a {
            display: inline-block;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        .btn-view {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-approve {
            background: #16a34a;
            color: white;
        }

        .btn-suspend {
            background: #dc2626;
            color: white;
        }

        .btn-restore {
            background: #2563eb;
            color: white;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 5px;
            background: #e2e8f0;
            font-size: 12px;
        }
    </style>

</head>

<body>

<main class="container">

    <h1>Manage Vendors</h1>

    <p>Review vendor accounts and monitor their verification status.</p>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <p>
        <a href="admin_dashboard.php">&larr; Back to Dashboard</a>
    </p>

    <div class="admin-section">

        <h2>Registered Vendors</h2>

        <?php if ($vendors->num_rows > 0): ?>

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Email</th>
                        <th>Business</th>
                        <th>Verification</th>
                        <th>Account Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($vendor = $vendors->fetch_assoc()): ?>

                        <tr>

                            <td><?= (int) $vendor['id'] ?></td>

                            <td><?= e($vendor['name']) ?></td>

                            <td><?= e($vendor['email']) ?></td>

                            <td><?= e($vendor['business_name']) ?></td>

                            <td>
                                <span class="status-badge">
                                    <?= e($vendor['verification_status']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="status-badge">
                                    <?= e($vendor['account_status']) ?>
                                </span>
                            </td>

                            <td><?= e($vendor['created_at']) ?></td>

                            <td>
                                <div class="action-buttons">

                                    <a
                                        class="btn-view"
                                        href="admin_vendor_view.php?id=<?= (int) $vendor['id'] ?>"
                                    >
                                        View
                                    </a>

                                    <?php if (
                                        in_array(
                                            $vendor['verification_status'],
                                            ['contact_verified', 'business_verified'],
                                            true
                                        )
                                    ): ?>

                                        <form method="post" action="admin_vendor_action.php">

                                            <input type="hidden" name="csrf_token"
                                                value="<?= e(csrf_token()) ?>">

                                            <input type="hidden" name="vendor_id"
                                                value="<?= (int) $vendor['id'] ?>">

                                            <input type="hidden" name="action" value="approve">

                                            <button class="btn-approve" type="submit">
                                                Approve
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                    <?php if ($vendor['account_status'] === 'active'): ?>

                                        <form method="post" action="admin_vendor_action.php"
                                            onsubmit="return confirm('Are you sure you want to suspend this vendor?');">

                                            <input type="hidden" name="csrf_token"
                                                value="<?= e(csrf_token()) ?>">

                                            <input type="hidden" name="vendor_id"
                                                value="<?= (int) $vendor['id'] ?>">

                                            <input type="hidden" name="action" value="suspend">

                                            <button class="btn-suspend" type="submit">
                                                Suspend
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <form method="post" action="admin_vendor_action.php"
                                            onsubmit="return confirm('Restore this vendor account?');">

                                            <input type="hidden" name="csrf_token"
                                                value="<?= e(csrf_token()) ?>">

                                            <input type="hidden" name="vendor_id"
                                                value="<?= (int) $vendor['id'] ?>">

                                            <input type="hidden" name="action" value="restore">

                                            <button class="btn-restore" type="submit">
                                                Restore
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </div>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <p>No vendors have registered yet.</p>

        <?php endif; ?>

    </div>

</main>

</body>
</html>