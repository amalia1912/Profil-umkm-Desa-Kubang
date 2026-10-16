<?php
session_start();
if(!isset($_SESSION['admin_logged_in'])) header("Location: login.php");
include '../koneksi.php';

$id = $_GET['id'];

// Hapus file foto jika ada
$query_foto = mysqli_query($conn, "SELECT foto FROM data_umkm WHERE id=$id");
$data = mysqli_fetch_assoc($query_foto);
if($data['foto'] != "" && file_exists("../uploads/".$data['foto'])){
    unlink("../uploads/".$data['foto']);
}

mysqli_query($conn, "DELETE FROM data_umkm WHERE id=$id");
header("Location: index.php");
?>