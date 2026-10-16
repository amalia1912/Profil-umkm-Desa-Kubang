<?php
require_once '../config/koneksi.php';

$page_title = 'Kelola Produk';
$error = '';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT foto FROM produk WHERE id_produk = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();

    $del = $conn->prepare("DELETE FROM produk WHERE id_produk = ?");
    $del->bind_param('i', $id);
    $del->execute();

    if ($old && $old['foto'] && file_exists(__DIR__ . '/../uploads/' . $old['foto'])) {
        unlink(__DIR__ . '/../uploads/' . $old['foto']);
    }

    redirect('admin/produk.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_produk'] ?? 0);
    $id_umkm = (int)($_POST['id_umkm'] ?? 0);
    $nama = trim($_POST['nama_produk'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $old_foto = trim($_POST['old_foto'] ?? '');

    if ($nama === '' || $id_umkm === 0) {
        $error = 'Nama produk dan UMKM wajib dipilih.';
    } else {
        $foto = upload_image('foto', __DIR__ . '/../uploads/');

        if ($id > 0) {
            if ($foto) {
                if ($old_foto && file_exists(__DIR__ . '/../uploads/' . $old_foto)) {
                    unlink(__DIR__ . '/../uploads/' . $old_foto);
                }
            } else {
                $foto = $old_foto ?: null;
            }

            $stmt = $conn->prepare("
                UPDATE produk
                SET id_umkm = ?, nama_produk = ?, harga = ?, deskripsi = ?, foto = ?
                WHERE id_produk = ?
            ");

            $stmt->bind_param('isdssi', $id_umkm, $nama, $harga, $deskripsi, $foto, $id);
        } else {
            if (!$foto) {
                $foto = null;
            }

            $stmt = $conn->prepare("
                INSERT INTO produk (id_umkm, nama_produk, harga, deskripsi, foto)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->bind_param('isdss', $id_umkm, $nama, $harga, $deskripsi, $foto);
        }

        $stmt->execute();

        redirect('admin/produk.php');
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM produk WHERE id_produk = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $edit = $stmt->get_result()->fetch_assoc();
}

$umkm_list = $conn->query("SELECT id_umkm, nama_umkm FROM umkm ORDER BY nama_umkm");

$daftar = $conn->query("
    SELECT p.*, u.nama_umkm
    FROM produk p
    JOIN umkm u ON p.id_umkm = u.id_umkm
    ORDER BY p.created_at DESC
");

include 'includes/header.php';
?>

<h2 class="mb-4">Kelola Produk UMKM</h2>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    <?= $edit ? 'Edit Produk' : 'Tambah Produk' ?>
                </h5>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_produk" value="<?= e($edit['id_produk'] ?? 0) ?>">
                    <input type="hidden" name="old_foto" value="<?= e($edit['foto'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label">UMKM</label>
                        <select name="id_umkm" class="form-select" required>
                            <option value="">Pilih UMKM</option>
                            <?php while ($u = $umkm_list->fetch_assoc()): ?>
                                <option value="<?= (int)$u['id_umkm'] ?>"
                                    <?= (isset($edit['id_umkm']) && $edit['id_umkm'] == $u['id_umkm']) ? 'selected' : '' ?>>
                                    <?= e($u['nama_umkm']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control"
                               value="<?= e($edit['nama_produk'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" class="form-control" min="0" step="100"
                               value="<?= e($edit['harga'] ?? 0) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Produk</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= e($edit['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Produk</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar Produk</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-success">
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>UMKM</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($daftar->num_rows === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada produk.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; while ($p = $daftar->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= e($p['nama_produk']) ?></td>
                                        <td><?= e($p['nama_umkm']) ?></td>
                                        <td><?= format_rupiah($p['harga']) ?></td>
                                        <td>
                                            <a href="<?= url('admin/produk.php?edit=' . (int)$p['id_produk']) ?>"
                                               class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <a href="<?= url('admin/produk.php?hapus=' . (int)$p['id_produk']) ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>