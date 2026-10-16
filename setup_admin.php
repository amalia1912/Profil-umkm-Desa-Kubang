<?php
require_once 'config/koneksi.php';

$admin_count = $conn->query("SELECT COUNT(*) AS j FROM users")->fetch_assoc()['j'];

if ($admin_count > 0) {
    die('Admin sudah terdaftar. Silakan login melalui <a href="' . url('login.php') . '">halaman login</a>.');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nama === '' || $username === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'superadmin';

        $stmt = $conn->prepare("
            INSERT INTO users (nama, username, password, role)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param('ssss', $nama, $username, $hashed, $role);

        if ($stmt->execute()) {
            $success = 'Akun admin berhasil dibuat. Silakan login.';
        } else {
            $error = 'Gagal menyimpan akun. Username mungkin sudah digunakan.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Admin - UMKM Desa Kubang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="text-center text-success mb-4">Setup Admin Pertama</h3>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?= e($success) ?>
                            <br>
                            <a href="<?= url('login.php') ?>">Klik di sini untuk login</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Simpan Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>