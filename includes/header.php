<?php require_once __DIR__ . '/../config/session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(isset($pageTitle) ? (strpos($pageTitle, 'Local Service Provider') === 0 ? $pageTitle : "Local Service Provider | " . $pageTitle) : "Local Service Provider | Home") ?></title>
    <!-- Google Fonts: Inter & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Core Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= getBaseUrl() ?>/assets/css/style.css" rel="stylesheet">
    <?php if (isset($isLanding) && $isLanding): ?>
        <link href="<?= getBaseUrl() ?>/assets/css/landing.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body class="<?= $bodyClass ?? '' ?>">
<?php include __DIR__ . '/navbar.php'; ?>

