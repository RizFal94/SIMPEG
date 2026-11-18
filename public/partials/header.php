<?php
/**
 * Partial header: <head>, sidebar, topbar, dan pembuka area konten.
 *
 * Variabel yang dapat diisi halaman sebelum include:
 * - $pageTitle    (string) judul halaman
 * - $pageSubtitle (string) keterangan di bawah judul
 * - $currentPage  (string) key menu aktif: dashboard|employees|create
 * - $pageScripts  (array)  daftar script tambahan relatif terhadap /assets
 */
if (!defined('BASE_URL')) {
    http_response_code(500);
    exit('Bootstrap aplikasi belum dimuat.');
}

$pageTitle    = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
$currentPage  = $currentPage ?? '';
$pageScripts  = $pageScripts ?? [];
$flashes      = consume_flashes();
$todayLabel   = format_date_id(date('Y-m-d'));

$menu = [
    ['key' => 'dashboard', 'label' => 'Dashboard',      'icon' => 'bi-grid-1x2',     'href' => 'index.php'],
    ['key' => 'employees', 'label' => 'Data Pegawai',    'icon' => 'bi-people',       'href' => 'employees/index.php'],
    ['key' => 'create',    'label' => 'Tambah Pegawai',  'icon' => 'bi-person-plus',  'href' => 'employees/create.php'],
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SIMPEG - Sistem Manajemen Data Pegawai">
    <title><?= e($pageTitle) ?> &middot; <?= e(config('app.name')) ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='16' fill='%230f766e'/%3E%3Ctext x='32' y='45' font-family='Arial' font-size='36' font-weight='bold' fill='white' text-anchor='middle'%3ES%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">S</span>
            <div class="brand-text">
                <span class="brand-name"><?= e(config('app.name')) ?></span>
                <span class="brand-sub">Sistem Manajemen Pegawai</span>
            </div>
        </div>

        <div class="sidebar-label">Menu Utama</div>
        <nav class="sidebar-nav">
            <?php foreach ($menu as $item): ?>
                <a href="<?= e(url($item['href'])) ?>"
                   class="nav-link <?= $currentPage === $item['key'] ? 'active' : '' ?>">
                    <i class="bi <?= e($item['icon']) ?>"></i>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Tentang Aplikasi</div>
        <div class="sidebar-card">
            <i class="bi bi-shield-check"></i>
            <div>
                <strong><?= e(config('app.company')) ?></strong>
                <span>Sistem internal pengelolaan SDM</span>
            </div>
        </div>

        <div class="sidebar-footer">
            <span>&copy; <?= date('Y') ?> <?= e(config('app.name')) ?></span>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-area">
        <header class="topbar">
            <button class="btn-icon d-lg-none" type="button" id="sidebarToggle"
                    aria-label="Buka menu navigasi" aria-controls="appSidebar" aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>

            <div class="topbar-title">
                <h1><?= e($pageTitle) ?></h1>
                <?php if ($pageSubtitle !== ''): ?>
                    <p><?= e($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>

            <div class="topbar-right">
                <span class="topbar-date d-none d-md-inline-flex">
                    <i class="bi bi-calendar3"></i>
                    <?= e($todayLabel) ?>
                </span>
                <div class="user-chip" title="Masuk sebagai Admin">
                    <span class="avatar avatar-sm">AD</span>
                    <span class="user-name d-none d-sm-inline">Admin</span>
                </div>
            </div>
        </header>

        <main class="content">
            <?php foreach ($flashes as $flash): ?>
                <?php
                $type = in_array($flash['type'], ['success', 'danger', 'warning', 'info'], true)
                    ? $flash['type']
                    : 'info';
                $icon = match ($type) {
                    'success' => 'bi-check-circle-fill',
                    'danger'  => 'bi-exclamation-octagon-fill',
                    'warning' => 'bi-exclamation-triangle-fill',
                    default   => 'bi-info-circle-fill',
                };
                ?>
                <div class="alert alert-<?= e($type) ?> alert-dismissible fade show app-alert" role="alert">
                    <i class="bi <?= e($icon) ?>"></i>
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endforeach; ?>
