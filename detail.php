<?php
require_once 'config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT u.*, k.nama_kategori
    FROM umkm u
    LEFT JOIN kategori_usaha k ON u.id_kategori = k.id_kategori
    WHERE u.id_umkm = ? AND u.status = 'aktif'
");

$stmt->bind_param('i', $id);
$stmt->execute();
$umkm = $stmt->get_result()->fetch_assoc();

$page_title = $umkm['nama_umkm'] ?? 'Detail UMKM';

$produk_stmt = $conn->prepare("
    SELECT * FROM produk
    WHERE id_umkm = ?
    ORDER BY created_at DESC
");

$produk_stmt->bind_param('i', $id);
$produk_stmt->execute();
$produk_list = $produk_stmt->get_result();

include 'includes/header.php';
?>

<div class="container py-5">
    <?php if (!$umkm): ?>
        <div class="alert alert-warning">
            UMKM tidak ditemukan.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-md-5">
                <img src="<?= $umkm['foto'] && file_exists(__DIR__ . '/uploads/' . $umkm['foto'])
                    ? url('uploads/' . $umkm['foto'])
                    : 'https://placehold.co/600x400?text=UMKM+Desa+Kubang' ?>"
                    class="img-detail"
                    alt="<?= e($umkm['nama_umkm']) ?>">
            </div>

            <div class="col-md-7">
                <span class="badge bg-success mb-2">
                    <?= e($umkm['nama_kategori'] ?? 'Umum') ?>
                </span>

                <h2><?= e($umkm['nama_umkm']) ?></h2>

                <table class="table table-bordered mt-3">
                    <tr>
                        <th style="width: 200px;">Pemilik</th>
                        <td><?= e($umkm['pemilik'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td><?= e($umkm['alamat'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Kontak</th>
                        <td><?= e($umkm['kontak'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-success"><?= e(ucfirst($umkm['status'])) ?></span>
                        </td>
                    </tr>
                </table>

                <?php if ($umkm['kontak']): ?>
                    <a href="<?= wa_link($umkm['kontak']) ?>" target="_blank" class="btn btn-success">
                        Hubungi via WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-5">
            <h4>Deskripsi Usaha</h4>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?= nl2br(e($umkm['deskripsi'] ?: 'Belum ada deskripsi.')) ?>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h4>Produk UMKM</h4>

            <div class="row g-4 mt-1">
                <?php if ($produk_list->num_rows === 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            Belum ada produk untuk UMKM ini.
                        </div>
                    </div>
                <?php else: ?>
                    <?php while ($p = $produk_list->fetch_assoc()): ?>
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <img src="<?= $p['foto'] && file_exists(__DIR__ . '/uploads/' . $p['foto'])
                                    ? url('uploads/' . $p['foto'])
                                    : 'https://placehold.co/600x400?text=Produk+UMKM' ?>"
                                    class="card-img-top"
                                    alt="<?= e($p['nama_produk']) ?>">

                                <div class="card-body">
                                    <h5 class="card-title"><?= e($p['nama_produk']) ?></h5>
                                    <p class="text-success fw-bold">
                                        <?= format_rupiah($p['harga']) ?>
                                    </p>
                                    <p class="card-text">
                                        <?= e(mb_substr(strip_tags($p['deskripsi'] ?? ''), 0, 80)) ?>...
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>