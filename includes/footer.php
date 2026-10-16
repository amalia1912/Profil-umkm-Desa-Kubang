<footer class="footer-umkm mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="footer-brand"><?= e($site_name) ?></div>
                <p class="mb-0">
                    <?= e($site_slogan) ?>.
                    Website ini dikembangkan untuk memperkenalkan potensi UMKM, produk lokal,
                    dan kegiatan ekonomi masyarakat Desa Kubang.
                </p>
            </div>

            <div class="col-lg-3">
                <div class="footer-title">Tautan</div>
                <ul class="list-unstyled d-grid gap-2 mb-0">
                    <li><a href="<?= url('index.php') ?>">Beranda</a></li>
                    <li><a href="<?= url('umkm.php') ?>">Daftar UMKM</a></li>
                    <li><a href="<?= url('galeri.php') ?>">Galeri</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <div class="footer-title">Kontak Desa</div>
                <p class="mb-1">
                    <strong>Alamat:</strong><br>
                    <?= e($site_alamat ?: 'Desa Kubang, Kecamatan Talun, Kabupaten Cirebon') ?>
                </p>
                <p class="mb-0">
                    <strong>Kontak:</strong><br>
                    <?= e($site_kontak ?: '-') ?>
                </p>
            </div>
        </div>

        <hr class="border-white opacity-25 my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="mb-0 small">
                &copy; <?= date('Y') ?> UMKM Desa Kubang
            </p>
            <p class="mb-0 small">
                KKM Universitas Muhammadiyah Cirebon - Desa Kubang
            </p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>