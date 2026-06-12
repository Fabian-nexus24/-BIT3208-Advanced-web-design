<?php
declare(strict_types=1);

/**
 * Input validation and upload security helpers.
 */

function positive_int(mixed $value, int $min = 1, ?int $max = null): ?int
{
    if (!is_numeric($value)) {
        return null;
    }
    $n = (int) $value;
    if ($n < $min) {
        return null;
    }
    if ($max !== null && $n > $max) {
        return null;
    }
    return $n;
}

function positive_decimal(mixed $value, float $min = 0.0, ?float $max = null): ?float
{
    if (!is_numeric($value)) {
        return null;
    }
    $n = round((float) $value, 2);
    if ($n < $min) {
        return null;
    }
    if ($max !== null && $n > $max) {
        return null;
    }
    return $n;
}

function safe_relative_upload_path(?string $path): ?string
{
    if ($path === null || $path === '') {
        return null;
    }
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if (str_contains($path, '..') || str_starts_with($path, 'http')) {
        return null;
    }
    return $path;
}

function verify_image_is_safe(string $filePath): bool
{
    if (!is_file($filePath)) {
        return false;
    }
    $info = @getimagesize($filePath);
    if ($info === false) {
        return false;
    }
    $maxW = defined('UPLOAD_MAX_WIDTH') ? UPLOAD_MAX_WIDTH : 2000;
    $maxH = defined('UPLOAD_MAX_HEIGHT') ? UPLOAD_MAX_HEIGHT : 2000;
    return $info[0] <= $maxW && $info[1] <= $maxH;
}
