<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) header("Location: login.php");
include '../koneksi.php';

if(isset($_POST['simpan'])){
    $nama_usaha = $_POST['nama_usaha'];
    $pemilik = $_POST['pemilik'];
    $kategori = $_POST['kategori'];
    $alamat = $_POST['alamat'];
    $deskripsi = $_POST['deskripsi'];
    $kontak = $_POST['kontak'];

    // Upload Foto
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $folder = "../uploads/";
    
    if(!empty($foto_name)){
        move_uploaded_file($foto_tmp, $folder.$foto_name);
    } else {
        $foto_name = "";
    }

    $sql = "INSERT INTO data_umkm (nama_usaha, nama_pemilik, id_kategori, alamat, deskripsi, kontak, foto) 
            VALUES ('$nama_usaha', '$pemilik', '$kategori', '$alamat', '$deskripsi', '$kontak', '$foto_name')";
    
    if(mysqli_query($conn, $sql)){
        header("Location: index.php");
    } else {
        echo "Gagal menyimpan data.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah UMKM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Tambah Data UMKM</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Nama Usaha</label>
            <input type="text" name="nama_usaha" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Nama Pemilik</label>
            <input type="text" name="pemilik" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Kategori</label>
            <select name="kategori" class="form-control">
                <?php
                $kat = mysqli_query($conn, "SELECT * FROM kategori_usaha");
                while($k = mysqli_fetch_assoc($kat)){
                    echo "<option value='".$k['id']."'>".$k['nama_kategori']."</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Kontak (WhatsApp)</label>
            <input type="text" name="kontak" class="form-control" placeholder="628xxx">
        </div>
        <div class="mb-3">
            <label>Foto Usaha</label>
            <input type="file" name="foto" class="form-control">
        </div>
        <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>