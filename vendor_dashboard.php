<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Only vendors can access this dashboard.
require_role('vendor');

$id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| BUSINESS STATISTICS
|--------------------------------------------------------------------------
*/

// Pending orders.
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS pending
     FROM orders
     WHERE vendor_id = ? AND status = 'pending'"
);

$stmt->bind_param('i', $id);
$stmt->execute();

$pending = (int) $stmt->get_result()->fetch_assoc()['pending'];
$stmt->close();

// Total menu items.
$stmt = $conn->prepare(
    'SELECT COUNT(*) AS items
     FROM menu_items
     WHERE vendor_id = ?'
);

$stmt->bind_param('i', $id);
$stmt->execute();

$items = (int) $stmt->get_result()->fetch_assoc()['items'];
$stmt->close();

// Today's completed sales.
$stmt = $conn->prepare(
    "SELECT COALESCE(SUM(total), 0) AS sales
     FROM orders
     WHERE vendor_id = ?
       AND status = 'completed'
       AND DATE(created_at) = CURDATE()"
);

$stmt->bind_param('i', $id);
$stmt->execute();

$sales = $stmt->get_result()->fetch_assoc()['sales'];
$stmt->close();

/*
|--------------------------------------------------------------------------
| VENDOR VERIFICATION INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    'SELECT business_name,
            verification_status,
            rejection_reason,
            submitted_at
     FROM vendor_verifications
     WHERE vendor_id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $id);
$stmt->execute();

$verification = $stmt->get_result()->fetch_assoc();
$stmt->close();

$verificationStatus = $verification['verification_status'] ?? 'not_submitted';

$verificationLabels = [
    'not_submitted' => 'Not submitted',
    'pending' => 'Awaiting review',
    'contact_verified' => 'Contact verified',
    'business_verified' => 'Business verified',
    'approved' => 'Approved',
    'rejected' => 'Rejected'
];

$statusLabel = $verificationLabels[$verificationStatus] ?? 'Unknown status';

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Vendor Workspace | Mama Ntilie</title>

    <link rel="stylesheet" href="style.css">

    <style>

        /* BUSINESS VERIFICATION CARD */

        .verification-card {
            margin: 30px 0;
            padding: 28px;
            border-radius: 16px;
            background: linear-gradient(145deg, #202020, #171717);
            border: 1px solid rgba(255, 255, 255, 0.09);
            color: #f5f5f5;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .verification-card h2 {
            color: #ffffff;
            margin-bottom: 12px;
        }

        .verification-card p {
            color: #b8b8b8;
            line-height: 1.7;
        }

        .verification-card strong {
            color: #ffffff;
        }

        /* VERIFICATION STATUS */

        .verification-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 30px;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
            font-weight: 600;
            margin: 12px 0;
        }

        /* VERIFICATION BUTTON */

        .verification-button {
            display: inline-block;
            padding: 12px 20px;
            margin-top: 15px;
            border-radius: 8px;
            background: #e87924;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .verification-button:hover {
            background: #c65d12;
            transform: translateY(-2px);
        }

        /* ADMINISTRATOR FEEDBACK */

        .verification-feedback {
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #ef4444;
            border-radius: 6px;
            background: rgba(239, 68, 68, 0.1);
        }

        .verification-feedback strong {
            color: #fca5a5;
        }

        .verification-feedback p {
            color: #fecaca;
        }

        /* MOBILE RESPONSIVENESS */

        @media (max-width: 600px) {
            .verification-card {
                padding: 20px;
            }

            .verification-button {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }
        }

    </style>

</head>

<body>

<header class="topbar">

    <a class="brand" href="index.php">🍲 Mama Ntilie</a>

    <span>Vendor workspace</span>

    <a href="logout.php">Sign out</a>

</header>

<main class="page-wrap">

    <span class="eyebrow">YOUR BUSINESS AT A GLANCE</span>

    <h1>Welcome, <?= e(current_user_name()) ?>.</h1>

    <p class="muted">
        Manage your menu, orders, business records and verification.
    </p>

    <!-- BUSINESS STATISTICS -->

    <div class="stat-grid">

        <div class="stat-card">
            <span>Menu items</span>
            <strong><?= $items ?></strong>
            <small>Meals listed by you</small>
        </div>

        <div class="stat-card">
            <span>Pending orders</span>
            <strong><?= $pending ?></strong>
            <small>Need your attention</small>
        </div>

        <div class="stat-card">
            <span>Today's completed sales</span>
            <strong><?= money($sales) ?></strong>
            <small>Completed orders only</small>
        </div>

    </div>

    <!-- BUSINESS VERIFICATION -->

    <section class="verification-card">

        <h2>Business Verification</h2>

        <p>
            Complete your business information so the Mama Ntilie
            administration team can review your business.
        </p>

        <span class="verification-status">
            <?= e($statusLabel) ?>
        </span>

        <?php if ($verification): ?>

            <p>
                <strong>Business name:</strong>
                <?= e($verification['business_name']) ?>
            </p>

            <p>
                <strong>Submitted:</strong>
                <?= e($verification['submitted_at']) ?>
            </p>

            <?php if (
                $verificationStatus === 'rejected' &&
                !empty($verification['rejection_reason'])
            ): ?>

                <div class="verification-feedback">

                    <strong>Administrator feedback:</strong>

                    <p><?= e($verification['rejection_reason']) ?></p>

                </div>

            <?php endif; ?>

        <?php endif; ?>

        <?php if ($verificationStatus !== 'approved'): ?>

            <a class="verification-button"
               href="vendor_verification.php">

                <?= $verificationStatus === 'not_submitted'
                    ? 'Submit Business Verification'
                    : ($verificationStatus === 'rejected'
                        ? 'Update Verification Details'
                        : 'View Verification Details') ?>

            </a>

        <?php else: ?>

            <p>Your business verification has been approved.</p>

        <?php endif; ?>

    </section>

    <!-- BUSINESS MANAGEMENT -->

    <div class="action-grid">

        <a class="action-card" href="menu.php">
            <b>🍲 Manage menu</b>
            <span>Add meals, update availability and prices →</span>
        </a>

        <a class="action-card" href="vendor_orders.php">
            <b>📦 Customer orders <i class="badge"><?= $pending ?></i></b>
            <span>Review and update incoming orders →</span>
        </a>

        <a class="action-card" href="expenses.php">
            <b>🧾 Business expenses</b>
            <span>Record ingredient and operating costs →</span>
        </a>

        <a class="action-card" href="report.php">
            <b>📊 Sales report</b>
            <span>Review sales, expenses and net result →</span>
        </a>

    </div>

</main>

</body>
</html>