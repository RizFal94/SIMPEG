<?php
/**
 * EmployeeValidator
 * -----------------
 * Memusatkan aturan validasi data pegawai (server-side).
 * Validasi di sisi browser hanya untuk kenyamanan; aturan final tetap di sini
 * karena request POST bisa dikirim tanpa melewati JavaScript.
 */
declare(strict_types=1);

final class EmployeeValidator
{
    public function __construct(private EmployeeRepository $repository)
    {
    }

    /**
     * Membersihkan input sebelum divalidasi/disimpan.
     *
     * @return array<string, string>
     */
    public function normalize(array $raw): array
    {
        $text = static fn (string $key): string => is_string($raw[$key] ?? null) ? trim($raw[$key]) : '';

        return [
            'nip'                 => preg_replace('/\s+/', '', $text('nip')) ?? '',
            'nama'                => preg_replace('/\s+/', ' ', $text('nama')) ?? '',
            'jenis_kelamin'       => strtoupper($text('jenis_kelamin')),
            'tanggal_lahir'       => $text('tanggal_lahir'),
            'pendidikan_terakhir' => $text('pendidikan_terakhir'),
            'jabatan'             => preg_replace('/\s+/', ' ', $text('jabatan')) ?? '',
            'departemen'          => $text('departemen'),
            'email'               => strtolower($text('email')),
            'telepon'             => preg_replace('/[\s\-]/', '', $text('telepon')) ?? '',
            'alamat'              => $text('alamat'),
            'tanggal_masuk'       => $text('tanggal_masuk'),
            'status'              => ucfirst(strtolower($text('status'))),
        ];
    }

    /**
     * @return array{data: array<string, string>, errors: array<string, string>}
     */
    public function validate(array $raw, ?int $ignoreId = null): array
    {
        $data   = $this->normalize($raw);
        $errors = [];

        // ---- NIP ----
        if ($data['nip'] === '') {
            $errors['nip'] = 'NIP wajib diisi.';
        } elseif (!preg_match('/^[0-9]{5,20}$/', $data['nip'])) {
            $errors['nip'] = 'NIP harus berupa angka 5 sampai 20 digit.';
        } elseif ($this->repository->nipExists($data['nip'], $ignoreId)) {
            $errors['nip'] = 'NIP sudah digunakan oleh pegawai lain.';
        }

        // ---- Nama ----
        if ($data['nama'] === '') {
            $errors['nama'] = 'Nama lengkap wajib diisi.';
        } elseif (mb_strlen($data['nama']) < 3 || mb_strlen($data['nama']) > 100) {
            $errors['nama'] = 'Nama harus terdiri dari 3 sampai 100 karakter.';
        } elseif (!preg_match("/^[a-zA-ZÀ-ÿ.'\- ]+$/u", $data['nama'])) {
            $errors['nama'] = 'Nama hanya boleh berisi huruf, spasi, titik, dan tanda hubung.';
        }

        // ---- Jenis kelamin ----
        if ($data['jenis_kelamin'] === '') {
            $errors['jenis_kelamin'] = 'Jenis kelamin wajib dipilih.';
        } elseif (!EmployeeOptions::isGender($data['jenis_kelamin'])) {
            $errors['jenis_kelamin'] = 'Jenis kelamin tidak valid.';
        }

        // ---- Tanggal lahir & usia ----
        if ($data['tanggal_lahir'] === '') {
            $errors['tanggal_lahir'] = 'Tanggal lahir wajib diisi.';
        } elseif (!valid_date($data['tanggal_lahir'])) {
            $errors['tanggal_lahir'] = 'Format tanggal lahir tidak valid.';
        } elseif ($data['tanggal_lahir'] > date('Y-m-d')) {
            $errors['tanggal_lahir'] = 'Tanggal lahir tidak boleh di masa depan.';
        } else {
            $age = age_from_birthdate($data['tanggal_lahir']);
            if ($age === null || $age < 17 || $age > 70) {
                $errors['tanggal_lahir'] = 'Usia pegawai harus antara 17 sampai 70 tahun.';
            }
        }

        // ---- Pendidikan ----
        if ($data['pendidikan_terakhir'] === '') {
            $errors['pendidikan_terakhir'] = 'Pendidikan terakhir wajib dipilih.';
        } elseif (!EmployeeOptions::isEducation($data['pendidikan_terakhir'])) {
            $errors['pendidikan_terakhir'] = 'Pendidikan terakhir tidak valid.';
        }

        // ---- Jabatan ----
        if ($data['jabatan'] === '') {
            $errors['jabatan'] = 'Jabatan wajib diisi.';
        } elseif (mb_strlen($data['jabatan']) > 100) {
            $errors['jabatan'] = 'Jabatan maksimal 100 karakter.';
        }

        // ---- Departemen ----
        if ($data['departemen'] === '') {
            $errors['departemen'] = 'Departemen wajib dipilih.';
        } elseif (!EmployeeOptions::isDepartment($data['departemen'])) {
            $errors['departemen'] = 'Departemen tidak valid.';
        }

        // ---- Email ----
        if ($data['email'] === '') {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif (mb_strlen($data['email']) > 100) {
            $errors['email'] = 'Email maksimal 100 karakter.';
        } elseif ($this->repository->emailExists($data['email'], $ignoreId)) {
            $errors['email'] = 'Email sudah digunakan oleh pegawai lain.';
        }

        // ---- Telepon ----
        if ($data['telepon'] === '') {
            $errors['telepon'] = 'Nomor telepon wajib diisi.';
        } elseif (!preg_match('/^(\+62|62|0)[0-9]{8,13}$/', $data['telepon'])) {
            $errors['telepon'] = 'Nomor telepon tidak valid (contoh: 081234567890).';
        }

        // ---- Alamat (opsional) ----
        if (mb_strlen($data['alamat']) > 255) {
            $errors['alamat'] = 'Alamat maksimal 255 karakter.';
        }

        // ---- Tanggal masuk ----
        if ($data['tanggal_masuk'] === '') {
            $errors['tanggal_masuk'] = 'Tanggal masuk wajib diisi.';
        } elseif (!valid_date($data['tanggal_masuk'])) {
            $errors['tanggal_masuk'] = 'Format tanggal masuk tidak valid.';
        } elseif ($data['tanggal_masuk'] > date('Y-m-d')) {
            $errors['tanggal_masuk'] = 'Tanggal masuk tidak boleh di masa depan.';
        } elseif (
            $data['tanggal_lahir'] !== ''
            && valid_date($data['tanggal_lahir'])
            && $data['tanggal_masuk'] < $data['tanggal_lahir']
        ) {
            $errors['tanggal_masuk'] = 'Tanggal masuk tidak boleh sebelum tanggal lahir.';
        }

        // ---- Status ----
        if ($data['status'] === '') {
            $errors['status'] = 'Status kepegawaian wajib dipilih.';
        } elseif (!EmployeeOptions::isStatus($data['status'])) {
            $errors['status'] = 'Status kepegawaian tidak valid.';
        }

        return ['data' => $data, 'errors' => $errors];
    }
}
