<?php
/**
 * Partial form tambah/ubah pegawai.
 *
 * Variabel yang wajib tersedia:
 * - $data       array nilai field (hasil gabungan default / data lama / DB)
 * - $errors     array pesan error per field
 * - $mode       'create' | 'edit'
 * - $formAction URL tujuan submit
 * - $cancelUrl  URL tombol batal
 */
$mode       = $mode ?? 'create';
$formAction = $formAction ?? url('employees/create.php');
$cancelUrl  = $cancelUrl ?? url('employees/index.php');
$errors     = $errors ?? [];

$hasErrors = $errors !== [];
$invalidClass = static fn (string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
?>
<div class="form-layout">
    <form method="post" action="<?= e($formAction) ?>" class="panel form-card" id="employeeForm">
        <?= csrf_field() ?>
        <?php if (isset($employeeId)): ?>
            <input type="hidden" name="id" value="<?= (int) $employeeId ?>">
        <?php endif; ?>

        <?php if ($hasErrors): ?>
            <div class="alert alert-danger app-alert" role="alert">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span>Terdapat <strong><?= count($errors) ?> kesalahan</strong> pada form. Periksa kembali kolom yang ditandai.</span>
            </div>
        <?php endif; ?>

        <section class="form-section">
            <header class="form-section-head">
                <span class="form-section-icon"><i class="bi bi-person-vcard"></i></span>
                <div>
                    <h3>Data Pribadi</h3>
                    <p>Identitas dasar pegawai sesuai dokumen resmi.</p>
                </div>
            </header>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nip" class="form-label">NIP <span class="required">*</span></label>
                    <input type="text" class="form-control<?= $invalidClass('nip') ?>" id="nip" name="nip"
                           value="<?= e($data['nip']) ?>" inputmode="numeric" maxlength="20"
                           placeholder="Contoh: 199003142018071001" autocomplete="off" required>
                    <?php if (isset($errors['nip'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['nip']) ?></div>
                    <?php else: ?>
                        <div class="form-text">5&ndash;20 digit angka, harus unik.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="nama" class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" class="form-control<?= $invalidClass('nama') ?>" id="nama" name="nama"
                           value="<?= e($data['nama']) ?>" maxlength="100"
                           placeholder="Contoh: Andi Pratama" autocomplete="off" required>
                    <?php if (isset($errors['nama'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['nama']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <span class="form-label d-block">Jenis Kelamin <span class="required">*</span></span>
                    <div class="gender-options">
                        <label class="gender-option">
                            <input type="radio" name="jenis_kelamin" value="L"
                                   <?= $data['jenis_kelamin'] === 'L' ? 'checked' : '' ?> required>
                            <span class="gender-card">
                                <i class="bi bi-gender-male"></i> Laki-laki
                            </span>
                        </label>
                        <label class="gender-option">
                            <input type="radio" name="jenis_kelamin" value="P"
                                   <?= $data['jenis_kelamin'] === 'P' ? 'checked' : '' ?> required>
                            <span class="gender-card">
                                <i class="bi bi-gender-female"></i> Perempuan
                            </span>
                        </label>
                    </div>
                    <?php if (isset($errors['jenis_kelamin'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['jenis_kelamin']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="required">*</span></label>
                    <input type="date" class="form-control<?= $invalidClass('tanggal_lahir') ?>"
                           id="tanggal_lahir" name="tanggal_lahir"
                           value="<?= e($data['tanggal_lahir']) ?>"
                           max="<?= e(date('Y-m-d')) ?>" required>
                    <?php if (isset($errors['tanggal_lahir'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['tanggal_lahir']) ?></div>
                    <?php else: ?>
                        <div class="form-text">Usia pegawai harus antara 17&ndash;70 tahun.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                    <input type="email" class="form-control<?= $invalidClass('email') ?>" id="email" name="email"
                           value="<?= e($data['email']) ?>" maxlength="100"
                           placeholder="nama@perusahaan.co.id" autocomplete="off" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="telepon" class="form-label">Nomor Telepon <span class="required">*</span></label>
                    <input type="tel" class="form-control<?= $invalidClass('telepon') ?>" id="telepon" name="telepon"
                           value="<?= e($data['telepon']) ?>" maxlength="16"
                           placeholder="081234567890" autocomplete="off" required>
                    <?php if (isset($errors['telepon'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['telepon']) ?></div>
                    <?php else: ?>
                        <div class="form-text">Diawali 0, 62, atau +62.</div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control<?= $invalidClass('alamat') ?>" id="alamat" name="alamat"
                              rows="3" maxlength="255"
                              placeholder="Jalan, nomor, kota, provinsi"><?= e($data['alamat']) ?></textarea>
                    <?php if (isset($errors['alamat'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['alamat']) ?></div>
                    <?php else: ?>
                        <div class="form-text">Opsional, maksimal 255 karakter.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <hr class="form-divider">

        <section class="form-section">
            <header class="form-section-head">
                <span class="form-section-icon"><i class="bi bi-briefcase"></i></span>
                <div>
                    <h3>Data Kepegawaian</h3>
                    <p>Posisi, penempatan, dan status kerja pegawai.</p>
                </div>
            </header>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="jabatan" class="form-label">Jabatan <span class="required">*</span></label>
                    <input type="text" class="form-control<?= $invalidClass('jabatan') ?>" id="jabatan" name="jabatan"
                           value="<?= e($data['jabatan']) ?>" maxlength="100"
                           list="jabatanOptions" placeholder="Contoh: Software Engineer" autocomplete="off" required>
                    <datalist id="jabatanOptions">
                        <?php foreach (EmployeeOptions::POSITIONS as $position): ?>
                            <option value="<?= e($position) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <?php if (isset($errors['jabatan'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['jabatan']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="departemen" class="form-label">Departemen <span class="required">*</span></label>
                    <select class="form-select<?= $invalidClass('departemen') ?>" id="departemen" name="departemen" required>
                        <option value="">-- Pilih departemen --</option>
                        <?php foreach (EmployeeOptions::DEPARTMENTS as $department): ?>
                            <option value="<?= e($department) ?>" <?= selected_if($data['departemen'], $department) ?>>
                                <?= e($department) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['departemen'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['departemen']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="pendidikan_terakhir" class="form-label">Pendidikan Terakhir <span class="required">*</span></label>
                    <select class="form-select<?= $invalidClass('pendidikan_terakhir') ?>"
                            id="pendidikan_terakhir" name="pendidikan_terakhir" required>
                        <option value="">-- Pilih pendidikan --</option>
                        <?php foreach (EmployeeOptions::EDUCATIONS as $education): ?>
                            <option value="<?= e($education) ?>" <?= selected_if($data['pendidikan_terakhir'], $education) ?>>
                                <?= e($education) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['pendidikan_terakhir'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['pendidikan_terakhir']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status Kepegawaian <span class="required">*</span></label>
                    <select class="form-select<?= $invalidClass('status') ?>" id="status" name="status" required>
                        <?php foreach (EmployeeOptions::STATUSES as $status): ?>
                            <option value="<?= e($status) ?>" <?= selected_if($data['status'], $status) ?>>
                                <?= e($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['status']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="tanggal_masuk" class="form-label">Tanggal Masuk <span class="required">*</span></label>
                    <input type="date" class="form-control<?= $invalidClass('tanggal_masuk') ?>"
                           id="tanggal_masuk" name="tanggal_masuk"
                           value="<?= e($data['tanggal_masuk']) ?>"
                           max="<?= e(date('Y-m-d')) ?>" required>
                    <?php if (isset($errors['tanggal_masuk'])): ?>
                        <div class="invalid-feedback d-block"><?= e($errors['tanggal_masuk']) ?></div>
                    <?php else: ?>
                        <div class="form-text">Tidak boleh melebihi tanggal hari ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <a href="<?= e($cancelUrl) ?>" class="btn btn-light">Batal</a>
            <button type="submit" class="btn btn-brand">
                <i class="bi bi-check2-circle"></i>
                <?= $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Data Pegawai' ?>
            </button>
        </div>
    </form>

    <aside class="form-aside">
        <div class="tip-card">
            <span class="tip-icon"><i class="bi bi-info-circle"></i></span>
            <h4>Panduan pengisian</h4>
            <ul>
                <li><strong>NIP</strong> dan <strong>email</strong> harus unik, tidak boleh sama dengan pegawai lain.</li>
                <li>Usia pegawai dihitung otomatis dari tanggal lahir dan harus 17&ndash;70 tahun.</li>
                <li>Grafik dashboard akan otomatis diperbarui setelah data disimpan.</li>
                <li>Field bertanda <span class="required">*</span> wajib diisi.</li>
            </ul>
        </div>

        <div class="tip-card tip-card-muted">
            <span class="tip-icon"><i class="bi bi-shield-check"></i></span>
            <h4>Data aman</h4>
            <p>Seluruh input divalidasi di server dan disimpan menggunakan <em>prepared statement</em> untuk mencegah SQL Injection.</p>
        </div>
    </aside>
</div>
