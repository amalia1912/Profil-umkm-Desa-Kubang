<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include '../koneksi.php';

// Ambil data user yang login
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? 'Admin';
$role = $_SESSION['role'] ?? 'admin';

// ============================================
// PROSES CRUD BERDASARKAN HALAMAN
// ============================================
$message = '';
$message_type = 'success';

// --- HALAMAN: UMKM ---
if (isset($_GET['page']) && $_GET['page'] == 'umkm') {
    
    // TAMBAH UMKM
    if (isset($_POST['tambah_umkm'])) {
        $nama_usaha = mysqli_real_escape_string($conn, $_POST['nama_usaha']);
        $nama_pemilik = mysqli_real_escape_string($conn, $_POST['nama_pemilik']);
        $kategori = (int)$_POST['kategori'];
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        $foto_name = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto_name = 'umkm_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto_name);
            }
        }
        
        $sql = "INSERT INTO data_umkm (nama_usaha, nama_pemilik, id_kategori, alamat, deskripsi, kontak, foto, status) 
                VALUES ('$nama_usaha', '$nama_pemilik', '$kategori', '$alamat', '$deskripsi', '$kontak', '$foto_name', '$status')";
        if (mysqli_query($conn, $sql)) {
            $message = "Data UMKM berhasil ditambahkan!";
        } else {
            $message = "Gagal: " . mysqli_error($conn);
            $message_type = 'danger';
        }
    }
    
    // EDIT UMKM
    if (isset($_POST['edit_umkm'])) {
        $id = (int)$_POST['id'];
        $nama_usaha = mysqli_real_escape_string($conn, $_POST['nama_usaha']);
        $nama_pemilik = mysqli_real_escape_string($conn, $_POST['nama_pemilik']);
        $kategori = (int)$_POST['kategori'];
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        $foto_update = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto_name = 'umkm_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto_name);
                $foto_update = ", foto='$foto_name'";
            }
        }
        
        $sql = "UPDATE data_umkm SET nama_usaha='$nama_usaha', nama_pemilik='$nama_pemilik', 
                id_kategori='$kategori', alamat='$alamat', deskripsi='$deskripsi', 
                kontak='$kontak', status='$status' $foto_update WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            $message = "Data UMKM berhasil diperbarui!";
        } else {
            $message = "Gagal: " . mysqli_error($conn);
            $message_type = 'danger';
        }
    }
    
    // HAPUS UMKM
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        $q = mysqli_query($conn, "SELECT foto FROM data_umkm WHERE id=$id");
        if ($r = mysqli_fetch_assoc($q)) {
            if ($r['foto'] && file_exists('../uploads/' . $r['foto'])) {
                unlink('../uploads/' . $r['foto']);
            }
        }
        mysqli_query($conn, "DELETE FROM data_umkm WHERE id=$id");
        $message = "Data UMKM berhasil dihapus!";
    }
    
    // APPROVE / REJECT
    if (isset($_GET['approve'])) {
        $id = (int)$_GET['approve'];
        mysqli_query($conn, "UPDATE data_umkm SET status='approved' WHERE id=$id");
        $message = "UMKM disetujui dan ditampilkan di website!";
    }
    if (isset($_GET['reject'])) {
        $id = (int)$_GET['reject'];
        mysqli_query($conn, "UPDATE data_umkm SET status='rejected' WHERE id=$id");
        $message = "UMKM ditolak.";
    }
}

