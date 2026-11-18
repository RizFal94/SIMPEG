<?php
/**
 * Detail pegawai.
 * Menampilkan seluruh informasi satu pegawai beserta aksi ubah/hapus.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

$repository = new EmployeeRepository(Database::connection());
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    abort_page(400, 'Permintaan Tidak Valid', 'ID pegawai tidak valid atau tidak disertakan.');
}

$employee = $repository->find((int) $id);

if ($employee === null) {
    abort_page(404, 'Data Tidak Ditemukan', 'Data pegawai yang Anda cari tidak tersedia atau sudah dihapus.');
}

$age = age_from_birthdate($employee['tanggal_lahir']);

$joinDate  = new DateTime($employee['tanggal_masuk']);
$today     = new DateTime('today');
$tenure    = $joinDate->diff($today);
$masaKerja = $joinDate <= $today
    ? $tenure->y . ' tahun ' . $tenure->m . ' bulan'
    : '-';

$pageTitle    = 'Detail Pegawai';
$pageSubtitle = 'Informasi lengkap data pegawai';
$currentPage  = 'employees';
?>
<?php require dirname(__DIR__) . '/partials/header.php'; ?>

<div class="detail-toolbar">
    <a href="<?= e(url('employees/index.php')) ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke daftar pegawai
    </a>
    <div class="detail-actions">
        <a href="<?= e(url('employees/edit.php?id=' . (int) $employee['id'])) ?>" class="btn btn-brand">
            <i class="bi bi-pencil-square"></i> Ubah Data
        </a>
        <button type="button" class="btn btn-outline-danger"
                data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-delete-id="<?= (int) $employee['id'] ?>"
                data-delete-name="<?= e($employee['nama']) ?>"
                data-delete-nip="<?= e($employee['nip']) ?>">
            <i class="bi bi-trash3"></i> Hapus
        </button>
    </div>
</div>

<div class="row g-3 g-xl-4">
    <div class="col-lg-4">
        <article class="panel profile-card">
            <span class="avatar avatar-xl avatar-<?= $employee['jenis_kelamin'] === 'L' ? 'blue' : 'pink' ?>">
                <?= e(initial_from_name($employee['nama'])) ?>
            </span>
            <h2 class="profile-name"><?= e($employee['nama']) ?></h2>
            <p class="profile-role"><?= e($employee['jabatan']) ?> &middot; <?= e($employee['departemen']) ?></p>
            <span class="badge <?= e(status_badge_class($employee['status'])) ?>"><?= e($employee['status']) ?></span>

            <div class="profile-contact">
                <div class="contact-row">
                    <span class="contact-icon"><i class="bi bi-upc-scan"></i></span>
                    <div>
                        <span class="contact-label">NIP</span>
                        <span class="contact-value"><?= e($employee['nip']) ?></span>
                    </div>
                </div>
                <div class="contact-row">
                    <span class="contact-icon"><i class="bi bi-envelope"></i></span>
                    <div>
                        <span class="contact-label">Email</span>
                        <a class="contact-value" href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a>
                    </div>
                </div>
                <div class="contact-row">
                    <span class="contact-icon"><i class="bi bi-telephone"></i></span>
                    <div>
                        <span class="contact-label">Telepon</span>
                        <a class="contact-value" href="tel:<?= e($employee['telepon']) ?>"><?= e($employee['telepon']) ?></a>
                    </div>
                </div>
                <div class="contact-row">
                    <span class="contact-icon"><i class="bi bi-calendar-check"></i></span>
                    <div>
                        <span class="contact-label">Tanggal Masuk</span>
                        <span class="contact-value"><?= e(format_date_id($employee['tanggal_masuk'])) ?></span>
                    </div>
                </div>
            </div>
        </article>
    </div>

    <div class="col-lg-8">
        <article class="panel">
            <header class="panel-head">
                <div>
                    <h3>Informasi Pribadi</h3>
                    <p>Data identitas dan latar belakang pegawai</p>
                </div>
                <span class="panel-icon"><i class="bi bi-person-vcard"></i></span>
            </header>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama Lengkap</span>
                    <span class="detail-value"><?= e($employee['nama']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">NIP</span>
                    <span class="detail-value"><?= e($employee['nip']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jenis Kelamin</span>
                    <span class="detail-value"><?= e(EmployeeOptions::genderLabel($employee['jenis_kelamin'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Lahir</span>
                    <span class="detail-value">
                        <?= e(format_date_id($employee['tanggal_lahir'])) ?>
                        <?php if ($age !== null): ?>
                            <span class="detail-hint">(<?= (int) $age ?> tahun)</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Pendidikan Terakhir</span>
                    <span class="detail-value">
                        <span class="badge <?= e(education_badge_class($employee['pendidikan_terakhir'])) ?>">
                            <?= e($employee['pendidikan_terakhir']) ?>
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status Kepegawaian</span>
                    <span class="detail-value">
                        <span class="badge <?= e(status_badge_class($employee['status'])) ?>">
                            <?= e($employee['status']) ?>
                        </span>
                    </span>
                </div>
                <div class="detail-item detail-item-full">
                    <span class="detail-label">Alamat</span>
                    <span class="detail-value">
                        <?= $employee['alamat'] ? e($employee['alamat']) : '<span class="text-muted">Belum diisi</span>' ?>
                    </span>
                </div>
            </div>
        </article>

        <article class="panel mt-3 mt-xl-4">
            <header class="panel-head">
                <div>
                    <h3>Informasi Kepegawaian</h3>
                    <p>Posisi, penempatan, dan masa kerja</p>
                </div>
                <span class="panel-icon"><i class="bi bi-briefcase"></i></span>
            </header>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Jabatan</span>
                    <span class="detail-value"><?= e($employee['jabatan']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Departemen</span>
                    <span class="detail-value"><?= e($employee['departemen']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Masuk</span>
                    <span class="detail-value"><?= e(format_date_id($employee['tanggal_masuk'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Masa Kerja</span>
                    <span class="detail-value"><?= e($masaKerja) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Terakhir Diperbarui</span>
                    <span class="detail-value">
                        <?= e(format_date_id(substr((string) $employee['updated_at'], 0, 10))) ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">ID Pegawai</span>
                    <span class="detail-value">#<?= (int) $employee['id'] ?></span>
                </div>
            </div>
        </article>
    </div>
</div>

<?php require dirname(__DIR__) . '/partials/delete-modal.php'; ?>
<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
