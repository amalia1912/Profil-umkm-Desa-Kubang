<?php
session_start();
include '../koneksi.php';

if(isset($_POST['register'])){
    // Data User
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $konfirmasi_password = mysqli_real_escape_string($conn, $_POST['konfirmasi_password']);
    
    // Data UMKM
    $nama_usaha = mysqli_real_escape_string($conn, $_POST['nama_usaha']);
    $nama_pemilik = mysqli_real_escape_string($conn, $_POST['nama_pemilik']);
    $kategori = $_POST['kategori'];
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    
    // Validasi password
    if($password !== $konfirmasi_password){
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif(strlen($password) < 6){
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek username sudah ada atau belum
        $cek_username = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if(mysqli_num_rows($cek_username) > 0){
            $error = "Username sudah digunakan!";
        } else {
            // Upload Foto
            $foto_name = "";
            if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['foto']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if(in_array($ext, $allowed)){
                    $foto_name = uniqid() . '_' . time() . '.' . $ext;
                    $upload_path = "../uploads/" . $foto_name;
                    
                    if(!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)){
                        $error = "Gagal upload foto!";
                    }
                } else {
                    $error = "Format foto harus JPG, JPEG, atau PNG!";
                }
            }
            
            if(!isset($error)){
                // Insert ke tabel users
                $query_user = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'umkm')";
                
                if(mysqli_query($conn, $query_user)){
                    $user_id = mysqli_insert_id($conn);
                    
                    // Insert ke tabel data_umkm
                    $query_umkm = "INSERT INTO data_umkm 
                        (user_id, nama_usaha, nama_pemilik, id_kategori, alamat, deskripsi, kontak, foto, status) 
                        VALUES 
                        ('$user_id', '$nama_usaha', '$nama_pemilik', '$kategori', '$alamat', '$deskripsi', '$kontak', '$foto_name', 'pending')";
                    
                    if(mysqli_query($conn, $query_umkm)){
                        $success = "Registrasi berhasil! Akun Anda sedang menunggu persetujuan admin.";
                        // Clear POST data
                        unset($_POST);
                    } else {
                        // Rollback user jika insert umkm gagal
                        mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'");
                        $error = "Gagal menyimpan data UMKM!";
                    }
                } else {
                    $error = "Gagal membuat akun!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi UMKM - Desa Kubang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 700px;
            margin: 0 auto;
        }
        .register-header {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-header h3 {
            margin: 0;
            font-weight: 600;
        }
        .register-header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .register-body {
            padding: 40px;
        }
        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }
        .form-control:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }
        .btn-register {
            background: #2c3e50;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-register:hover {
            background: #1a252f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #2c3e50;
            font-weight: 600;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="register-header">
                <h3><i class="fas fa-user-plus me-2"></i>Registrasi UMKM</h3>
                <p>Daftarkan usaha Anda di Portal UMKM Desa Kubang</p>
            </div>
            <div class="register-body">
                <?php 
                if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; 
                if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; 
                ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <!-- Bagian 1: Akun Login -->
                    <h5 class="section-title"><i class="fas fa-user-shield me-2"></i>Informasi Akun</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>" 
                                   placeholder="Buat username" required>
                            <small class="text-muted">Untuk login ke dashboard</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Minimal 6 karakter" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="konfirmasi_password" class="form-control" 
                                   placeholder="Ulangi password" required>
                        </div>
                    </div>

                    <!-- Bagian 2: Data UMKM -->
                    <h5 class="section-title mt-4"><i class="fas fa-store me-2"></i>Data Usaha</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Usaha <span class="text-danger">*</span></label>
                            <input type="text" name="nama_usaha" class="form-control" 
                                   value="<?php echo isset($_POST['nama_usaha']) ? $_POST['nama_usaha'] : ''; ?>" 
                                   placeholder="Contoh: UMKM Opak Becak" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pemilik <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pemilik" class="form-control" 
                                   value="<?php echo isset($_POST['nama_pemilik']) ? $_POST['nama_pemilik'] : ''; ?>" 
                                   placeholder="Nama lengkap pemilik" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori Usaha <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                <?php
                                $kat = mysqli_query($conn, "SELECT * FROM kategori_usaha ORDER BY nama_kategori ASC");
                                while($k = mysqli_fetch_assoc($kat)){
                                    $selected = (isset($_POST['kategori']) && $_POST['kategori'] == $k['id']) ? 'selected' : '';
                                    echo "<option value='".$k['id']."' $selected>".$k['nama_kategori']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="kontak" class="form-control" 
                                   value="<?php echo isset($_POST['kontak']) ? $_POST['kontak'] : ''; ?>" 
                                   placeholder="Contoh: 081234567890" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Alamat Usaha <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" 
                                      placeholder="Alamat lengkap usaha" required><?php echo isset($_POST['alamat']) ? $_POST['alamat'] : ''; ?></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi Usaha</label>
                            <textarea name="deskripsi" class="form-control" rows="3" 
                                      placeholder="Ceritakan tentang usaha Anda, produk yang dijual, dll"><?php echo isset($_POST['deskripsi']) ? $_POST['deskripsi'] : ''; ?></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Foto Usaha/Produk</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="register" class="btn btn-primary btn-register">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="login-link">
                    <p class="mb-2">Sudah punya akun?</p>
                    <a href="login.php"><i class="fas fa-sign-in-alt me-2"></i>Login di sini</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>