// --- HALAMAN: KATEGORI ---
if (isset($_GET['page']) && $_GET['page'] == 'kategori') {
    if (isset($_POST['tambah_kategori'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
        mysqli_query($conn, "INSERT INTO kategori_usaha (nama_kategori) VALUES ('$nama')");
        $message = "Kategori berhasil ditambahkan!";
    }
    if (isset($_POST['edit_kategori'])) {
        $id = (int)$_POST['id'];
        $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
        mysqli_query($conn, "UPDATE kategori_usaha SET nama_kategori='$nama' WHERE id=$id");
        $message = "Kategori berhasil diperbarui!";
    }
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        mysqli_query($conn, "DELETE FROM kategori_usaha WHERE id=$id");
        $message = "Kategori berhasil dihapus!";
    }
}

// --- HALAMAN: PRODUK ---
if (isset($_GET['page']) && $_GET['page'] == 'produk') {
    if (isset($_POST['tambah_produk'])) {
        $id_umkm = (int)$_POST['id_umkm'];
        $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $harga = (float)$_POST['harga'];
        $kat_produk = mysqli_real_escape_string($conn, $_POST['kategori_produk']);
        
        $foto_name = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto_name = 'produk_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto_name);
            }
        }
        
        $sql = "INSERT INTO produk (id_umkm, nama_produk, deskripsi, harga, foto, kategori_produk) 
                VALUES ($id_umkm, '$nama', '$deskripsi', $harga, '$foto_name', '$kat_produk')";
        mysqli_query($conn, $sql);
        $message = "Produk berhasil ditambahkan!";
    }
    if (isset($_POST['edit_produk'])) {
        $id = (int)$_POST['id'];
        $id_umkm = (int)$_POST['id_umkm'];
        $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $harga = (float)$_POST['harga'];
        $kat_produk = mysqli_real_escape_string($conn, $_POST['kategori_produk']);
        
        $foto_update = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $foto_name = 'produk_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto_name);
            $foto_update = ", foto='$foto_name'";
        }
        
        mysqli_query($conn, "UPDATE produk SET id_umkm=$id_umkm, nama_produk='$nama', 
                deskripsi='$deskripsi', harga=$harga, kategori_produk='$kat_produk' $foto_update WHERE id=$id");
        $message = "Produk berhasil diperbarui!";
    }
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        $q = mysqli_query($conn, "SELECT foto FROM produk WHERE id=$id");
        if ($r = mysqli_fetch_assoc($q)) {
            if ($r['foto'] && file_exists('../uploads/' . $r['foto'])) unlink('../uploads/' . $r['foto']);
        }
        mysqli_query($conn, "DELETE FROM produk WHERE id=$id");
        $message = "Produk berhasil dihapus!";
    }
}

// --- HALAMAN: GALERI ---
if (isset($_GET['page']) && $_GET['page'] == 'galeri') {
    if (isset($_POST['tambah_galeri'])) {
        $judul = mysqli_real_escape_string($conn, $_POST['judul']);
        $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        
        $foto_name = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto_name = 'galeri_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $foto_name);
            }
        }
        
        mysqli_query($conn, "INSERT INTO galeri (judul, deskripsi, foto) VALUES ('$judul', '$deskripsi', '$foto_name')");
        $message = "Galeri berhasil ditambahkan!";
    }
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        $q = mysqli_query($conn, "SELECT foto FROM galeri WHERE id=$id");
        if ($r = mysqli_fetch_assoc($q)) {
            if ($r['foto'] && file_exists('../uploads/' . $r['foto'])) unlink('../uploads/' . $r['foto']);
        }
        mysqli_query($conn, "DELETE FROM galeri WHERE id=$id");
        $message = "Galeri berhasil dihapus!";
    }
}

// --- HALAMAN: PENGGUNA ---
if (isset($_GET['page']) && $_GET['page'] == 'users') {
    if (isset($_POST['tambah_user'])) {
        $uname = mysqli_real_escape_string($conn, $_POST['username']);
        $pass = mysqli_real_escape_string($conn, $_POST['password']);
        $role_u = mysqli_real_escape_string($conn, $_POST['role']);
        mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$uname', '$pass', '$role_u')");
        $message = "Pengguna berhasil ditambahkan!";
    }
    if (isset($_POST['edit_user'])) {
        $id = (int)$_POST['id'];
        $uname = mysqli_real_escape_string($conn, $_POST['username']);
        $role_u = mysqli_real_escape_string($conn, $_POST['role']);
        $pass = $_POST['password'];
        
        if (!empty($pass)) {
            mysqli_query($conn, "UPDATE users SET username='$uname', role='$role_u', password='$pass' WHERE id=$id");
        } else {
            mysqli_query($conn, "UPDATE users SET username='$uname', role='$role_u' WHERE id=$id");
        }
        $message = "Pengguna berhasil diperbarui!";
    }
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        if ($id != $user_id) {
            mysqli_query($conn, "DELETE FROM users WHERE id=$id");
            $message = "Pengguna berhasil dihapus!";
        } else {
            $message = "Tidak bisa menghapus akun sendiri!";
            $message_type = 'warning';
        }
    }
}

