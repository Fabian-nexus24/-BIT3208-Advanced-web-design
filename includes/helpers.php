<?php
declare(strict_types=1);

/**
 * General helper functions.
 */

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_phone(string $phone): bool
{
    $phone = trim($phone);
    if ($phone === '') {
        return true;
    }
    return (bool) preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone);
}

function category_icon(string $category): string
{
    return match ($category) {
        'Vegetables'       => 'bi-flower1',
        'Fruits'           => 'bi-apple',
        'Tubers & Roots'   => 'bi-moisture',
        'Grains & Cereals' => 'bi-grain',
        'Legumes'          => 'bi-circle-fill',
        'Herbs & Spices'   => 'bi-leaf',
        'Dairy'            => 'bi-cup-straw',
        'Poultry & Eggs'   => 'bi-egg-fried',
        default            => 'bi-basket2',
    };
}
