<?php
declare(strict_types=1);

/**
 * Session flash messages (one-time display).
 */

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function flash_render(): string
{
    $flash = flash_get();
    if ($flash === null) {
        return '';
    }

    $type = in_array($flash['type'], ['success', 'danger', 'warning', 'info'], true)
        ? $flash['type']
        : 'info';

    $icon = match ($type) {
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default   => 'bi-info-circle-fill',
    };

    return sprintf(
        '<div class="alert alert-%s alert-dismissible fade show d-flex align-items-start gap-2" role="alert">
            <i class="bi %s flex-shrink-0 mt-1"></i>
            <div class="flex-grow-1">%s</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>',
        e($type),
        e($icon),
        e($flash['message'])
    );
}
