<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
$navContext = $navContext ?? 'public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FarmConnect Kenya — fresh produce marketplace connecting farmers and customers across Kenya.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(url('assets/img/favicon.svg')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/theme.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
    <?php if (!empty($useMarketplaceCss)): ?>
    <link rel="stylesheet" href="<?= e(url('assets/css/marketplace.css')) ?>">
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100 fc-public">
