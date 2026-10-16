<?php
$base_url = 'http://localhost/umkm-kubang';

$conn = @new mysqli('localhost', 'root', '', 'db_umkm_kubang');

if ($conn->connect_error) {
    die('Koneksi database gagal. Pastikan database db_umkm_kubang sudah dibuat.');
}

$conn->set_charset('utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function url($path = '')
{
    global $base_url;
    return rtrim($base_url, '/') . '/' . ltrim($path, '/');
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function format_rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function wa_link($kontak)
{
    $digits = preg_replace('/\D/', '', (string)$kontak);

    if (!$digits) {
        return '#';
    }

    if (substr($digits, 0, 1) === '0') {
        $digits = '62' . substr($digits, 1);
    }

    return 'https://wa.me/' . $digits;
}

function upload_image($field, $dir)
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return null;
    }

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $newName = 'img_' . date('YmdHis_') . uniqid() . '.' . $ext;

    if (move_uploaded_file($file['tmp_name'], rtrim($dir, '/') . '/' . $newName)) {
        return $newName;
    }

    return null;
}