<?php
/**
 * Dashboard (halaman utama).
 * Menampilkan kartu statistik + tiga grafik batang:
 * jenis kelamin, pendidikan terakhir, dan kelompok umur pegawai.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$repository = new EmployeeRepository(Database::connection());
$dashboard  = new DashboardService($repository);
$summary    = $dashboard->summary();
$recent     = $repository->recent(5);
$stats      = $summary['statistics'];

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan komposisi dan sebaran data pegawai';
$currentPage  = 'dashboard';
$pageScripts  = ['vendor/chartjs/chart.umd.min.js', 'js/dashboard.js'];

$chartPayload = [
    'gender'    => $summary['gender'],
    'education' => $summary['education'],
    'age'       => $summary['age'],
];
?>
<?php require __DIR__ . '/partials/header.php'; ?>

<section class="hero-banner">
    <div class="hero-copy">
        <span class="hero-eyebrow"><i class="bi bi-stars"></i> Ringkasan Perusahaan</span>
        <h2>Selamat datang kembali, Admin</h2>
        <p>Pantau komposisi pegawai berdasarkan jenis kelamin, pendidikan, dan usia dalam satu tampilan.</p>
    </div>
    <a href="<?= e(url('employees/create.php')) ?>" class="btn btn-hero">
        <i class="bi bi-plus-lg"></i> Tambah Pegawai
    </a>
</section>

<div class="row g-3 g-xl-4">
    <div class="col-6 col-xl-3">
        <article class="stat-card">
            <span class="stat-icon stat-teal"><i class="bi bi-people-fill"></i></span>
            <div class="stat-body">
                <span class="stat-label">Total Pegawai</span>
                <span class="stat-value"><?= number_format($stats['total'], 0, ',', '.') ?></span>
                <span class="stat-meta"><?= number_format($stats['aktif'], 0, ',', '.') ?> pegawai aktif</span>
            </div>
        </article>
    </div>
    <div class="col-6 col-xl-3">
        <article class="stat-card">
            <span class="stat-icon stat-blue"><i class="bi bi-gender-male"></i></span>
            <div class="stat-body">
                <span class="stat-label">Laki-laki</span>
                <span class="stat-value"><?= number_format($stats['laki_laki'], 0, ',', '.') ?></span>
                <span class="stat-meta">
                    <?= $stats['total'] > 0 ? round($stats['laki_laki'] / $stats['total'] * 100) : 0 ?>% dari total
                </span>
            </div>
        </article>
    </div>
    <div class="col-6 col-xl-3">
        <article class="stat-card">
            <span class="stat-icon stat-pink"><i class="bi bi-gender-female"></i></span>
            <div class="stat-body">
                <span class="stat-label">Perempuan</span>
                <span class="stat-value"><?= number_format($stats['perempuan'], 0, ',', '.') ?></span>
                <span class="stat-meta">
                    <?= $stats['total'] > 0 ? round($stats['perempuan'] / $stats['total'] * 100) : 0 ?>% dari total
                </span>
            </div>
        </article>
    </div>
    <div class="col-6 col-xl-3">
        <article class="stat-card">
            <span class="stat-icon stat-amber"><i class="bi bi-hourglass-split"></i></span>
            <div class="stat-body">
                <span class="stat-label">Rata-rata Usia</span>
                <span class="stat-value"><?= number_format($summary['average_age'], 0, ',', '.') ?> <small>tahun</small></span>
                <span class="stat-meta">
                    <?php if ($summary['youngest'] !== null): ?>
                        Rentang <?= (int) $summary['youngest'] ?>&ndash;<?= (int) $summary['oldest'] ?> tahun
                    <?php else: ?>
                        Belum ada data
                    <?php endif; ?>
                </span>
            </div>
        </article>
    </div>
</div>

<div class="row g-3 g-xl-4 mt-1">
    <div class="col-lg-4">
        <article class="panel h-100">
            <header class="panel-head">
                <div>
                    <h3>Jenis Kelamin</h3>
                    <p>Jumlah pegawai per jenis kelamin</p>
                </div>
                <span class="panel-icon"><i class="bi bi-bar-chart-line"></i></span>
            </header>
            <div class="chart-box">
                <canvas id="genderChart" role="img"
                        aria-label="Grafik batang perbandingan jumlah pegawai laki-laki dan perempuan"></canvas>
            </div>
        </article>
    </div>

    <div class="col-lg-4">
        <article class="panel h-100">
            <header class="panel-head">
                <div>
                    <h3>Pendidikan Terakhir</h3>
                    <p>Sebaran jenjang pendidikan seluruh pegawai</p>
                </div>
                <span class="panel-icon"><i class="bi bi-mortarboard"></i></span>
            </header>
            <div class="chart-box">
                <canvas id="educationChart" role="img"
                        aria-label="Grafik batang jumlah pegawai berdasarkan pendidikan terakhir"></canvas>
            </div>
        </article>
    </div>

    <div class="col-lg-4">
        <article class="panel h-100">
            <header class="panel-head">
                <div>
                    <h3>Kelompok Usia</h3>
                    <p>Distribusi umur pegawai (tahun)</p>
                </div>
                <span class="panel-icon"><i class="bi bi-calendar-heart"></i></span>
            </header>
            <div class="chart-box">
                <canvas id="ageChart" role="img"
                        aria-label="Grafik batang jumlah pegawai berdasarkan kelompok usia"></canvas>
            </div>
        </article>
    </div>
</div>

<div class="row g-3 g-xl-4 mt-1">
    <div class="col-xl-8">
        <article class="panel panel-table h-100">
            <header class="panel-head">
                <div>
                    <h3>Pegawai Terbaru</h3>
                    <p>Lima data pegawai yang terakhir ditambahkan</p>
                </div>
                <a href="<?= e(url('employees/index.php')) ?>" class="btn btn-sm btn-soft">
                    Lihat semua <i class="bi bi-arrow-right"></i>
                </a>
            </header>

            <div class="table-responsive">
                <table class="table app-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Pegawai</th>
                            <th scope="col">Jabatan</th>
                            <th scope="col">Pendidikan</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent === []): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-inline">
                                        <i class="bi bi-inbox"></i>
                                        <span>Belum ada data pegawai.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($recent as $employee): ?>
                            <tr>
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
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="cell-main"><?= e($employee['jabatan']) ?></span>
                                    <span class="cell-sub"><?= e($employee['departemen']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= e(education_badge_class($employee['pendidikan_terakhir'])) ?>">
                                        <?= e($employee['pendidikan_terakhir']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= e(status_badge_class($employee['status'])) ?>">
                                        <?= e($employee['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= e(url('employees/show.php?id=' . (int) $employee['id'])) ?>"
                                       class="btn-icon-sm" title="Lihat detail" aria-label="Lihat detail <?= e($employee['nama']) ?>">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <div class="col-xl-4">
        <article class="panel h-100">
            <header class="panel-head">
                <div>
                    <h3>Status Kepegawaian</h3>
                    <p>Komposisi status seluruh pegawai</p>
                </div>
            </header>

            <?php foreach ($summary['status'] as $status => $count): ?>
                <?php
                $percent = $stats['total'] > 0 ? (int) round($count / $stats['total'] * 100) : 0;
                $barClass = match ($status) {
                    'Aktif'    => 'bar-success',
                    'Cuti'     => 'bar-warning',
                    default    => 'bar-secondary',
                };
                ?>
                <div class="status-row">
                    <div class="status-head">
                        <span><?= e($status) ?></span>
                        <span><strong><?= number_format($count, 0, ',', '.') ?></strong> pegawai &middot; <?= $percent ?>%</span>
                    </div>
                    <div class="progress app-progress">
                        <div class="progress-bar <?= e($barClass) ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="insight-box">
                <i class="bi bi-lightbulb"></i>
                <p>
                    <?php if ($stats['perempuan'] > $stats['laki_laki']): ?>
                        Komposisi pegawai saat ini didominasi oleh <strong>perempuan</strong>.
                    <?php elseif ($stats['laki_laki'] > $stats['perempuan']): ?>
                        Komposisi pegawai saat ini didominasi oleh <strong>laki-laki</strong>.
                    <?php else: ?>
                        Komposisi pegawai saat ini <strong>seimbang</strong> antara laki-laki dan perempuan.
                    <?php endif; ?>
                    Rata-rata usia pegawai adalah <strong><?= (int) $summary['average_age'] ?> tahun</strong>.
                </p>
            </div>
        </article>
    </div>
</div>

<script>
    window.SIMPEG_CHARTS = <?= json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
