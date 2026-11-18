<?php
/**
 * Pagination reusable.
 * Variabel yang harus tersedia:
 * - $paginator        : hasil EmployeeRepository::paginate()
 * - $paginationParams : query string filter aktif (tanpa "page")
 */
$paginationParams = $paginationParams ?? [];
$activePage       = (int) $paginator['page'];
$totalPages       = (int) $paginator['pages'];

$pageUrl = static function (int $page) use ($paginationParams): string {
    $query = $paginationParams;
    if ($page <= 1) {
        unset($query['page']);
    } else {
        $query['page'] = $page;
    }

    $queryString = http_build_query($query);

    return url('employees/index.php' . ($queryString !== '' ? '?' . $queryString : ''));
};

$windowStart = max(1, $activePage - 2);
$windowEnd   = min($totalPages, $activePage + 2);
?>
<div class="table-footer">
    <p class="table-info">
        Menampilkan <strong><?= (int) $paginator['from'] ?></strong>&ndash;<strong><?= (int) $paginator['to'] ?></strong>
        dari <strong><?= (int) $paginator['total'] ?></strong> pegawai
    </p>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Navigasi halaman data pegawai">
            <ul class="pagination app-pagination mb-0">
                <li class="page-item <?= $activePage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($pageUrl(max(1, $activePage - 1))) ?>" aria-label="Sebelumnya">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <?php if ($windowStart > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= e($pageUrl(1)) ?>">1</a></li>
                    <?php if ($windowStart > 2): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
                    <li class="page-item <?= $page === $activePage ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e($pageUrl($page)) ?>"
                           <?= $page === $activePage ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($windowEnd < $totalPages): ?>
                    <?php if ($windowEnd < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= e($pageUrl($totalPages)) ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>

                <li class="page-item <?= $activePage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($pageUrl(min($totalPages, $activePage + 1))) ?>" aria-label="Berikutnya">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