// --- HALAMAN: PENGATURAN ---
if (isset($_GET['page']) && $_GET['page'] == 'pengaturan') {
    if (isset($_POST['simpan_pengaturan'])) {
        $nama_desa = mysqli_real_escape_string($conn, $_POST['nama_desa']);
        $sambutan = mysqli_real_escape_string($conn, $_POST['sambutan']);
        $kontak_desa = mysqli_real_escape_string($conn, $_POST['kontak_desa']);
        $alamat_desa = mysqli_real_escape_string($conn, $_POST['alamat_desa']);
        
        $logo_update = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $logo_name = 'logo_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], '../uploads/' . $logo_name);
            $logo_update = ", logo='$logo_name'";
        }
        
        $banner_update = '';
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
            $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
            $banner_name = 'banner_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['banner']['tmp_name'], '../uploads/' . $banner_name);
            $banner_update = ", banner='$banner_name'";
        }
        
        // Cek apakah pengaturan sudah ada
        $cek = mysqli_query($conn, "SELECT id FROM pengaturan LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "UPDATE pengaturan SET nama_desa='$nama_desa', sambutan='$sambutan', 
                    kontak_desa='$kontak_desa', alamat_desa='$alamat_desa' $logo_update $banner_update WHERE id=1");
        } else {
            mysqli_query($conn, "INSERT INTO pengaturan (nama_desa, sambutan, kontak_desa, alamat_desa) 
                    VALUES ('$nama_desa', '$sambutan', '$kontak_desa', '$alamat_desa')");
        }
        $message = "Pengaturan website berhasil disimpan!";
    }
}

// --- HALAMAN: PESAN ---
if (isset($_GET['page']) && $_GET['page'] == 'pesan') {
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        mysqli_query($conn, "DELETE FROM pesan WHERE id=$id");
        $message = "Pesan berhasil dihapus!";
    }
    if (isset($_GET['baca'])) {
        $id = (int)$_GET['baca'];
        mysqli_query($conn, "UPDATE pesan SET status='dibaca' WHERE id=$id");
    }
}

// ============================================
// AMBIL DATA UNTUK TAMPILAN
// ============================================
$page = $_GET['page'] ?? 'dashboard';

// Statistik Dashboard
$stat_umkm = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM data_umkm"));
$stat_umkm_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM data_umkm WHERE status='approved'"));
$stat_umkm_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM data_umkm WHERE status='pending'"));
$stat_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori_usaha"));
$stat_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk"));
$stat_galeri = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM galeri"));
$stat_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"));
$stat_pesan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesan WHERE status='belum_dibaca'"));

