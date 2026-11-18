/**
 * Interaksi UI umum: sidebar mobile, modal konfirmasi hapus,
 * auto-dismiss alert, dan status tombol simpan.
 */
(function () {
    'use strict';

    /* ---------------------------------------------------------- Sidebar -- */
    var sidebar = document.getElementById('appSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggle = document.getElementById('sidebarToggle');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('show');
        document.body.style.overflow = '';
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (sidebar && sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (overlay) overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeSidebar();
    });

    /* --------------------------------------------- Modal konfirmasi hapus -- */
    var deleteModal = document.getElementById('deleteModal');

    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (!trigger) return;

            var id = trigger.getAttribute('data-delete-id') || '';
            var name = trigger.getAttribute('data-delete-name') || '';
            var nip = trigger.getAttribute('data-delete-nip') || '';

            var idField = document.getElementById('deleteEmployeeId');
            var nameField = document.getElementById('deleteEmployeeName');
            var nipField = document.getElementById('deleteEmployeeNip');

            if (idField) idField.value = id;
            if (nameField) nameField.textContent = name;
            if (nipField) nipField.textContent = nip;
        });
    }

    /* ------------------------------------------------- Auto-hide alert -- */
    document.querySelectorAll('.app-alert.alert-success, .app-alert.alert-info').forEach(function (alert) {
        window.setTimeout(function () {
            if (!window.bootstrap || !window.bootstrap.Alert) return;
            var instance = window.bootstrap.Alert.getOrCreateInstance(alert);
            instance.close();
        }, 6000);
    });

    /* -------------------------------------------- Fokus field bermasalah -- */
    var firstInvalid = document.querySelector('.form-control.is-invalid, .form-select.is-invalid');

    if (firstInvalid) {
        window.setTimeout(function () {
            firstInvalid.focus({ preventScroll: false });
            if (firstInvalid.scrollIntoView) {
                firstInvalid.scrollIntoView({ block: 'center', behavior: 'smooth' });
            }
        }, 120);
    }

    /* -------------------------------------- Cegah double submit form -- */
    var employeeForm = document.getElementById('employeeForm');

    if (employeeForm) {
        employeeForm.addEventListener('submit', function () {
            if (!employeeForm.checkValidity()) return;

            var button = employeeForm.querySelector('button[type="submit"]');
            if (!button) return;

            window.setTimeout(function () {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Menyimpan...';
            }, 10);
        });
    }
})();
