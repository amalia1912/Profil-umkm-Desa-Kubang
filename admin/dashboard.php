<?php
require_once '../config/koneksi.php';

$page_title = 'Dashboard';

$jumlah_kategori = $conn->query("SELECT COUNT(*) AS j FROM kategori_usaha")->fetch_assoc()['j'];
$jumlah_umkm = $conn->query("SELECT COUNT(*) AS j FROM umkm")->fetch_assoc()['j'];
$jumlah_produk = $conn->query("SELECT COUNT(*) AS j FROM produk")->fetch_assoc()['j'];
$jumlah_galeri = $conn->query("SELECT COUNT(*) AS j FROM galeri")->fetch_assoc()['j'];

include 'includes/header.php';
?>

<h2 class="mb-4">Dashboard Admin</h2>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-success shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success"><?= (int)$jumlah_kategori ?></h3>
                <p class="mb-0">Kategori Usaha</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-success shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success"><?= (int)$jumlah_umkm ?></h3>
                <p class="mb-0">UMKM</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-success shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success"><?= (int)$jumlah_produk ?></h3>
                <p class="mb-0">Produk</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-success shadow-sm">
            <div class="card-body text-center">
                <h3 class="text-success"><?= (int)$jumlah_galeri ?></h3>
                <p class="mb-0">Galeri</p>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-success mt-4">
    Sistem Informasi Profil UMKM Desa Kubang siap digunakan.
    Silakan kelola data kategori, UMKM, produk, dan galeri melalui menu di atas.
</div>

<?php include 'includes/footer.php'; ?>