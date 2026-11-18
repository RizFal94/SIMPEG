<?php
/**
 * Modal konfirmasi hapus (kebutuhan non-fungsional).
 * Tombol pemicu cukup menambahkan atribut:
 *   data-bs-toggle="modal" data-bs-target="#deleteModal"
 *   data-delete-id="12" data-delete-name="Nama" data-delete-nip="..."
 */
?>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content delete-modal">
            <form method="post" action="<?= e(url('employees/delete.php')) ?>" id="deleteForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteEmployeeId" value="">

                <div class="modal-body">
                    <div class="delete-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h2 class="delete-title" id="deleteModalLabel">Hapus data pegawai?</h2>
                    <p class="delete-text">
                        Anda akan menghapus data <strong id="deleteEmployeeName">&nbsp;</strong>
                        <span class="delete-nip">NIP <span id="deleteEmployeeNip">&nbsp;</span></span>
                    </p>
                    <p class="delete-warning">
                        Tindakan ini permanen dan data yang sudah dihapus tidak dapat dikembalikan.
                    </p>
                </div>

                <div class="modal-footer delete-actions">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3"></i> Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
