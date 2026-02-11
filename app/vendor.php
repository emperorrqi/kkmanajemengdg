<?php
include 'koneksi.php';

/* ===============================
   GENERATE KODE VENDOR
================================ */
function generateKodeVendor($mysqli) {
    $q = $mysqli->query("SELECT MAX(kode_vendor) AS kode FROM master_vendor");
    $d = $q->fetch_assoc();

    if ($d['kode']) {
        $num = (int)substr($d['kode'], 3) + 1;
    } else {
        $num = 1;
    }

    return 'VND' . str_pad($num, 3, '0', STR_PAD_LEFT);
}

/* ===============================
   HAPUS DATA
================================ */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $mysqli->prepare("DELETE FROM master_vendor WHERE id_vendor=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: vendor.php");
    exit;
}

/* ===============================
   TAMBAH / UPDATE DATA
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = $_POST['nama_vendor'];
    $alamat = $_POST['alamat'];
    $telepon = $_POST['telepon'];

    if (!empty($_POST['id_vendor'])) {
        // UPDATE
        $id = $_POST['id_vendor'];
        $stmt = $mysqli->prepare("
            UPDATE master_vendor
            SET nama_vendor=?, alamat=?, telepon=?
            WHERE id_vendor=?
        ");
        $stmt->bind_param("sssi", $nama, $alamat, $telepon, $id);
    } else {
        // INSERT
        $kode = generateKodeVendor($mysqli);
        $stmt = $mysqli->prepare("
            INSERT INTO master_vendor (kode_vendor, nama_vendor, alamat, telepon)
            VALUES (?,?,?,?)
        ");
        $stmt->bind_param("ssss", $kode, $nama, $alamat, $telepon);
    }

    $stmt->execute();
    header("Location: vendor.php");
    exit;
}

/* ===============================
   EDIT MODE
================================ */
$edit_mode = false;
$edit = null;

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $q = $mysqli->prepare("SELECT * FROM master_vendor WHERE id_vendor=?");
    $q->bind_param("i", $id);
    $q->execute();
    $edit = $q->get_result()->fetch_assoc();
}

/* ===============================
   LOAD DATA
================================ */
$data = $mysqli->query("SELECT * FROM master_vendor ORDER BY id_vendor ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master Vendor</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f5f7fa; padding:20px; }
.container { max-width:900px; margin:auto; background:#fff; padding:30px; border-radius:12px; }
h2 { text-align:center; margin-bottom:25px; }
label { font-weight:600; }
input, textarea { width:100%; padding:10px; margin-bottom:15px; border-radius:6px; border:1px solid #ccc; }
button { background:#007bff; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:12px; border-bottom:1px solid #ddd; text-align:center; }
th { background:#f1f1f1; }
a { text-decoration:none; font-weight:600; }
.delete { color:#e74c3c; }
</style>
</head>

<body>
<div class="container">
<h2>🏢 Master Vendor</h2>

<form method="post">
<?php if ($edit_mode): ?>
    <input type="hidden" name="id_vendor" value="<?= $edit['id_vendor'] ?>">
<?php endif; ?>

<label>Nama Vendor</label>
<input type="text" name="nama_vendor" value="<?= $edit['nama_vendor'] ?? '' ?>" required>

<label>Alamat</label>
<textarea name="alamat"><?= $edit['alamat'] ?? '' ?></textarea>

<label>Telepon</label>
<input type="text" name="telepon" value="<?= $edit['telepon'] ?? '' ?>">

<button type="submit"><?= $edit_mode ? '💾 Update Vendor' : '+ Tambah Vendor ' ?></button>
<?php if ($edit_mode): ?>
<a href="vendor.php">Batal</a>
<?php endif; ?>
</form>

<table>
<tr>
    <th>ID</th>
    <th>Kode</th>
    <th>Nama Vendor</th>
    <th>Alamat</th>
    <th>Telepon</th>
    <th>Aksi</th>
</tr>

<?php while ($r = $data->fetch_assoc()): ?>
<tr>
    <td><?= $r['id_vendor'] ?></td>
    <td><?= $r['kode_vendor'] ?></td>
    <td><?= $r['nama_vendor'] ?></td>
    <td><?= $r['alamat'] ?></td>
    <td><?= $r['telepon'] ?></td>
    <td>
        <a href="?edit=<?= $r['id_vendor'] ?>">✏️ Edit</a> |
        <a class="delete" href="?delete=<?= $r['id_vendor'] ?>" onclick="return confirm('Hapus vendor?')">🗑️ Hapus</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

<br>
<a href="index.php">← Kembali ke Menu</a>
</div>
</body>
</html>
