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
    die("ID administrasi tidak ditemukan");
}

$stmt = $mysqli->prepare("SELECT id_admin, kode_admin, nama_admin FROM master_administrasi WHERE id_admin = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    die("Data administrasi tidak ditemukan");
}

/* ===========================
   UPDATE DATA
=========================== */
if (isset($_POST['update'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $nama_admin = trim($_POST['nama_admin']);

    if ($nama_admin === '') {
        $_SESSION['error'] = "Nama administrasi wajib diisi";
    } else {
        $stmt = $mysqli->prepare("UPDATE master_administrasi SET nama_admin = ? WHERE id_admin = ?");
        $stmt->bind_param("si", $nama_admin, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Administrasi berhasil diupdate";
        header("Location: administrasi.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Administrasi</title>
<style>
body{font-family:Inter,Arial,sans-serif;background:#f3f4f6;padding:30px;}
.container{max-width:600px;margin:auto;}
.card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.08);}
form{display:grid;gap:14px;}
input, button{padding:12px;border-radius:8px;border:1px solid #d1d5db;}
button{background:#2563eb;color:#fff;font-weight:600;border:none;cursor:pointer;}
.alert-success{background:#dcfce7;padding:10px;border-radius:6px;}
.alert-error{background:#fee2e2;padding:10px;border-radius:6px;}
</style>
</head>
<body>

<div class="container">
<div class="card">

<a href="administrasi.php">⬅ Kembali</a>
<h2 style="text-align:center;">✏️ Edit Administrasi</h2>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

    <label>Kode Admin</label>
    <input type="text" value="<?= htmlspecialchars($admin['kode_admin']) ?>" disabled>

    <label>Nama Administrasi</label>
    <input type="text" name="nama_admin" value="<?= htmlspecialchars($admin['nama_admin']) ?>" required>

    <button name="update">💾 Update Administrasi</button>
</form>

</div>
</div>

</body>
</html>
