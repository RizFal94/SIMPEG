<?php
/**
 * Daftar pegawai: pencarian, filter, pengurutan, dan paginasi.
 * Tombol hapus memicu modal konfirmasi (partial delete-modal.php).
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

$repository = new EmployeeRepository(Database::connection());

$filters = [
    'q'         => trim((string) ($_GET['q'] ?? '')),
    'gender'    => (string) ($_GET['gender'] ?? ''),
    'education' => (string) ($_GET['education'] ?? ''),
    'status'    => (string) ($_GET['status'] ?? ''),
    'sort'      => (string) ($_GET['sort'] ?? 'terbaru'),
];

// Nilai filter yang tidak dikenal diabaikan (fail safe).
if (!EmployeeOptions::isGender($filters['gender'])) {
    $filters['gender'] = '';
}
if (!EmployeeOptions::isEducation($filters['education'])) {
    $filters['education'] = '';
}
if (!EmployeeOptions::isStatus($filters['status'])) {
    $filters['status'] = '';
}
if (!in_array($filters['sort'], ['terbaru', 'nama_asc', 'nama_desc', 'masuk_asc', 'masuk_desc'], true)) {
    $filters['sort'] = 'terbaru';
}

$requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$paginator = $repository->paginate($filters, $requestedPage === false || $requestedPage === null ? 1 : (int) $requestedPage, 10);

$isFiltered = $filters['q'] !== '' || $filters['gender'] !== '' || $filters['education'] !== ''
    || $filters['status'] !== '';

$paginationParams = array_filter(
    $filters,
    static fn (string $value, string $key): bool => $value !== '' && !($key === 'sort' && $value === 'terbaru'),
    ARRAY_FILTER_USE_BOTH
);

$pageTitle    = 'Data Pegawai';
$pageSubtitle = 'Kelola seluruh data pegawai perusahaan';
$currentPage  = 'employees';
?>
<?php require dirname(__DIR__) . '/partials/header.php'; ?>

<article class="panel toolbar-panel">
    <form method="get" action="<?= e(url('employees/index.php')) ?>" class="filter-bar" role="search">
        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>"
                   placeholder="Cari nama, NIP, email, jabatan..." aria-label="Cari pegawai">
        </div>

        <div class="filter-group">
            <select name="gender" class="form-select" aria-label="Filter jenis kelamin">
                <option value="">Semua Gender</option>
                <?php foreach (EmployeeOptions::GENDERS as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= selected_if($filters['gender'], $value) ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="education" class="form-select" aria-label="Filter pendidikan">
                <option value="">Semua Pendidikan</option>
                <?php foreach (EmployeeOptions::EDUCATIONS as $education): ?>
                    <option value="<?= e($education) ?>" <?= selected_if($filters['education'], $education) ?>>
                        <?= e($education) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="form-select" aria-label="Filter status">
                <option value="">Semua Status</option>
                <?php foreach (EmployeeOptions::STATUSES as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected_if($filters['status'], $status) ?>>
                        <?= e($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="sort" class="form-select" aria-label="Urutkan data">
                <option value="terbaru" <?= selected_if($filters['sort'], 'terbaru') ?>>Terbaru</option>
                <option value="nama_asc" <?= selected_if($filters['sort'], 'nama_asc') ?>>Nama A-Z</option>
                <option value="nama_desc" <?= selected_if($filters['sort'], 'nama_desc') ?>>Nama Z-A</option>
                <option value="masuk_asc" <?= selected_if($filters['sort'], 'masuk_asc') ?>>Terlama masuk</option>
                <option value="masuk_desc" <?= selected_if($filters['sort'], 'masuk_desc') ?>>Terbaru masuk</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-brand">
                <i class="bi bi-funnel"></i> Terapkan
            </button>
            <?php if ($isFiltered || $filters['sort'] !== 'terbaru'): ?>
                <a href="<?= e(url('employees/index.php')) ?>" class="btn btn-light">Reset</a>
            <?php endif; ?>
            <a href="<?= e(url('employees/create.php')) ?>" class="btn btn-outline-brand">
                <i class="bi bi-plus-lg"></i> Tambah
            </a>
        </div>
    </form>
</article>

<article class="panel panel-table">
    <header class="panel-head">
        <div>
            <h3>Daftar Pegawai</h3>
            <p>
                <?= number_format($paginator['total'], 0, ',', '.') ?> data ditemukan
                <?php if ($isFiltered): ?>
                    &middot; difilter dari <?= number_format($repository->statistics()['total'], 0, ',', '.') ?> pegawai
                <?php endif; ?>
            </p>
        </div>
        <span class="panel-icon"><i class="bi bi-table"></i></span>
    </header>

    <div class="table-responsive">
        <table class="table app-table align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col" style="width: 72px;">No</th>
                    <th scope="col">Pegawai</th>
                    <th scope="col">Jenis Kelamin</th>
                    <th scope="col">Pendidikan</th>
                    <th scope="col">Jabatan / Departemen</th>
                    <th scope="col">Usia</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($paginator['items'] === []): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <span class="empty-icon"><i class="bi bi-people"></i></span>
                                <?php if ($isFiltered): ?>
                                    <h4>Tidak ada pegawai yang cocok</h4>
                                    <p>Kata kunci atau filter yang Anda pilih tidak menemukan data. Coba ubah filter pencarian.</p>
                                    <a href="<?= e(url('employees/index.php')) ?>" class="btn btn-light">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset filter
                                    </a>
                                <?php else: ?>
                                    <h4>Belum ada data pegawai</h4>
                                    <p>Mulai kelola data SDM dengan menambahkan pegawai pertama ke sistem.</p>
                                    <a href="<?= e(url('employees/create.php')) ?>" class="btn btn-brand">
                                        <i class="bi bi-plus-lg"></i> Tambah Pegawai
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($paginator['items'] as $index => $employee): ?>
                    <?php $age = age_from_birthdate($employee['tanggal_lahir']); ?>
                    <tr>
                        <td class="text-muted-strong">
                            <?= (int) $paginator['from'] + $index ?>
                        </td>
                        <td>
                            <div class="person-cell">
                                <span class="avatar avatar-<?= $employee['jenis_kelamin'] === 'L' ? 'blue' : 'pink' ?>">
                                    <?= e(initial_from_name($employee['nama'])) ?>
                                </span>
                                <div>
                                    <a class="person-name"
                                       href="<?= e(url('employees/show.php?id=' . (int) $employee['id'])) ?>">
                                        <?= e($employee['nama']) ?>
                                    </a>
                                    <span class="person-sub">NIP <?= e($employee['nip']) ?></span>
                                    <span class="person-sub d-none d-md-block"><?= e($employee['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= e(gender_badge_class($employee['jenis_kelamin'])) ?>">
                                <?= e(EmployeeOptions::genderLabel($employee['jenis_kelamin'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= e(education_badge_class($employee['pendidikan_terakhir'])) ?>">
                                <?= e($employee['pendidikan_terakhir']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="cell-main"><?= e($employee['jabatan']) ?></span>
                            <span class="cell-sub"><?= e($employee['departemen']) ?></span>
                        </td>
                        <td>
                            <span class="cell-main"><?= $age === null ? '-' : (int) $age . ' th' ?></span>
                            <span class="cell-sub"><?= e(format_date_short($employee['tanggal_lahir'])) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= e(status_badge_class($employee['status'])) ?>">
                                <?= e($employee['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="action-group">
                                <a href="<?= e(url('employees/show.php?id=' . (int) $employee['id'])) ?>"
                                   class="btn-icon-sm" title="Lihat detail"
                                   aria-label="Lihat detail <?= e($employee['nama']) ?>">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= e(url('employees/edit.php?id=' . (int) $employee['id'])) ?>"
                                   class="btn-icon-sm" title="Ubah data"
                                   aria-label="Ubah data <?= e($employee['nama']) ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn-icon-sm btn-icon-danger" title="Hapus data"
                                        aria-label="Hapus data <?= e($employee['nama']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-delete-id="<?= (int) $employee['id'] ?>"
                                        data-delete-name="<?= e($employee['nama']) ?>"
                                        data-delete-nip="<?= e($employee['nip']) ?>">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php require dirname(__DIR__) . '/partials/pagination.php'; ?>
</article>

<?php require dirname(__DIR__) . '/partials/delete-modal.php'; ?>
<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
