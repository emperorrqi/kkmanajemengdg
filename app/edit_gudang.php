<?php
require 'koneksi.php';

/* ===============================
   AMBIL ID
================================ */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID gudang tidak valid");
}

/* ===============================
   SIMPAN PERUBAHAN
================================ */
if (isset($_POST['update'])) {

    $nama_gudang = trim($_POST['nama_gudang']);
    $plant       = trim($_POST['plant']);

    if ($nama_gudang === '' || $plant === '') {
        die("❌ Data tidak boleh kosong");
    }

    $stmt = $mysqli->prepare("
        UPDATE master_gudang
        SET nama_gudang=?, plant=?
        WHERE id_gudang=?
    ");
    $stmt->bind_param("ssi", $nama_gudang, $plant, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: gudang.php?edit=1");
    exit;
}

/* ===============================
   LOAD DATA GUDANG
================================ */
$stmt = $mysqli->prepare("
    SELECT kode_gudang, nama_gudang, plant
    FROM master_gudang
    WHERE id_gudang=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Data gudang tidak ditemukan");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Gudang</title>
<style>
body{
    font-family:"Segoe UI",Arial;
    background:#f5f6fa;
    padding:30px
}
.container{
    width:600px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.1)
}
h2{text-align:center;margin-bottom:20px}
label{
    font-weight:600;
    display:block;
    margin-bottom:6px
}
input{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
    margin-bottom:15px
}
button{
    background:#0d6efd;
    color:#fff;
    border:none;
    padding:10px 18px;
    border-radius:6px;
    cursor:pointer
}
button:hover{background:#0b5ed7}
.back{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#6c757d
}
</style>
</head>

<body>

<div class="container">
    <h2>✏ Edit Gudang</h2>

    <a href="gudang.php" class="back">⬅ Kembali</a>

    <form method="post">
        <label>Kode Gudang</label>
        <input type="text" value="<?= htmlspecialchars($data['kode_gudang']) ?>" disabled>

        <label>Nama Gudang</label>
        <input type="text" name="nama_gudang"
               value="<?= htmlspecialchars($data['nama_gudang']) ?>" required>

        <label>Plant</label>
        <input type="text" name="plant"
               value="<?= htmlspecialchars($data['plant']) ?>" required>

        <button name="update">💾 Update Gudang</button>
    </form>
</div>

</body>
</html>
