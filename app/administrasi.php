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
   SIMPAN DATA
=========================== */
if (isset($_POST['tambah'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $nama_admin = trim($_POST['nama_admin']);

    if ($nama_admin === '') {
        $_SESSION['error'] = "Nama administrasi wajib diisi";
    } else {
        $stmt = $mysqli->prepare("
            INSERT INTO master_administrasi (nama_admin)
            VALUES (?)
        ");
        $stmt->bind_param("s", $nama_admin);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Administrasi berhasil ditambahkan";
        header("Location: administrasi.php");
        exit;
    }
}

/* ===========================
   AMBIL DATA
=========================== */
$data = $mysqli->query("
    SELECT id_admin, kode_admin, nama_admin
    FROM master_administrasi
    ORDER BY id_admin DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master Administrasi</title>
<style>
body{
    font-family: Inter, Arial, sans-serif;
    background:#f3f4f6;
    padding:30px;
}
.container{max-width:900px;margin:auto;}
.card{
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,.08);
}
form{
    display:grid;
    gap:14px;
}
input, button{
    padding:12px;
    border-radius:8px;
    border:1px solid #d1d5db;
}
button{
    background:#2563eb;
    color:#fff;
    font-weight:600;
    border:none;
    cursor:pointer;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:30px;
}
th,td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
}
th{background:#f3f4f6;}
.alert-success{background:#dcfce7;padding:10px;border-radius:6px;}
.alert-error{background:#fee2e2;padding:10px;border-radius:6px;}
.action a{
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
}
</style>
</head>
<body>

<div class="container">
<div class="card">

<a href="index.php">⬅ Kembali</a>
<h2 style="text-align:center;">👨‍💼 Master Administrasi</h2>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- FORM ENTRY -->
<form method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

    <label>Nama Administrasi</label>
    <input type="text" name="nama_admin" placeholder="Contoh: Admin Gudang" required>

    <button name="tambah">+ Tambah Administrasi</button>
</form>

<!-- DATA TABLE -->
<table>
<tr>
    <th>Kode Admin</th>
    <th>Nama Administrasi</th>
    <th>Aksi</th>
</tr>

<?php while ($r = $data->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($r['kode_admin']) ?></td>
    <td><?= htmlspecialchars($r['nama_admin']) ?></td>
    <td class="action">
        <a href="edit_administrasi.php?id=<?= $r['id_admin'] ?>">Edit</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

</body>
</html>
