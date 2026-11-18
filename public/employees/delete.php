<?php
/**
 * Hapus pegawai (DELETE).
 * Hanya menerima POST + token CSRF valid. Konfirmasi dilakukan lewat modal
 * di halaman daftar/detail, lalu form modal diarahkan ke file ini.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

if (!is_post()) {
    http_response_code(405);
    header('Allow: POST');
    abort_page(
        405,
        'Metode Tidak Diizinkan',
        'Penghapusan data hanya dapat dilakukan melalui tombol hapus yang tersedia di aplikasi.'
    );
}

csrf_verify();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    flash('danger', 'Gagal menghapus: ID pegawai tidak valid.');
    redirect('employees/index.php');
}

$repository = new EmployeeRepository(Database::connection());
$employee   = $repository->find((int) $id);

if ($employee === null) {
    flash('warning', 'Data pegawai tidak ditemukan atau sudah dihapus sebelumnya.');
    redirect('employees/index.php');
}

$repository->delete((int) $id);

flash('success', 'Data pegawai "' . $employee['nama'] . '" berhasil dihapus.');
redirect('employees/index.php');