// Rekap per kategori
$rekap_kategori = mysqli_query($conn, "SELECT k.nama_kategori, COUNT(d.id) as jumlah 
    FROM kategori_usaha k LEFT JOIN data_umkm d ON k.id = d.id_kategori 
    GROUP BY k.id ORDER BY jumlah DESC");

// Data untuk dropdown
$kategoris = mysqli_query($conn, "SELECT * FROM kategori_usaha ORDER BY nama_kategori");
$umkms = mysqli_query($conn, "SELECT * FROM data_umkm ORDER BY nama_usaha");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - UMKM Desa Kubang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --primary-dark: #1a252f;
            --secondary: #34495e;
            --accent: #f39c12;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --light: #ecf0f1;
        }
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .sidebar {
            background: var(--primary);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 20px;
            background: var(--primary-dark);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h4 {
            margin: 10px 0 0 0;
            font-size: 16px;
        }
        .sidebar-header small {
            opacity: 0.7;
            font-size: 12px;
        }
        .sidebar-menu {
            padding: 15px 0;
        }
        .sidebar-menu a {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            font-size: 14px;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: var(--secondary);
            color: white;
            border-left-color: var(--accent);
        }
        .sidebar-menu a i {
            width: 25px;
            margin-right: 10px;
        }
        .sidebar-menu .badge {
            float: right;
            background: var(--danger);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .topbar {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-card.success { border-left-color: var(--success); }
        .stat-card.warning { border-left-color: var(--warning); }
        .stat-card.danger { border-left-color: var(--danger); }
        .stat-card.info { border-left-color: #3498db; }
        .stat-card .icon {
            font-size: 32px;
            opacity: 0.2;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        .stat-card h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: var(--primary);
        }
        .stat-card p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 13px;
        }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .content-card h5 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
        }
        .btn-primary-custom {
            background: var(--primary);
            border: none;
            color: white;
        }
        .btn-primary-custom:hover {
            background: var(--primary-dark);
            color: white;
        }
        .table thead {
            background: var(--light);
        }
        .table thead th {
            border: none;
            font-weight: 600;
            color: var(--primary);
            font-size: 13px;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .img-thumbnail-sm {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-store fa-2x"></i>
        <h4>UMKM Desa Kubang</h4>
        <small>Admin Dashboard</small>
    </div>
    <div class="sidebar-menu">
        <a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="?page=umkm" class="<?= $page == 'umkm' ? 'active' : '' ?>">
            <i class="fas fa-store"></i> Data UMKM
            <?php if ($stat_umkm_pending['total'] > 0): ?>
                <span class="badge"><?= $stat_umkm_pending['total'] ?></span>
            <?php endif; ?>
        </a>
        <a href="?page=kategori" class="<?= $page == 'kategori' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Kategori Usaha
        </a>
        <a href="?page=produk" class="<?= $page == 'produk' ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Katalog Produk
        </a>
        <a href="?page=galeri" class="<?= $page == 'galeri' ? 'active' : '' ?>">
            <i class="fas fa-images"></i> Galeri
        </a>
        <a href="?page=users" class="<?= $page == 'users' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Pengguna
        </a>
        <a href="?page=pesan" class="<?= $page == 'pesan' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Pesan Masuk
            <?php if ($stat_pesan['total'] > 0): ?>
                <span class="badge"><?= $stat_pesan['total'] ?></span>
            <?php endif; ?>
        </a>
        <a href="?page=pengaturan" class="<?= $page == 'pengaturan' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Pengaturan
        </a>
        <hr style="border-color: rgba(255,255,255,0.1);">
        <a href="../index.php" target="_blank">
            <i class="fas fa-external-link-alt"></i> Lihat Website
        </a>
        <a href="logout.php" style="color: #e74c3c;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    
    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <span class="ms-2 fw-bold text-secondary">
                <i class="fas fa-home me-1"></i> 
                <?= ucfirst($page == 'dashboard' ? 'Dashboard' : str_replace('_', ' ', $page)) ?>
            </span>
        </div>
        <div class="d-flex align-items-center">
            <span class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></span>
            <div>
                <div class="fw-bold" style="font-size: 14px;"><?= htmlspecialchars($username) ?></div>
                <small class="text-muted"><?= ucfirst($role) ?></small>
            </div>
        </div>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : ($message_type == 'danger' ? 'exclamation-circle' : 'info-circle') ?> me-2"></i>
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: DASHBOARD -->
    <!-- ============================================ -->
    <?php if ($page == 'dashboard'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <i class="fas fa-store icon"></i>
                <h3><?= $stat_umkm['total'] ?></h3>
                <p>Total UMKM Terdaftar</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success position-relative">
                <i class="fas fa-check-circle icon"></i>
                <h3><?= $stat_umkm_approved['total'] ?></h3>
                <p>UMKM Disetujui</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning position-relative">
                <i class="fas fa-clock icon"></i>
                <h3><?= $stat_umkm_pending['total'] ?></h3>
                <p>Menunggu Persetujuan</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info position-relative">
                <i class="fas fa-box icon"></i>
                <h3><?= $stat_produk['total'] ?></h3>
                <p>Total Produk</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <i class="fas fa-tags icon"></i>
                <h3><?= $stat_kategori['total'] ?></h3>
                <p>Kategori Usaha</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <i class="fas fa-images icon"></i>
                <h3><?= $stat_galeri['total'] ?></h3>
                <p>Foto Galeri</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card position-relative">
                <i class="fas fa-users icon"></i>
                <h3><?= $stat_user['total'] ?></h3>
                <p>Total Pengguna</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card danger position-relative">
                <i class="fas fa-envelope icon"></i>
                <h3><?= $stat_pesan['total'] ?></h3>
                <p>Pesan Belum Dibaca</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="content-card">
                <h5><i class="fas fa-chart-bar me-2"></i>Rekap UMKM per Kategori</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Jumlah UMKM</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $max = $stat_umkm['total'] > 0 ? $stat_umkm['total'] : 1;
                            while($k = mysqli_fetch_assoc($rekap_kategori)): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($k['nama_kategori']) ?></td>
                                <td><strong><?= $k['jumlah'] ?></strong></td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: <?= ($k['jumlah']/$max)*100 ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="content-card">
                <h5><i class="fas fa-clock me-2"></i>UMKM Menunggu Persetujuan</h5>
                <?php
                $pending = mysqli_query($conn, "SELECT * FROM data_umkm WHERE status='pending' ORDER BY created_at DESC LIMIT 5");
                if (mysqli_num_rows($pending) > 0):
                    while($p = mysqli_fetch_assoc($pending)):
                ?>
                <div class="d-flex align-items-center mb-3 p-2 border-bottom">
                    <img src="../uploads/<?= $p['foto'] ?: 'placeholder.png' ?>" 
                         class="img-thumbnail-sm me-3" 
                         onerror="this.src='https://via.placeholder.com/60?text=No+Img'">
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size: 13px;"><?= htmlspecialchars($p['nama_usaha']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($p['nama_pemilik']) ?></small>
                    </div>
                    <a href="?page=umkm&approve=<?= $p['id'] ?>" class="btn btn-sm btn-success" title="Setujui">
                        <i class="fas fa-check"></i>
                    </a>
                </div>
                <?php endwhile; else: ?>
                <p class="text-muted text-center py-3">Tidak ada UMKM pending</p>
                <?php endif; ?>
            </div>

            <div class="content-card">
                <h5><i class="fas fa-info-circle me-2"></i>Informasi Sistem</h5>
                <ul class="list-unstyled mb-0" style="font-size: 13px;">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>PHP Version: <?= phpversion() ?></li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>MySQL: Aktif</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Folder Uploads: 
                        <?php echo is_writable('../uploads') ? '<span class="text-success">Writable</span>' : '<span class="text-danger">Not Writable</span>'; ?>
                    </li>
                    <li><i class="fas fa-calendar me-2"></i>Tanggal: <?= date('d M Y') ?></li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: DATA UMKM -->
    <!-- ============================================ -->
    <?php if ($page == 'umkm'): ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-store me-2"></i>Kelola Data UMKM</h5>
            <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUMKM">
                <i class="fas fa-plus me-1"></i> Tambah UMKM
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama Usaha</th>
                        <th>Pemilik</th>
                        <th>Kategori</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = mysqli_query($conn, "SELECT d.*, k.nama_kategori FROM data_umkm d 
                        LEFT JOIN kategori_usaha k ON d.id_kategori = k.id ORDER BY d.id DESC");
                    while($r = mysqli_fetch_assoc($q)):
                        $status_class = $r['status'] == 'approved' ? 'status-approved' : 
                                       ($r['status'] == 'pending' ? 'status-pending' : 'status-rejected');
                        $status_text = $r['status'] == 'approved' ? 'Disetujui' : 
                                       ($r['status'] == 'pending' ? 'Pending' : 'Ditolak');
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <img src="../uploads/<?= $r['foto'] ?>" class="img-thumbnail-sm" 
                                 onerror="this.src='https://via.placeholder.com/60?text=No+Img'">
                        </td>
                        <td><strong><?= htmlspecialchars($r['nama_usaha']) ?></strong></td>
                        <td><?= htmlspecialchars($r['nama_pemilik']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($r['nama_kategori'] ?? '-') ?></span></td>
                        <td><small><?= htmlspecialchars($r['kontak']) ?></small></td>
                        <td><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                        <td>
                            <?php if ($r['status'] == 'pending'): ?>
                                <a href="?page=umkm&approve=<?= $r['id'] ?>" class="btn btn-sm btn-success" title="Setujui" onclick="return confirm('Setujui UMKM ini?')">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="?page=umkm&reject=<?= $r['id'] ?>" class="btn btn-sm btn-danger" title="Tolak" onclick="return confirm('Tolak UMKM ini?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditUMKM<?= $r['id'] ?>" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?page=umkm&hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Yakin hapus UMKM ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <!-- Modal Edit UMKM -->
                    <div class="modal fade" id="modalEditUMKM<?= $r['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Edit UMKM: <?= htmlspecialchars($r['nama_usaha']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Usaha</label>
                                                <input type="text" name="nama_usaha" class="form-control" value="<?= htmlspecialchars($r['nama_usaha']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Pemilik</label>
                                                <input type="text" name="nama_pemilik" class="form-control" value="<?= htmlspecialchars($r['nama_pemilik']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Kategori</label>
                                                <select name="kategori" class="form-select" required>
                                                    <?php
                                                    mysqli_data_seek($kategoris, 0);
                                                    while($k = mysqli_fetch_assoc($kategoris)):
                                                        $sel = $k['id'] == $r['id_kategori'] ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $k['id'] ?>" <?= $sel ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Kontak (WhatsApp)</label>
                                                <input type="text" name="kontak" class="form-control" value="<?= htmlspecialchars($r['kontak']) ?>" required>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Alamat</label>
                                                <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($r['alamat']) ?></textarea>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Deskripsi</label>
                                                <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($r['deskripsi']) ?></textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="pending" <?= $r['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="approved" <?= $r['status'] == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                                                    <option value="rejected" <?= $r['status'] == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Foto (kosongkan jika tidak diganti)</label>
                                                <input type="file" name="foto" class="form-control" accept="image/*">
                                                <?php if ($r['foto']): ?>
                                                    <img src="../uploads/<?= $r['foto'] ?>" class="img-thumbnail mt-2" style="max-height: 100px;">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_umkm" class="btn btn-primary-custom">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah UMKM -->
    <div class="modal fade" id="modalTambahUMKM" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah UMKM Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="nama_usaha" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Pemilik <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemilik" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php
                                    mysqli_data_seek($kategoris, 0);
                                    while($k = mysqli_fetch_assoc($kategoris)):
                                    ?>
                                        <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kontak (WhatsApp) <span class="text-danger">*</span></label>
                                <input type="text" name="kontak" class="form-control" placeholder="08xxxxxxxxxx" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                <textarea name="alamat" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="approved">Langsung Disetujui</option>
                                    <option value="pending">Pending (Butuh Persetujuan)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto Usaha</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_umkm" class="btn btn-primary-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: KATEGORI -->
    <!-- ============================================ -->
    <?php if ($page == 'kategori'): ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Kelola Kategori Usaha</h5>
            <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                <i class="fas fa-plus me-1"></i> Tambah Kategori
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah UMKM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = mysqli_query($conn, "SELECT k.*, COUNT(d.id) as jumlah FROM kategori_usaha k 
                        LEFT JOIN data_umkm d ON k.id = d.id_kategori GROUP BY k.id ORDER BY k.nama_kategori");
                    while($r = mysqli_fetch_assoc($q)):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($r['nama_kategori']) ?></strong></td>
                        <td><span class="badge bg-primary"><?= $r['jumlah'] ?> UMKM</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditKategori<?= $r['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?page=kategori&hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kategori ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <div class="modal fade" id="modalEditKategori<?= $r['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Edit Kategori</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <label class="form-label">Nama Kategori</label>
                                        <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($r['nama_kategori']) ?>" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_kategori" class="btn btn-primary-custom">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalTambahKategori" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Kategori Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_kategori" class="btn btn-primary-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: PRODUK -->
    <!-- ============================================ -->
    <?php if ($page == 'produk'): ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Katalog Produk UMKM</h5>
            <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
                <i class="fas fa-plus me-1"></i> Tambah Produk
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama Produk</th>
                        <th>UMKM</th>
                        <th>Harga</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = mysqli_query($conn, "SELECT p.*, d.nama_usaha FROM produk p 
                        LEFT JOIN data_umkm d ON p.id_umkm = d.id ORDER BY p.id DESC");
                    while($r = mysqli_fetch_assoc($q)):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <img src="../uploads/<?= $r['foto'] ?>" class="img-thumbnail-sm" 
                                 onerror="this.src='https://via.placeholder.com/60?text=No+Img'">
                        </td>
                        <td><strong><?= htmlspecialchars($r['nama_produk']) ?></strong></td>
                        <td><small><?= htmlspecialchars($r['nama_usaha'] ?? '-') ?></small></td>
                        <td>Rp <?= number_format($r['harga'], 0, ',', '.') ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($r['kategori_produk']) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditProduk<?= $r['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?page=produk&hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <div class="modal fade" id="modalEditProduk<?= $r['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Edit Produk</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nama Produk</label>
                                                <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($r['nama_produk']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">UMKM Pemilik</label>
                                                <select name="id_umkm" class="form-select" required>
                                                    <?php
                                                    mysqli_data_seek($umkms, 0);
                                                    while($u = mysqli_fetch_assoc($umkms)):
                                                        $sel = $u['id'] == $r['id_umkm'] ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $u['id'] ?>" <?= $sel ?>><?= htmlspecialchars($u['nama_usaha']) ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Harga (Rp)</label>
                                                <input type="number" name="harga" class="form-control" value="<?= $r['harga'] ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Kategori Produk</label>
                                                <input type="text" name="kategori_produk" class="form-control" value="<?= htmlspecialchars($r['kategori_produk']) ?>">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Deskripsi</label>
                                                <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($r['deskripsi']) ?></textarea>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Foto (kosongkan jika tidak diganti)</label>
                                                <input type="file" name="foto" class="form-control" accept="image/*">
                                                <?php if ($r['foto']): ?>
                                                    <img src="../uploads/<?= $r['foto'] ?>" class="img-thumbnail mt-2" style="max-height: 100px;">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_produk" class="btn btn-primary-custom">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalTambahProduk" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Produk Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="nama_produk" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">UMKM Pemilik <span class="text-danger">*</span></label>
                                <select name="id_umkm" class="form-select" required>
                                    <option value="">-- Pilih UMKM --</option>
                                    <?php
                                    mysqli_data_seek($umkms, 0);
                                    while($u = mysqli_fetch_assoc($umkms)):
                                    ?>
                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama_usaha']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="harga" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Produk</label>
                                <input type="text" name="kategori_produk" class="form-control" placeholder="Contoh: Makanan, Kerajinan">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Foto Produk</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_produk" class="btn btn-primary-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: GALERI -->
    <!-- ============================================ -->
    <?php if ($page == 'galeri'): ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-images me-2"></i>Galeri UMKM & Kegiatan Desa</h5>
            <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahGaleri">
                <i class="fas fa-plus me-1"></i> Tambah Foto
            </button>
        </div>
        <div class="row">
            <?php
            $q = mysqli_query($conn, "SELECT * FROM galeri ORDER BY id DESC");
            if (mysqli_num_rows($q) > 0):
                while($r = mysqli_fetch_assoc($q)):
            ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <img src="../uploads/<?= $r['foto'] ?>" class="card-img-top" style="height: 180px; object-fit: cover;" 
                         onerror="this.src='https://via.placeholder.com/300x180?text=No+Img'">
                    <div class="card-body p-2">
                        <h6 class="mb-1"><?= htmlspecialchars($r['judul']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars(substr($r['deskripsi'], 0, 50)) ?></small>
                        <div class="mt-2">
                            <a href="?page=galeri&hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus foto ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="col-12 text-center py-4">
                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada foto di galeri</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="modalTambahGaleri" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Foto Galeri</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto <span class="text-danger">*</span></label>
                            <input type="file" name="foto" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_galeri" class="btn btn-primary-custom">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: PENGGUNA -->
    <!-- ============================================ -->
    <?php if ($page == 'users'): ?>
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Kelola Pengguna Sistem</h5>
            <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="fas fa-plus me-1"></i> Tambah Pengguna
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                    while($r = mysqli_fetch_assoc($q)):
                        $role_badge = $r['role'] == 'super_admin' ? 'bg-danger' : ($r['role'] == 'admin' ? 'bg-primary' : 'bg-success');
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <span class="user-avatar"><?= strtoupper(substr($r['username'], 0, 1)) ?></span>
                            <strong><?= htmlspecialchars($r['username']) ?></strong>
                            <?php if ($r['id'] == $user_id): ?>
                                <span class="badge bg-info ms-1">Anda</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $role_badge ?>"><?= ucfirst(str_replace('_', ' ', $r['role'])) ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= $r['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($r['id'] != $user_id): ?>
                                <a href="?page=users&hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pengguna ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <div class="modal fade" id="modalEditUser<?= $r['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Edit Pengguna</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($r['username']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password (kosongkan jika tidak diganti)</label>
                                            <input type="password" name="password" class="form-control">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select name="role" class="form-select">
                                                <option value="super_admin" <?= $r['role'] == 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                                <option value="admin" <?= $r['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                <option value="umkm" <?= $r['role'] == 'umkm' ? 'selected' : '' ?>>UMKM</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit_user" class="btn btn-primary-custom">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalTambahUser" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Pengguna Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                                <option value="umkm">UMKM</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_user" class="btn btn-primary-custom">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: PENGATURAN -->
    <!-- ============================================ -->
    <?php if ($page == 'pengaturan'): ?>
    <?php
    $pengaturan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pengaturan LIMIT 1"));
    ?>
    <div class="content-card">
        <h5><i class="fas fa-cog me-2"></i>Pengaturan Website</h5>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Desa</label>
                    <input type="text" name="nama_desa" class="form-control" 
                           value="<?= htmlspecialchars($pengaturan['nama_desa'] ?? 'Desa Kubang') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kontak Desa</label>
                    <input type="text" name="kontak_desa" class="form-control" 
                           value="<?= htmlspecialchars($pengaturan['kontak_desa'] ?? '') ?>">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Alamat Desa</label>
                    <textarea name="alamat_desa" class="form-control" rows="2"><?= htmlspecialchars($pengaturan['alamat_desa'] ?? '') ?></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Teks Sambutan Beranda</label>
                    <textarea name="sambutan" class="form-control" rows="4"><?= htmlspecialchars($pengaturan['sambutan'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Logo Desa (kosongkan jika tidak diganti)</label>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <?php if (!empty($pengaturan['logo'])): ?>
                        <img src="../uploads/<?= $pengaturan['logo'] ?>" class="img-thumbnail mt-2" style="max-height: 80px;">
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Banner Beranda (kosongkan jika tidak diganti)</label>
                    <input type="file" name="banner" class="form-control" accept="image/*">
                    <?php if (!empty($pengaturan['banner'])): ?>
                        <img src="../uploads/<?= $pengaturan['banner'] ?>" class="img-thumbnail mt-2" style="max-height: 80px;">
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" name="simpan_pengaturan" class="btn btn-primary-custom">
                <i class="fas fa-save me-1"></i> Simpan Pengaturan
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HALAMAN: PESAN -->
    <!-- ============================================ -->
    <?php if ($page == 'pesan'): ?>
    <div class="content-card">
        <h5><i class="fas fa-envelope me-2"></i>Pesan Masuk</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Subjek</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $q = mysqli_query($conn, "SELECT * FROM pesan ORDER BY id DESC");
                    if (mysqli_num_rows($q) > 0):
                        while($r = mysqli_fetch_assoc($q)):
                            $status_badge = $r['status'] == 'dibaca' ? 'bg-success' : 'bg-warning';
                            $status_text = $r['status'] == 'dibaca' ? 'Dibaca' : 'Baru';
                    ?>
                    <tr class="<?= $r['status'] == 'belum_dibaca' ? 'table-warning' : '' ?>">
                        <td><?= $no++ ?></td>
                        <td><small><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small></td>
                        <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
                        <td><small><?= htmlspecialchars($r['email']) ?></small></td>
                        <td><?= htmlspecialchars($r['subjek']) ?></td>
                        <td><small><?= htmlspecialchars(substr($r['pesan'], 0, 50)) ?>...</small></td>
                        <td><span class="badge <?= $status_badge ?>"><?= $status_text ?></span></td>
                        <td>
                            <?php if ($r['status'] == 'belum_dibaca'): ?>
                                <a href="?page=pesan&baca=<?= $r['id'] ?>" class="btn btn-sm btn-info" title="Tandai Dibaca">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php endif; ?>
                            <a href="mailto:<?= $r['email'] ?>" class="btn btn-sm btn-primary" title="Balas Email">
                                <i class="fas fa-reply"></i>
                            </a>
                            <a href="?page=pesan&hapus=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pesan ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Belum ada pesan masuk</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide alert after 5 seconds
setTimeout(function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

</body>
</html>