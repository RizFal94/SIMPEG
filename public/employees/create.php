<?php
/**
 * Tambah pegawai (CREATE).
 * Alur: POST -> validasi -> simpan -> redirect ke halaman detail (PRG pattern).
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

$repository = new EmployeeRepository(Database::connection());
$validator  = new EmployeeValidator($repository);

$defaults = [
    'nip'                 => '',
    'nama'                => '',
    'jenis_kelamin'       => '',
    'tanggal_lahir'       => '',
    'pendidikan_terakhir' => '',
    'jabatan'             => '',
    'departemen'          => '',
    'email'               => '',
    'telepon'             => '',
    'alamat'              => '',
    'tanggal_masuk'       => date('Y-m-d'),
    'status'              => 'Aktif',
];

if (is_post()) {
    csrf_verify();

    $result = $validator->validate($_POST);

    if ($result['errors'] !== []) {
        set_errors($result['errors']);
        set_old($result['data']);
        redirect('employees/create.php');
    }

    $newId = $repository->create($result['data']);

    flash('success', 'Data pegawai "' . $result['data']['nama'] . '" berhasil ditambahkan.');
    redirect('employees/show.php?id=' . $newId);
}

$errors = consume_errors();
$old    = consume_old();
$data   = array_merge($defaults, $old);

$mode       = 'create';
$formAction = url('employees/create.php');
$cancelUrl  = url('employees/index.php');

$pageTitle    = 'Tambah Pegawai';
$pageSubtitle = 'Lengkapi formulir untuk mendaftarkan pegawai baru';
$currentPage  = 'create';
?>
<?php require dirname(__DIR__) . '/partials/header.php'; ?>

<div class="detail-toolbar">
    <a href="<?= e(url('employees/index.php')) ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar pegawai
    </a>
</div>

<?php require __DIR__ . '/_form.php'; ?>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
