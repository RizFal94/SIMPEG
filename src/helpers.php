<?php
/**
 * Kumpulan helper (fungsi bantu) yang dipakai lintas halaman.
 * Semua fungsi di sini murni/utility dan tidak menyentuh database,
 * sehingga mudah diuji dan tidak menimbulkan efek samping tak terduga.
 */
declare(strict_types=1);

/**
 * Membaca konfigurasi dengan dot notation: config('db.host').
 */
function config(?string $path = null, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        $config = require APP_ROOT . '/config/config.php';
    }

    if ($path === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

/**
 * Escape HTML untuk mencegah XSS. Selalu pakai saat mencetak data user.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Membentuk URL internal aplikasi (mendukung sub-folder).
 */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');

    if ($path === '') {
        return BASE_URL === '' ? '/' : BASE_URL . '/';
    }

    return BASE_URL . '/' . $path;
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Menyimpan pesan sekali-tampil (flash message) lalu redirect.
 */
function flash(string $type, string $message): void
{
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

/** @return array<int, array{type:string, message:string}> */
function consume_flashes(): array
{
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);

    return is_array($flashes) ? $flashes : [];
}

/** @param array<string, string> $errors */
function set_errors(array $errors): void
{
    $_SESSION['errors'] = $errors;
}

/** @return array<string, string> */
function consume_errors(): array
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);

    return is_array($errors) ? $errors : [];
}

/** @param array<string, mixed> $old */
function set_old(array $old): void
{
    unset($old['csrf_token']);
    $_SESSION['old'] = $old;
}

/** @return array<string, mixed> */
function consume_old(): array
{
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);

    return is_array($old) ? $old : [];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Memastikan request POST membawa token CSRF yang valid.
 * Jika tidak valid, request dihentikan (fail closed).
 */
function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        abort_page(
            419,
            'Sesi Kedaluwarsa',
            'Token keamanan tidak valid atau sudah kedaluwarsa. Silakan kembali dan ulangi proses.'
        );
    }
}

/**
 * Halaman error minimalis yang tetap memakai identitas visual aplikasi.
 */
function abort_page(int $statusCode, string $title, string $message): void
{
    http_response_code($statusCode);

    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' - ' . e(config('app.name')) . '</title>';
    echo '<style>'
        . 'body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f6fa;'
        . 'font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#0f172a;padding:24px}'
        . '.card{max-width:520px;background:#fff;border-radius:18px;padding:36px;'
        . 'box-shadow:0 18px 50px rgba(15,23,42,.08);text-align:center}'
        . '.code{font-size:52px;font-weight:800;color:#0f766e;line-height:1;margin-bottom:12px}'
        . 'h1{font-size:22px;margin:0 0 10px}p{color:#64748b;line-height:1.6;margin:0 0 22px}'
        . 'a{display:inline-block;background:#0f766e;color:#fff;text-decoration:none;'
        . 'padding:11px 22px;border-radius:10px;font-weight:600}'
        . 'a:hover{background:#115e59}'
        . '</style></head><body><div class="card">';
    echo '<div class="code">' . e((string) $statusCode) . '</div>';
    echo '<h1>' . e($title) . '</h1><p>' . e($message) . '</p>';
    echo '<a href="' . e(url('index.php')) . '">Kembali ke Dashboard</a>';
    echo '</div></body></html>';
    exit;
}

/**
 * Format tanggal Indonesia: 2024-05-01 -> "01 Mei 2024".
 */
function format_date_id(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    try {
        $parsed = new DateTime($date);
    } catch (Exception) {
        return '-';
    }

    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    return $parsed->format('d') . ' ' . $months[(int) $parsed->format('n')] . ' ' . $parsed->format('Y');
}

/**
 * Format tanggal ringkas untuk tabel: 11/02/1970.
 */
function format_date_short(?string $date): string
{
    if ($date === null || $date === '' || !valid_date(substr($date, 0, 10))) {
        return '-';
    }

    return date('d/m/Y', strtotime($date));
}

function valid_date(string $date): bool{
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);

    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function age_from_birthdate(?string $birthdate): ?int
{
    if ($birthdate === null || $birthdate === '' || !valid_date($birthdate)) {
        return null;
    }

    $birth = new DateTime($birthdate);
    $today = new DateTime('today');

    return $birth <= $today ? $birth->diff($today)->y : null;
}

/**
 * Kelompok umur untuk grafik dashboard. Urutan tetap agar warna konsisten.
 */
function age_group_of(int $age): string
{
    if ($age < 25) {
        return '< 25 tahun';
    }
    if ($age <= 34) {
        return '25 - 34 tahun';
    }
    if ($age <= 44) {
        return '35 - 44 tahun';
    }
    if ($age <= 54) {
        return '45 - 54 tahun';
    }

    return '>= 55 tahun';
}

function age_group_order(): array
{
    return ['< 25 tahun', '25 - 34 tahun', '35 - 44 tahun', '45 - 54 tahun', '>= 55 tahun'];
}

function initial_from_name(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $initial = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $initial .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        if (mb_strlen($initial) >= 2) {
            break;
        }
    }

    return $initial !== '' ? $initial : '?';
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'Aktif'   => 'badge-soft-success',
        'Cuti'    => 'badge-soft-warning',
        'Nonaktif'=> 'badge-soft-secondary',
        default   => 'badge-soft-secondary',
    };
}

function gender_badge_class(string $gender): string
{
    return $gender === 'L' ? 'badge-soft-info' : 'badge-soft-pink';
}

function education_badge_class(string $education): string
{
    return match ($education) {
        'S3'      => 'badge-soft-purple',
        'S2'      => 'badge-soft-indigo',
        'S1'      => 'badge-soft-primary',
        'D3'      => 'badge-soft-teal',
        default   => 'badge-soft-secondary',
    };
}

function selected_if(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? 'selected' : '';
}
