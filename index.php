<?php
require_once 'config/koneksi.php';

$page_title = 'Beranda';

$jumlah_umkm = $conn->query("SELECT COUNT(*) AS j FROM umkm WHERE status='aktif'")->fetch_assoc()['j'];
$jumlah_produk = $conn->query("SELECT COUNT(*) AS j FROM produk")->fetch_assoc()['j'];
$jumlah_galeri = $conn->query("SELECT COUNT(*) AS j FROM galeri")->fetch_assoc()['j'];

$kategori_umkm = $conn->query("
    SELECT k.*, COUNT(u.id_umkm) AS jumlah
    FROM kategori_usaha k
    LEFT JOIN umkm u ON k.id_kategori = u.id_kategori AND u.status = 'aktif'
    GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi
    ORDER BY jumlah DESC, k.nama_kategori ASC
    LIMIT 8
");

$umkm_terbaru = $conn->query("
    SELECT u.*, k.nama_kategori
    FROM umkm u
    LEFT JOIN kategori_usaha k ON u.id_kategori = k.id_kategori
    WHERE u.status = 'aktif'
    ORDER BY u.created_at DESC
    LIMIT 6
");

$produk_terbaru = $conn->query("
    SELECT p.*, u.nama_umkm
    FROM produk p
    JOIN umkm u ON p.id_umkm = u.id_umkm
    WHERE u.status = 'aktif'
    ORDER BY p.created_at DESC
    LIMIT 8
");

$galeri_terbaru = $conn->query("
    SELECT * FROM galeri
    ORDER BY created_at DESC
    LIMIT 3
");

include 'includes/header.php';
?>

<div class="container py-4">

    <section class="hero-section p-4 p-lg-5 mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="hero-badge">Desa Kubang • Kecamatan Talun • Kabupaten Cirebon</span>

                <h1 class="hero-title mt-3">
                    Sistem Informasi Profil UMKM Desa Kubang
                </h1>

                <p class="hero-text mt-3">
                    Platform promosi dan digitalisasi bisnis lokal untuk memperkenalkan
                    produk unggulan UMKM Desa Kubang, seperti opak becak, home industry,
                    warung klontong, kios tani, kerajinan, dan kuliner lokal.
                </p>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="<?= url('umkm.php') ?>" class="btn btn-light btn-hero">
                        Jelajahi UMKM
                    </a>
                    <a href="<?= url('galeri.php') ?>" class="btn btn-outline-light btn-hero">
                        Lihat Galeri
                    </a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-4">
                        <div class="stat-card p-3 h-100 text-center">
                            <h3><?= (int)$jumlah_umkm ?></h3>
                            <p>UMKM</p>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="stat-card p-3 h-100 text-center">
                            <h3><?= (int)$jumlah_produk ?></h3>
                            <p>Produk</p>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="stat-card p-3 h-100 text-center">
                            <h3><?= (int)$jumlah_galeri ?></h3>
                            <p>Galeri</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <span class="section-subtitle">Kategori Usaha</span>
        <h3 class="section-title mb-4">Jelajahi Jenis UMKM</h3>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($kategori_umkm->num_rows === 0): ?>
                <div class="alert alert-info mb-0">Belum ada kategori usaha.</div>
            <?php else: ?>
                <?php while ($k = $kategori_umkm->fetch_assoc()): ?>
                    <a href="<?= url('umkm.php?kategori=' . (int)$k['id_kategori']) ?>" class="category-chip">
                        <?= e($k['nama_kategori']) ?>
                        <span class="badge bg-success"><?= (int)$k['jumlah'] ?></span>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-subtitle">Profil UMKM</span>
                <h3 class="section-title mb-0">UMKM Terbaru</h3>
            </div>

            <a href="<?= url('umkm.php') ?>" class="btn btn-outline-success">Lihat Semua</a>
        </div>

        <div class="row g-4">
            <?php if ($umkm_terbaru->num_rows === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Belum ada data UMKM. Silakan tambahkan melalui halaman admin.
                    </div>
                </div>
            <?php else: ?>
                <?php while ($u = $umkm_terbaru->fetch_assoc()): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-hover h-100">
                            <img src="<?= $u['foto'] && file_exists(__DIR__ . '/uploads/' . $u['foto'])
                                ? url('uploads/' . $u['foto'])
                                : 'https://placehold.co/800x500?text=' . urlencode(e($u['nama_umkm'])) ?>"
                                class="card-img-top img-cover"
                                alt="<?= e($u['nama_umkm']) ?>">

                            <div class="card-body">
                                <span class="badge-category mb-2 d-inline-block">
                                    <?= e($u['nama_kategori'] ?? 'Umum') ?>
                                </span>

                                <h5 class="fw-bold mb-1"><?= e($u['nama_umkm']) ?></h5>
                                <p class="text-muted mb-2">Pemilik: <?= e($u['pemilik'] ?: '-') ?></p>
                                <p class="mb-3">
                                    <?= e(mb_substr(strip_tags($u['deskripsi'] ?? ''), 0, 90)) ?>...
                                </p>

                                <a href="<?= url('detail.php?id=' . (int)$u['id_umkm']) ?>" class="btn btn-success btn-sm">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-subtitle">Katalog Produk</span>
                <h3 class="section-title mb-0">Produk UMKM</h3>
            </div>
        </div>

        <div class="row g-4">
            <?php if ($produk_terbaru->num_rows === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">Belum ada produk UMKM.</div>
                </div>
            <?php else: ?>
                <?php while ($p = $produk_terbaru->fetch_assoc()): ?>
                    <div class="col-md-6 col-xl-3">
                        <div class="card card-hover h-100">
                            <img src="<?= $p['foto'] && file_exists(__DIR__ . '/uploads/' . $p['foto'])
                                ? url('uploads/' . $p['foto'])
                                : 'https://placehold.co/800x500?text=' . urlencode(e($p['nama_produk'])) ?>"
                                class="card-img-top img-cover"
                                alt="<?= e($p['nama_produk']) ?>">

                            <div class="card-body">
                                <h6 class="fw-bold mb-1"><?= e($p['nama_produk']) ?></h6>
                                <p class="text-muted mb-2 small">UMKM: <?= e($p['nama_umkm']) ?></p>
                                <div class="product-price">
                                    <?= format_rupiah($p['harga']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-subtitle">Dokumentasi</span>
                <h3 class="section-title mb-0">Galeri Desa</h3>
            </div>

            <a href="<?= url('galeri.php') ?>" class="btn btn-outline-success">Lihat Galeri</a>
        </div>

        <div class="row g-4">
            <?php if ($galeri_terbaru->num_rows === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">Belum ada galeri.</div>
                </div>
            <?php else: ?>
                <?php while ($g = $galeri_terbaru->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card card-hover h-100">
                            <img src="<?= $g['foto'] && file_exists(__DIR__ . '/uploads/' . $g['foto'])
                                ? url('uploads/' . $g['foto'])
                                : 'https://placehold.co/800x500?text=Galeri+Desa' ?>"
                                class="card-img-top img-cover"
                                alt="<?= e($g['judul']) ?>">

                            <div class="card-body">
                                <h6 class="fw-bold mb-1"><?= e($g['judul']) ?></h6>
                                <p class="mb-0 text-muted small">
                                    <?= e(mb_substr(strip_tags($g['deskripsi'] ?? ''), 0, 70)) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php include 'includes/footer.php'; ?>