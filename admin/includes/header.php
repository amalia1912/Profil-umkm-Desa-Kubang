<?php
require_once __DIR__ . '/../../config/koneksi.php';

require_login();

$page_title = $page_title ?? 'Dashboard';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> - Admin UMKM Desa Kubang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('admin/dashboard.php') ?>">
            Admin UMKM Kubang
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/kategori.php') ?>">Kategori</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/umkm.php') ?>">UMKM</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/produk.php') ?>">Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/galeri.php') ?>">Galeri</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <span class="navbar-text me-lg-3">
                        Halo, <?= e($_SESSION['nama'] ?? 'Admin') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('index.php') ?>" target="_blank">Lihat Situs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?= url('logout.php') ?>">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">