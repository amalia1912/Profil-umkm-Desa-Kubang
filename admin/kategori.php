<?php
require_once '../config/koneksi.php';

$page_title = 'Kategori Usaha';
$error = '';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("DELETE FROM kategori_usaha WHERE id_kategori = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    redirect('admin/kategori.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_kategori'] ?? 0);
    $nama = trim($_POST['nama_kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama === '') {
        $error = 'Nama kategori wajib diisi.';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("
                UPDATE kategori_usaha
                SET nama_kategori = ?, deskripsi = ?
                WHERE id_kategori = ?
            ");

            $stmt->bind_param('ssi', $nama, $deskripsi, $id);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO kategori_usaha (nama_kategori, deskripsi)
                VALUES (?, ?)
            ");

            $stmt->bind_param('ss', $nama, $deskripsi);
        }

        $stmt->execute();

        redirect('admin/kategori.php');
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM kategori_usaha WHERE id_kategori = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $edit = $stmt->get_result()->fetch_assoc();
}

$daftar = $conn->query("SELECT * FROM kategori_usaha ORDER BY nama_kategori");

include 'includes/header.php';
?>

<h2 class="mb-4">Kelola Kategori Usaha</h2>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <?= e($error) ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    <?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?>
                </h5>

                <form method="POST">
                    <input type="hidden" name="id_kategori" value="<?= e($edit['id_kategori'] ?? 0) ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control"
                               value="<?= e($edit['nama_kategori'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= e($edit['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar Kategori</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-success">
                            <tr>
                                <th>No</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($daftar->num_rows === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada kategori.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; while ($k = $daftar->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= e($k['nama_kategori']) ?></td>
                                        <td><?= e(mb_substr($k['deskripsi'] ?? '', 0, 70)) ?></td>
                                        <td>
                                            <a href="<?= url('admin/kategori.php?edit=' . (int)$k['id_kategori']) ?>"
                                               class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <a href="<?= url('admin/kategori.php?hapus=' . (int)$k['id_kategori']) ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin ingin menghapus kategori ini?')">
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