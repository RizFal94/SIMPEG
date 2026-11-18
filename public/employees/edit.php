<?php
/**
 * Ubah data pegawai (UPDATE).
 * Validasi unik (NIP/email) mengecualikan ID pegawai yang sedang diubah.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

$repository = new EmployeeRepository(Database::connection());
$validator  = new EmployeeValidator($repository);

/** @var int $id */
$id = 0;

if (is_post()) {
    csrf_verify();
    $postedId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($postedId === false || $postedId === null || $postedId <= 0) {
        abort_page(400, 'Permintaan Tidak Valid', 'ID pegawai tidak valid.');
    }
    $id = (int) $postedId;
} else {
    $requestedId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($requestedId === false || $requestedId === null || $requestedId <= 0) {
        abort_page(400, 'Permintaan Tidak Valid', 'ID pegawai tidak valid atau tidak disertakan.');
    }
    $id = (int) $requestedId;
}

$employee = $repository->find($id);
if ($employee === null) {
    abort_page(404, 'Data Tidak Ditemukan', 'Data pegawai yang ingin diubah tidak tersedia atau sudah dihapus.');
}

if (is_post()) {
    $result = $validator->validate($_POST, $id);

    if ($result['errors'] !== []) {
        set_errors($result['errors']);
        set_old($result['data']);
        redirect('employees/edit.php?id=' . $id);
    }

    $repository->update($id, $result['data']);

    flash('success', 'Data pegawai "' . $result['data']['nama'] . '" berhasil diperbarui.');
    redirect('employees/show.php?id=' . $id);
}

$errors = consume_errors();
$old    = consume_old();
$data   = array_merge($employee, $old);

$mode       = 'edit';
$formAction = url('employees/edit.php?id=' . $id);
$cancelUrl  = url('employees/show.php?id=' . $id);
$employeeId = $id;

$pageTitle    = 'Ubah Data Pegawai';
$pageSubtitle = 'Perbarui informasi pegawai ' . $employee['nama'];
$currentPage  = 'employees';
?>
<?php require dirname(__DIR__) . '/partials/header.php'; ?>

<div class="detail-toolbar">
    <a href="<?= e(url('employees/show.php?id=' . $id)) ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke detail pegawai
    </a>
</div>

<?php
/*
 * ID dikirim ulang lewat hidden input di dalam form agar proses POST
 * tetap tahu record mana yang diubah (form action juga membawa ?id=).
 */
?>

<?php require __DIR__ . '/_form.php'; ?>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
