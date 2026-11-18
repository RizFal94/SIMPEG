<?php
/**
 * Konfigurasi aplikasi SIMPEG.
 */
return [
    'app' => [
        'name'        => 'SIMPEG',
        'full_name'   => 'Sistem Manajemen Data Pegawai',
        'company'     => 'PT Kanan Kiri',
        'timezone'    => 'Asia/Jakarta',
        'per_page'    => 10,
        'debug'       => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ],

    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'simpeg_ujikom',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
];
