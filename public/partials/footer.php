        </main>

        <footer class="app-footer">
            <span>&copy; <?= date('Y') ?> <?= e(config('app.full_name')) ?></span>
            <span class="d-none d-sm-inline">Dibangun dengan PHP <?= e(PHP_VERSION) ?> &amp; MySQL</span>
        </footer>
    </div>
</div>

<script src="<?= asset('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php foreach ($pageScripts as $script): ?>
    <script src="<?= asset($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
