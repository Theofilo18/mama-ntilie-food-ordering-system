<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

require_role('admin');

// Validate vendor ID.
$vendorId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$vendorId) {
    set_flash('error', 'Invalid vendor ID.');
    redirect('admin_vendors.php');
}

// Retrieve vendor information.
$stmt = $conn->prepare(
    "SELECT
        u.id,
        u.name,
        u.email,
        u.location,
        u.account_status,
        u.created_at,
        vv.business_name,
        vv.business_address,
        vv.business_license_number,
        vv.tin_number,
        vv.brela_number,
        vv.verification_status,
        vv.rejection_reason,
        vv.submitted_at,
        vv.reviewed_at
     FROM users u
     LEFT JOIN vendor_verifications vv
        ON vv.vendor_id = u.id
     WHERE u.id = ? AND u.role = 'vendor'
     LIMIT 1"
);

$stmt->bind_param('i', $vendorId);
$stmt->execute();

$vendor = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$vendor) {
    http_response_code(404);
    exit('Vendor not found.');
}

function display_value(?string $value): string
{
    return ($value !== null && $value !== '')
        ? e($value)
        : 'Not provided';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Vendor Details | Mama Ntilie</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<main class="container">

    <h1>Vendor Details</h1>

    <p>
        <a href="admin_vendors.php">&larr; Back to Vendors</a>
    </p>

    <section class="admin-section">

        <h2>Personal Information</h2>

        <p><strong>Vendor ID:</strong> <?= (int)$vendor['id'] ?></p>

        <p><strong>Name:</strong> <?= e($vendor['name']) ?></p>

        <p><strong>Email:</strong> <?= e($vendor['email']) ?></p>

        <p><strong>Location:</strong> <?= display_value($vendor['location']) ?></p>

        <p><strong>Registered:</strong> <?= e($vendor['created_at']) ?></p>

    </section>

    <section class="admin-section">

        <h2>Business Information</h2>

        <p>
            <strong>Business Name:</strong>
            <?= display_value($vendor['business_name']) ?>
        </p>

        <p>
            <strong>Business Address:</strong>
            <?= display_value($vendor['business_address']) ?>
        </p>

        <p>
            <strong>Business License:</strong>
            <?= display_value($vendor['business_license_number']) ?>
        </p>

        <p>
            <strong>TIN Number:</strong>
            <?= display_value($vendor['tin_number']) ?>
        </p>

        <p>
            <strong>BRELA Number:</strong>
            <?= display_value($vendor['brela_number']) ?>
        </p>

    </section>

    <section class="admin-section">

        <h2>Verification Information</h2>

        <p>
            <strong>Verification Status:</strong>
            <?= e($vendor['verification_status'] ?? 'Not submitted') ?>
        </p>

        <p>
            <strong>Submitted:</strong>
            <?= display_value($vendor['submitted_at']) ?>
        </p>

        <p>
            <strong>Last Reviewed:</strong>
            <?= display_value($vendor['reviewed_at']) ?>
        </p>

        <p>
            <strong>Rejection Reason:</strong>
            <?= display_value($vendor['rejection_reason']) ?>
        </p>

    </section>

    <section class="admin-section">

        <h2>Account Information</h2>

        <p>
            <strong>Account Status:</strong>
            <?= e($vendor['account_status']) ?>
        </p>

    </section>

</main>

</body>
</html>