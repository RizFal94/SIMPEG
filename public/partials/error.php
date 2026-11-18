<?php
/**
 * Halaman error mandiri (tidak memakai header/footer) supaya tetap tampil
 * walau koneksi database bermasalah.
 *
 * Variabel: $title, $message, $detail (opsional).
 */
$title   = $title ?? 'Terjadi Kesalahan';
$message = $message ?? 'Permintaan tidak dapat diproses.';
$detail  = $detail ?? null;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> &middot; <?= e(config('app.name')) ?></title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
            background: #f4f6fa; color: #0f172a;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .error-card {
            width: min(620px, 100%); background: #fff; border-radius: 18px; padding: 40px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .09); text-align: center;
        }
        .error-icon {
            width: 64px; height: 64px; margin: 0 auto 18px; border-radius: 18px;
            display: grid; place-items: center; background: #fef2f2; color: #dc2626; font-size: 28px;
        }
        h1 { font-size: 22px; margin: 0 0 10px; }
        p { margin: 0 0 20px; color: #64748b; line-height: 1.65; }
        code {
            display: block; text-align: left; background: #0f172a; color: #e2e8f0;
            padding: 14px 16px; border-radius: 10px; font-size: 12.5px;
            overflow-x: auto; margin-bottom: 22px; white-space: pre-wrap; word-break: break-word;
        }
        a {
            display: inline-block; background: #0f766e; color: #fff; text-decoration: none;
            padding: 11px 22px; border-radius: 10px; font-weight: 600;
        }
        a:hover { background: #115e59; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">&#9888;</div>
        <h1><?= e($title) ?></h1>
        <p><?= e($message) ?></p>
        <?php if ($detail !== null): ?>
            <code><?= e($detail) ?></code>
        <?php endif; ?>
        <a href="<?= e(url('index.php')) ?>">Kembali ke Dashboard</a>
    </div>
</body>
</html>
