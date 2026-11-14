<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

require_role('vendor');

$vendorId = (int) $_SESSION['user_id'];
$error = '';

$stmt = $conn->prepare(
    'SELECT *
     FROM vendor_verifications
     WHERE vendor_id = ?
     LIMIT 1'
);

$stmt->bind_param('i', $vendorId);
$stmt->execute();

$verification = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $businessName = trim($_POST['business_name'] ?? '');
    $businessAddress = trim($_POST['business_address'] ?? '');
    $licenseNumber = trim($_POST['business_license_number'] ?? '');
    $tinNumber = trim($_POST['tin_number'] ?? '');
    $brelaNumber = trim($_POST['brela_number'] ?? '');

    if ($businessName === '') {

        $error = 'Business name is required.';

    } else {

        if ($verification) {

            if (in_array(
                $verification['verification_status'],
                ['approved', 'contact_verified', 'business_verified'],
                true
            )) {
                $error = 'Your verification is already under review or approved. Contact support if you need to change your details.';
            } else {

                $stmt = $conn->prepare(
                    "UPDATE vendor_verifications
                     SET business_name = ?,
                         business_address = ?,
                         business_license_number = ?,
                         tin_number = ?,
                         brela_number = ?,
                         verification_status = 'pending',
                         rejection_reason = NULL,
                         submitted_at = NOW()
                     WHERE vendor_id = ?"
                );

                $stmt->bind_param(
                    'sssssi',
                    $businessName,
                    $businessAddress,
                    $licenseNumber,
                    $tinNumber,
                    $brelaNumber,
                    $vendorId
                );

                $stmt->execute();
                $stmt->close();

                set_flash('success', 'Your verification information has been updated.');
                redirect('vendor_verification.php');
            }

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO vendor_verifications
                 (
                    vendor_id,
                    business_name,
                    business_address,
                    business_license_number,
                    tin_number,
                    brela_number,
                    verification_status
                 )
                 VALUES (?, ?, ?, ?, ?, ?, 'pending')"
            );

            $stmt->bind_param(
                'isssss',
                $vendorId,
                $businessName,
                $businessAddress,
                $licenseNumber,
                $tinNumber,
                $brelaNumber
            );

            $stmt->execute();
            $stmt->close();

            set_flash('success', 'Your verification application has been submitted.');
            redirect('vendor_verification.php');
        }
    }
}

$flash = get_flash();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Business Verification | Mama Ntilie</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<main class="container">

    <h1>Business Verification</h1>

    <p>Submit your business information for administrator review.</p>

    <p>
        <a href="vendor_dashboard.php">&larr; Back to Dashboard</a>
    </p>

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

    <?php if ($verification): ?>

        <p>
            <strong>Current verification status:</strong>
            <?= e($verification['verification_status']) ?>
        </p>

        <?php if ($verification['rejection_reason']): ?>
            <p>
                <strong>Administrator feedback:</strong>
                <?= e($verification['rejection_reason']) ?>
            </p>
        <?php endif; ?>

    <?php endif; ?>

    <form method="post" class="form-grid">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <label class="full">
            Business Name
            <input
                type="text"
                name="business_name"
                maxlength="180"
                value="<?= e($verification['business_name'] ?? '') ?>"
                required
            >
        </label>

        <label class="full">
            Business Address
            <input
                type="text"
                name="business_address"
                maxlength="255"
                value="<?= e($verification['business_address'] ?? '') ?>"
            >
        </label>

        <label class="full">
            Business License Number
            <input
                type="text"
                name="business_license_number"
                maxlength="100"
                value="<?= e($verification['business_license_number'] ?? '') ?>"
            >
        </label>

        <label class="full">
            TIN Number
            <input
                type="text"
                name="tin_number"
                maxlength="100"
                value="<?= e($verification['tin_number'] ?? '') ?>"
            >
        </label>

        <label class="full">
            BRELA Registration Number
            <input
                type="text"
                name="brela_number"
                maxlength="100"
                value="<?= e($verification['brela_number'] ?? '') ?>"
            >
        </label>

        <button type="submit" class="btn btn-primary full">
            Submit Verification
        </button>

    </form>

</main>

</body>
</html>