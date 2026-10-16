<?php
require_once __DIR__ . '/../config/koneksi.php';

$site_name = 'UMKM Desa Kubang';
$site_slogan = 'Sarana Promosi dan Digitalisasi Bisnis Lokal';
$site_kontak = '';
$site_alamat = '';

$pengaturan = $conn->query("SELECT * FROM pengaturan LIMIT 1")->fetch_assoc();

if ($pengaturan) {
    $site_name   = $pengaturan['nama_desa'] ?? $site_name;
    $site_slogan = $pengaturan['slogan'] ?? $site_slogan;
    $site_kontak = $pengaturan['kontak'] ?? '';
    $site_alamat = $pengaturan['alamat'] ?? '';
}

$page_title = $page_title ?? 'Profil UMKM Desa Kubang';
$current = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> - <?= e($site_name) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-umkm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= url('index.php') ?>">
            <span class="brand-badge">UMKM</span>
            <span><?= e($site_name) ?></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarPublic">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= $current === 'index.php' ? 'active' : '' ?>" href="<?= url('index.php') ?>">
                        Beranda
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current === 'umkm.php' || $current === 'detail.php' ? 'active' : '' ?>" href="<?= url('umkm.php') ?>">
                        UMKM
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $current === 'galeri.php' ? 'active' : '' ?>" href="<?= url('galeri.php') ?>">
                        Galeri
                    </a>
                </li>

                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('logout.php') ?>">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-light btn-sm px-3" href="<?= url('login.php') ?>">Login Admin</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>