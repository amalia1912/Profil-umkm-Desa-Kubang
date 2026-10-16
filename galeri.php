<?php
require_once 'config/koneksi.php';

$page_title = 'Galeri UMKM';

$galeri = $conn->query("SELECT * FROM galeri ORDER BY created_at DESC");

include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Galeri UMKM Desa Kubang</h2>

    <div class="row g-4">
        <?php if ($galeri->num_rows === 0): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Belum ada galeri.
                </div>
            </div>
        <?php else: ?>
            <?php while ($g = $galeri->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <img src="<?= $g['foto'] && file_exists(__DIR__ . '/uploads/' . $g['foto'])
                            ? url('uploads/' . $g['foto'])
                            : 'https://placehold.co/600x400?text=Galeri+Desa' ?>"
                            class="card-img-top"
                            alt="<?= e($g['judul']) ?>">

                        <div class="card-body">
                            <h5 class="card-title"><?= e($g['judul']) ?></h5>
                            <p class="card-text">
                                <?= e(mb_substr(strip_tags($g['deskripsi'] ?? ''), 0, 100)) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>