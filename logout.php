<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$flashMessage = 'You have been logged out.';
logout_user();

session_start();
flash_set('success', $flashMessage);
redirect(INDEX_PATH);
