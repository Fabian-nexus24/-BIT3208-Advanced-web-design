<?php
declare(strict_types=1);

/**
 * Secure profile image upload handling.
 */

/**
 * @return array{ok: bool, path?: string, error?: string}
 */
function upload_profile_image(array $file, string $prefix): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Image upload failed. Please try again.'];
    }

    if (($file['size'] ?? 0) > UPLOAD_MAX_BYTES) {
        return ['ok' => false, 'error' => 'Image must be 2 MB or smaller.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if ($mime === false || !in_array($mime, UPLOAD_ALLOWED_MIME, true)) {
        return ['ok' => false, 'error' => 'Only JPG, PNG, or WebP images are allowed.'];
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => null,
    };
    if ($ext === null) {
        return ['ok' => false, 'error' => 'Invalid image type.'];
    }

    if (!is_dir(UPLOAD_PROFILE_DIR)) {
        mkdir(UPLOAD_PROFILE_DIR, 0755, true);
    }

    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = UPLOAD_PROFILE_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'Could not save uploaded image.'];
    }

    if (!verify_image_is_safe($destination)) {
        unlink($destination);
        return ['ok' => false, 'error' => 'Image dimensions are too large or file is invalid.'];
    }

    return ['ok' => true, 'path' => UPLOAD_PROFILE_URL . $filename];
}

function delete_profile_image(?string $relativePath): void
{
    delete_upload_file($relativePath);
}

function delete_upload_file(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '') {
        return;
    }

    $full = dirname(__DIR__) . '/' . ltrim($relativePath, '/');
    if (is_file($full)) {
        unlink($full);
    }
}

/**
 * @return array{ok: bool, path?: string|null, error?: string}
 */
function upload_product_image(array $file, string $prefix): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Image upload failed. Please try again.'];
    }

    if (($file['size'] ?? 0) > UPLOAD_MAX_BYTES) {
        return ['ok' => false, 'error' => 'Image must be 2 MB or smaller.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png'];
    if ($mime === false || !in_array($mime, $allowed, true)) {
        return ['ok' => false, 'error' => 'Only JPG, JPEG, and PNG images are allowed.'];
    }

    $ext = $mime === 'image/png' ? 'png' : 'jpg';

    if (!is_dir(UPLOAD_PRODUCT_DIR)) {
        mkdir(UPLOAD_PRODUCT_DIR, 0755, true);
    }

    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destination = UPLOAD_PRODUCT_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'Could not save uploaded image.'];
    }

    if (!verify_image_is_safe($destination)) {
        unlink($destination);
        return ['ok' => false, 'error' => 'Image dimensions are too large or file is invalid.'];
    }

    optimize_uploaded_image($destination, UPLOAD_PRODUCT_MAX_WIDTH);

    return ['ok' => true, 'path' => UPLOAD_PRODUCT_URL . $filename];
}

/**
 * Resize large JPEG/PNG uploads when GD is available.
 */
function optimize_uploaded_image(string $absolutePath, int $maxWidth): void
{
    if (!function_exists('imagecreatefromjpeg') || !is_file($absolutePath)) {
        return;
    }

    $info = @getimagesize($absolutePath);
    if ($info === false || $info[0] <= $maxWidth) {
        return;
    }

    $ratio = $maxWidth / $info[0];
    $newW = $maxWidth;
    $newH = (int) round($info[1] * $ratio);

    $src = match ($info['mime']) {
        'image/jpeg' => @imagecreatefromjpeg($absolutePath),
        'image/png'  => @imagecreatefrompng($absolutePath),
        default      => null,
    };
    if ($src === false || $src === null) {
        return;
    }

    $dst = imagecreatetruecolor($newW, $newH);
    if ($dst === false) {
        imagedestroy($src);
        return;
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $info[0], $info[1]);

    if ($info['mime'] === 'image/jpeg') {
        imagejpeg($dst, $absolutePath, 85);
    } else {
        imagepng($dst, $absolutePath, 6);
    }

    imagedestroy($src);
    imagedestroy($dst);
}
