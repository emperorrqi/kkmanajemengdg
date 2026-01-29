<?php
include 'koneksi.php';
session_start();

/* ===========================
   CSRF TOKEN
=========================== */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ===========================
   AMBIL DATA BERDASARKAN ID
=========================== */
$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID barang tidak ditemukan");
}

$stmt = $mysqli->prepare("SELECT id_barang, kode_barang, nama_barang, keterangan FROM master_barang_elektronik WHERE id_barang = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$barang = $result->fetch_assoc();
$stmt->close();

if (!$barang) {
    die("Data barang elektronik tidak ditemukan");
}

/* ===========================
   UPDATE DATA
=========================== */
if (isset($_POST['update'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $nama_barang = trim($_POST['nama_barang']);
    $keterangan  = trim($_POST['keterangan']);

    if ($nama_barang === '') {
        $_SESSION['error'] = "Nama barang wajib diisi";
    } else {
        $stmt = $mysqli->prepare("UPDATE master_barang_elektronik SET nama_barang = ?, keterangan = ? WHERE id_barang = ?");
        $stmt->bind_param("ssi", $nama_barang, $keterangan, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Barang elektronik berhasil diupdate";
        header("Location: barang_elektronik.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Barang Elektronik</title>
<style>
body{font-family:Inter,Arial,sans-serif;background:#f3f4f6;padding:30px;}
.container{max-width:600px;margin:auto;}
.card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.08);}
form{display:grid;gap:14px;margin-bottom:30px;}
input, textarea, button{padding:12px;border-radius:8px;border:1px solid #d1d5db;}
textarea{resize:vertical;}
button{background:#2563eb;color:#fff;font-weight:600;border:none;cursor:pointer;}
.alert-success{background:#dcfce7;padding:10px;border-radius:6px;margin-bottom:15px;}
.alert-error{background:#fee2e2;padding:10px;border-radius:6px;margin-bottom:15px;}
</style>
</head>
<body>

<div class="container">
<div class="card">

<a href="barang_elektronik.php">⬅ Kembali</a>
<h2 style="text-align:center;">✏️ Edit Barang Elektronik</h2>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

    <label>Kode Barang</label>
    <input type="text" value="<?= htmlspecialchars($barang['kode_barang']) ?>" disabled>

    <label>Nama Barang Elektronik</label>
    <input type="text" name="nama_barang" value="<?= htmlspecialchars($barang['nama_barang']) ?>" required>

    <label>Keterangan</label>
    <textarea name="keterangan"><?= htmlspecialchars($barang['keterangan']) ?></textarea>

    <button name="update">💾 Update Barang</button>
</form>

</div>
</div>

</body>
</html>
