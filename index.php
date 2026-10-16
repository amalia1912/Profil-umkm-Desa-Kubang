<?php
// 1. Aktifkan error reporting untuk debugging (cegah halaman putih kosong)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Include koneksi database
include 'koneksi.php';

// 3. Cek apakah koneksi berhasil
if (!$conn) {
    die("Koneksi database gagal. Pastikan file 'koneksi.php' sudah benar dan database sudah dibuat.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil UMKM Desa Kubang - KKM UMC 2026</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero { 
            background: linear-gradient(rgba(44, 62, 80, 0.85), rgba(44, 62, 80, 0.85)), url('https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=1600&q=80'); 
            background-size: cover; 
            background-position: center;
            color: white; 
            padding: 80px 0; 
            text-align: center; 
        }
        .card-img-top { 
            height: 200px; 
            object-fit: cover; 
            background-color: #f8f9fa;
        }
        .category-badge {
            cursor: default;
            font-size: 0.95rem;
            padding: 8px 16px;
        }
        footer {
            background-color: #2c3e50;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fas fa-store me-2"></i>UMKM Desa Kubang</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#daftar">Direktori UMKM</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php"><i class="fas fa-user-shield me-1"></i>Login Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section (Disesuaikan dengan Proposal KKM) -->
    <div class="hero">
        <div class="container">
            <h1 class="fw-bold">Selamat Datang di Portal UMKM Desa Kubang</h1>
            <p class="lead mt-3">Mendukung Digitalisasi Bisnis Lokal & Ekonomi Sirkular.<br>
            Temukan produk unggulan seperti <strong>Pastel</strong>, <strong>Home Industry</strong>, dan potensi lokal lainnya.</p>
            <a href="#daftar" class="btn btn-warning btn-lg mt-3 fw-bold">Lihat Produk UMKM</a>
        </div>
    </div>

    <!-- Daftar UMKM -->
    <div class="container my-5" id="daftar">
        <h2 class="text-center mb-4 fw-bold text-dark">Direktori UMKM Desa Kubang</h2>
        <p class="text-center text-muted mb-5">Kecamatan Talun, Kabupaten Cirebon</p>
        
        <!-- Filter Kategori: HANYA HOME INDUSTRY -->
        <div class="row mb-4">
            <div class="col-md-12 text-center">
                <span class="badge bg-secondary category-badge m-1">
                    <i class="fas fa-industry me-2"></i>Home Industry
                </span>
            </div>
        </div>

        <div class="row">
            <?php
            // Ambil data UMKM HANYA yang berkategori "Home Industry"
            $query = "SELECT data_umkm.*, kategori_usaha.nama_kategori 
                      FROM data_umkm 
                      JOIN kategori_usaha ON data_umkm.id_kategori = kategori_usaha.id 
                      WHERE kategori_usaha.nama_kategori = 'Home Industry'
                      ORDER BY data_umkm.id DESC";
            
            $result = mysqli_query($conn, $query);

            if($result && mysqli_num_rows($result) > 0){
                while($row = mysqli_fetch_assoc($result)){
                    // Keamanan: Gunakan htmlspecialchars untuk mencegah XSS
                    $nama_usaha = htmlspecialchars($row['nama_usaha']);
                    $kategori = htmlspecialchars($row['nama_kategori']);
                    $deskripsi = htmlspecialchars(substr($row['deskripsi'], 0, 100));
                    $pemilik = htmlspecialchars($row['nama_pemilik']);
                    
                    // Cek foto, jika kosong pakai placeholder
                    $foto = !empty($row['foto']) ? 'uploads/'.$row['foto'] : 'https://via.placeholder.com/400x250?text=Foto+UMKM';
                    
                    // Perbaikan Format Nomor WhatsApp (Ubah 08xx jadi 628xx)
                    $kontak = $row['kontak'];
                    $kontak_wa = preg_replace('/^08/', '628', $kontak);
                    $kontak_wa = preg_replace('/^\+62/', '62', $kontak_wa);
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="<?php echo $foto; ?>" class="card-img-top" alt="<?php echo $nama_usaha; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold"><?php echo $nama_usaha; ?></h5>
                        <span class="badge bg-primary mb-2 align-self-start"><?php echo $kategori; ?></span>
                        <p class="card-text small text-muted flex-grow-1"><?php echo $deskripsi; ?>...</p>
                        <p class="card-text"><small class="text-muted"><i class="fas fa-user me-1"></i> Pemilik: <?php echo $pemilik; ?></small></p>
                        
                        <a href="https://wa.me/<?php echo $kontak_wa; ?>" target="_blank" class="btn btn-success w-100 mt-auto">
                            <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center py-5'>
                        <i class='fas fa-box-open fa-3x text-muted mb-3'></i>
                        <p class='text-muted'>Belum ada data UMKM Home Industry yang terdaftar.</p>
                        <a href='admin/login.php' class='btn btn-primary btn-sm'>Login Admin untuk menambah data</a>
                      </div>";
            }
            ?>
        </div>
    </div>

    <!-- Footer (Disesuaikan dengan Identitas Proposal) -->
    <footer class="text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-1">&copy; 2026 <strong>Universitas Muhammadiyah Cirebon</strong></p>
            <p class="mb-0 small text-white-50">Program Kuliah Kerja Mahasiswa (KKM) - Dela Arnis Amalia (230511138) - Teknik Informatika</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>