<?php
include 'koneksi.php';
session_start();

/* ===============================
   SIMPAN DATA
================================ */
if (isset($_POST['simpan'])) {
    $location = trim($_POST['location']);

    if ($location == '') {
        $_SESSION['error'] = "Location tidak boleh kosong";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO master_sbu (location) VALUES (?)");
        $stmt->bind_param("s", $location);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "SBU berhasil ditambahkan";
        header("Location: sbu.php");
        exit;
    }
}

/* ===============================
   UPDATE DATA
================================ */
if (isset($_POST['update'])) {
    $id       = (int)$_POST['id_sbu'];
    $location = trim($_POST['location']);

    if ($location == '') {
        $_SESSION['error'] = "Location tidak boleh kosong";
    } else {
        $stmt = $mysqli->prepare("UPDATE master_sbu SET location=? WHERE id_sbu=?");
        $stmt->bind_param("si", $location, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "SBU berhasil diperbarui";
        header("Location: sbu.php");
        exit;
    }
}

/* ===============================
   HAPUS DATA
================================ */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $mysqli->prepare("DELETE FROM master_sbu WHERE id_sbu=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "SBU berhasil dihapus";
    header("Location: sbu.php");
    exit;
}

/* ===============================
   DATA SBU
================================ */
$data = $mysqli->query("SELECT * FROM master_sbu ORDER BY id_sbu DESC");

/* ===============================
   DATA EDIT
================================ */
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit = $mysqli->query("SELECT * FROM master_sbu WHERE id_sbu=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master SBU</title>
<style>
body{
    font-family:Inter,Arial;
    background:#f3f4f6;
    padding:30px
}
.container{
    max-width:900px;
    margin:auto
}
.card{
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 6px 20px rgba(0,0,0,.08)
}
h2{
    text-align:center;
    margin-bottom:20px
}
form{
    display:grid;
    grid-template-columns:1fr;
    gap:12px;
    margin-bottom:30px
}
input,button{
    padding:12px;
    border-radius:8px;
    border:1px solid #d1d5db
}
button{
    background:#2563eb;
    color:#fff;
    font-weight:600;
    border:none;
    cursor:pointer
}
table{
    width:100%;
    border-collapse:collapse
}
th,td{
    padding:14px;
    border-bottom:1px solid #e5e7eb
}
th{
    background:#f3f4f6;
    text-align:left
}
.alert-success{
    background:#dcfce7;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px
}
.alert-error{
    background:#fee2e2;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px
}
.action a{
    padding:6px 10px;
    border-radius:6px;
    color:#fff;
    text-decoration:none;
    font-size:13px
}
.edit{background:#f59e0b}
.hapus{background:#ef4444}
</style>
</head>
<body>

<div class="container">
<div class="card">

<a href="index.php">⬅ Kembali</a>
<h2>🏢 Master SBU</h2>

<?php if(!empty($_SESSION['success'])): ?>
<div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if(!empty($_SESSION['error'])): ?>
<div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- FORM -->
<form method="post">
    <?php if($edit): ?>
        <input type="hidden" name="id_sbu" value="<?= $edit['id_sbu'] ?>">
    <?php endif; ?>

    <label>Location SBU</label>
    <input type="text" name="location" value="<?= $edit['location'] ?? '' ?>" placeholder="Contoh: Jakarta / Surabaya / Plant A" required>

    <button name="<?= $edit ? 'update' : 'simpan' ?>">
        <?= $edit ? '✏️ Update SBU' : '💾 Tambah SBU' ?>
    </button>
</form>

<!-- TABEL -->
<table>
<tr>
    <th>Kode SBU</th>
    <th>Location</th>
    <th>Aksi</th>
</tr>
<?php while($r=$data->fetch_assoc()): ?>
<tr>
    <td><?= $r['kode_sbu'] ?></td>
    <td><?= htmlspecialchars($r['location']) ?></td>
    <td class="action">
        <a class="edit" href="?edit=<?= $r['id_sbu'] ?>">Edit</a>
        <a class="hapus" href="?hapus=<?= $r['id_sbu'] ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

</body>
</html>
