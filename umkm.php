<?php
require_once 'config/koneksi.php';

$page_title = 'Daftar UMKM';

$q = trim($_GET['q'] ?? '');
$kategori = (int)($_GET['kategori'] ?? 0);

$kategori_list = $conn->query("SELECT * FROM kategori_usaha ORDER BY nama_kategori");

$where = " WHERE u.status = 'aktif'";
$types = '';
$params = [];

if ($kategori > 0) {
    $where .= " AND u.id_kategori = ?";
    $params[] = $kategori;
    $types .= 'i';
}

if ($q !== '') {
    $where .= " AND (u.nama_umkm LIKE ? OR u.pemilik LIKE ? OR u.deskripsi LIKE ?)";
    $like = "%$q%";
    array_push($params, $like, $like, $like);
    $types .= 'sss';
}

$sql = "
    SELECT u.*, k.nama_kategori
    FROM umkm u
    LEFT JOIN kategori_usaha k ON u.id_kategori = k.id_kategori
    $where
    ORDER BY u.created_at DESC
";

$stmt = $conn->prepare($sql);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$daftar = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Daftar UMKM Desa Kubang</h2>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Cari nama UMKM, pemilik, atau deskripsi..."
                   value="<?= e($q) ?>">
        </div>

        <div class="col-md-4">
            <select name="kategori" class="form-select">
                <option value="">Semua Kategori</option>
                <?php while ($k = $kategori_list->fetch_assoc()): ?>
                    <option value="<?= (int)$k['id_kategori'] ?>" <?= $kategori == $k['id_kategori'] ? 'selected' : '' ?>>
                        <?= e($k['nama_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100">Cari</button>
        </div>
    </form>

    <div class="row g-4">
        <?php if ($daftar->num_rows === 0): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Data UMKM tidak ditemukan.
                </div>
            </div>
        <?php else: ?>
            <?php while ($u = $daftar->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card card-hover h-100">
                        <img src="<?= $u['foto'] && file_exists(__DIR__ . '/uploads/' . $u['foto'])
                            ? url('uploads/' . $u['foto'])
                            : 'https://placehold.co/600x400?text=UMKM+Desa+Kubang' ?>"
                            class="card-img-top img-cover"
                            alt="<?= e($u['nama_umkm']) ?>">

                        <div class="card-body">
                            <span class="badge-category mb-2 d-inline-block">
                                <?= e($u['nama_kategori'] ?? 'Umum') ?>
                            </span>

                            <h5 class="card-title"><?= e($u['nama_umkm']) ?></h5>
                            <p class="card-text text-muted mb-2">
                                Pemilik: <?= e($u['pemilik'] ?: '-') ?>
                            </p>
                            <p class="card-text">
                                <?= e(mb_substr(strip_tags($u['deskripsi'] ?? ''), 0, 100)) ?>...
                            </p>

                            <a href="<?= url('detail.php?id=' . (int)$u['id_umkm']) ?>" class="btn btn-sm btn-success">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>