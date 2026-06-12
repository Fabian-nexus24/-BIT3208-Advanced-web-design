<?php
declare(strict_types=1);

/**
 * Simple offset pagination helpers.
 */

function pagination_page_from_request(string $param = 'page'): int
{
    $page = (int) ($_GET[$param] ?? 1);
    return max(1, $page);
}

/**
 * @return array{page: int, per_page: int, offset: int, total: int, total_pages: int}
 */
function pagination_meta(int $total, int $page, int $perPage): array
{
    $perPage = max(1, $perPage);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min(max(1, $page), $totalPages);

    return [
        'page'        => $page,
        'per_page'    => $perPage,
        'offset'      => ($page - 1) * $perPage,
        'total'       => $total,
        'total_pages' => $totalPages,
    ];
}

/**
 * @param array<string, scalar|null> $queryParams
 */
function render_pagination(int $total, int $page, int $perPage, string $path, array $queryParams = []): void
{
    $meta = pagination_meta($total, $page, $perPage);
    if ($meta['total_pages'] <= 1) {
        return;
    }

    unset($queryParams['page']);
    $baseQuery = http_build_query(array_filter($queryParams, static fn ($v) => $v !== null && $v !== ''));
    $sep = $baseQuery !== '' ? '&' : '';

    ?>
    <nav class="mt-4" aria-label="Pagination">
        <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">
            <li class="page-item <?= $meta['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(url($path . '?' . $baseQuery . $sep . 'page=' . ($meta['page'] - 1))) ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $meta['total_pages']; $i++): ?>
                <?php if ($i === 1 || $i === $meta['total_pages'] || abs($i - $meta['page']) <= 2): ?>
                    <li class="page-item <?= $i === $meta['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e(url($path . '?' . $baseQuery . $sep . 'page=' . $i)) ?>"><?= $i ?></a>
                    </li>
                <?php elseif (abs($i - $meta['page']) === 3): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
            <?php endfor; ?>
            <li class="page-item <?= $meta['page'] >= $meta['total_pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(url($path . '?' . $baseQuery . $sep . 'page=' . ($meta['page'] + 1))) ?>">Next</a>
            </li>
        </ul>
        <p class="text-center text-muted small mt-2 mb-0">
            Showing page <?= e((string) $meta['page']) ?> of <?= e((string) $meta['total_pages']) ?>
            (<?= e((string) $meta['total']) ?> total)
        </p>
    </nav>
    <?php
}
