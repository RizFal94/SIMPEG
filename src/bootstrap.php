<?php
/**
 * Bootstrap aplikasi.
 *
 * Semua halaman wajib meng-include file ini paling atas. Tugasnya:
 * 1. Menyalakan session dengan konfigurasi cookie yang aman.
 * 2. Menetapkan timezone dan konstanta path.
 * 3. Memuat seluruh class/helper yang dibutuhkan.
 * 4. Mendaftarkan exception handler agar error tampil rapi (bukan stack trace mentah).
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/src/helpers.php';

date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Menentukan base URL secara otomatis.
 * Contoh: http://localhost/simpeg/public/employees/index.php -> BASE_URL "/simpeg/public"
 * Aplikasi tetap berjalan baik di root domain maupun di sub-folder.
 */
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$basePath  = preg_replace('#/(employees)$#', '', rtrim($scriptDir, '/'));
define('BASE_URL', $basePath === '' ? '' : $basePath);

require_once APP_ROOT . '/src/Database.php';
require_once APP_ROOT . '/src/EmployeeOptions.php';
require_once APP_ROOT . '/src/EmployeeRepository.php';
require_once APP_ROOT . '/src/EmployeeValidator.php';
require_once APP_ROOT . '/src/DashboardService.php';

// Tampilkan / sembunyikan detail error sesuai konfigurasi.
ini_set('display_errors', config('app.debug') ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $error): void {
    error_log('[SIMPEG] ' . $error->getMessage() . ' @ ' . $error->getFile() . ':' . $error->getLine());

    http_response_code(500);
    $debug   = (bool) config('app.debug');
    $title   = 'Terjadi Kesalahan';
    $message = 'Aplikasi gagal memproses permintaan. Silakan coba lagi.';
    $detail  = $debug
        ? $error->getMessage() . ' (' . $error->getFile() . ':' . $error->getLine() . ')'
        : null;

    require APP_ROOT . '/public/partials/error.php';
    exit;
});
