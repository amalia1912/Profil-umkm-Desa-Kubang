<?php
require_once '../config/koneksi.php';

$page_title = 'Kelola UMKM';
$error = '';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $stmt = $conn->prepare("SELECT foto FROM umkm WHERE id_umkm = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();

    $del = $conn->prepare("DELETE FROM umkm WHERE id_umkm = ?");
    $del->bind_param('i', $id);
    $del->execute();

    if ($old && $old['foto'] && file_exists(__DIR__ . '/../uploads/' . $old['foto'])) {
        unlink(__DIR__ . '/../uploads/' . $old['foto']);
    }

    redirect('admin/umkm.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_umkm'] ?? 0);
    $nama = trim($_POST['nama_umkm'] ?? '');
    $id_kategori = !empty($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : null;
    $pemilik = trim($_POST['pemilik'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $kontak = trim($_POST['kontak'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = $_POST['status'] ?? 'aktif';
    $old_foto = trim($_POST['old_foto'] ?? '');

    if ($nama === '') {
        $error = 'Nama UMKM wajib diisi.';
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
                UPDATE umkm
                SET nama_umkm = ?, id_kategori = ?, pemilik = ?, alamat = ?,
                    kontak = ?, deskripsi = ?, foto = ?, status = ?
                WHERE id_umkm = ?
            ");

            $stmt->bind_param(
                'sissssssi',
                $nama,
                $id_kategori,
                $pemilik,
                $alamat,
                $kontak,
                $deskripsi,
                $foto,
                $status,
                $id
            );
        } else {
            if (!$foto) {
                $foto = null;
            }

            $stmt = $conn->prepare("
                INSERT INTO umkm (nama_umkm, id_kategori, pemilik, alamat, kontak, deskripsi, foto, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                'sissssss',
                $nama,
                $id_kategori,
                $pemilik,
                $alamat,
                $kontak,
                $deskripsi,
                $foto,
                $status
            );
        }

        $stmt->execute();

        redirect('admin/umkm.php');
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM umkm WHERE id_umkm = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $edit = $stmt->get_result()->fetch_assoc();
}

$kategori_list = $conn->query("SELECT * FROM kategori_usaha ORDER BY nama_kategori");

$daftar = $conn->query("
    SELECT u.*, k.nama_kategori
    FROM umkm u
    LEFT JOIN kategori_usaha k ON u.id_kategori = k.id_kategori
    ORDER BY u.created_at DESC
");

include 'includes/header.php';
?>

<h2 class="mb-4">Kelola UMKM</h2>

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
                    <?= $edit ? 'Edit UMKM' : 'Tambah UMKM' ?>
                </h5>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_umkm" value="<?= e($edit['id_umkm'] ?? 0) ?>">
                    <input type="hidden" name="old_foto" value="<?= e($edit['foto'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama UMKM</label>
                        <input type="text" name="nama_umkm" class="form-control"
                               value="<?= e($edit['nama_umkm'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori Usaha</label>
                        <select name="id_kategori" class="form-select">
                            <option value="">Pilih Kategori</option>
                            <?php while ($k = $kategori_list->fetch_assoc()): ?>
                                <option value="<?= (int)$k['id_kategori'] ?>"
                                    <?= (isset($edit['id_kategori']) && $edit['id_kategori'] == $k['id_kategori']) ? 'selected' : '' ?>>
                                    <?= e($k['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Pemilik</label>
                        <input type="text" name="pemilik" class="form-control"
                               value="<?= e($edit['pemilik'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= e($edit['alamat'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kontak / WhatsApp</label>
                        <input type="text" name="kontak" class="form-control"
                               placeholder="08xxxxxxxxxx"
                               value="<?= e($edit['kontak'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= e($edit['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto UMKM</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="aktif" <?= (isset($edit['status']) && $edit['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= (isset($edit['status']) && $edit['status'] == 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Daftar UMKM</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-success">
                            <tr>
                                <th>No</th>
                                <th>UMKM</th>
                                <th>Kategori</th>
                                <th>Kontak</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($daftar->num_rows === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data UMKM.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; while ($u = $daftar->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong><?= e($u['nama_umkm']) ?></strong><br>
                                            <small class="text-muted"><?= e($u['pemilik']) ?></small>
                                        </td>
                                        <td><?= e($u['nama_kategori'] ?? '-') ?></td>
                                        <td><?= e($u['kontak'] ?: '-') ?></td>
                                        <td>
                                            <?php if ($u['status'] === 'aktif'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= url('admin/umkm.php?edit=' . (int)$u['id_umkm']) ?>"
                                               class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <a href="<?= url('admin/umkm.php?hapus=' . (int)$u['id_umkm']) ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin ingin menghapus UMKM ini?')">
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