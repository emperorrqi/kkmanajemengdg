<?php
include 'koneksi.php';

/* ===============================
   GENERATE KODE GUDANG OTOMATIS
================================ */
function generateKodeGudang($mysqli) {
    $q = $mysqli->query("SELECT MAX(kode_gudang) AS kode FROM master_gudang");
    $d = $q->fetch_assoc();

    if ($d['kode']) {
        $num = (int) substr($d['kode'], 3) + 1;
        return 'GDG' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
    return 'GDG001';
}

/* ===============================
   SIMPAN DATA
================================ */
if (isset($_POST['tambah'])) {
    $kode_gudang = generateKodeGudang($mysqli);
    $nama_gudang = $_POST['nama_gudang'];
    $plant       = $_POST['plant'];

    $stmt = $mysqli->prepare("
        INSERT INTO master_gudang (kode_gudang, nama_gudang, plant)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $kode_gudang, $nama_gudang, $plant);
    $stmt->execute();

    header("Location: gudang.php");
    exit;
}

/* ===============================
   LOAD DATA
================================ */
$data = $mysqli->query("SELECT * FROM master_gudang ORDER BY kode_gudang ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master Gudang</title>

<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f5f6fa;
    padding: 30px;
}
.container {
    width: 900px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}
input[type=text] {
    width: 98%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 12px;
}
button {
    background: #198754;
    border: none;
    color: #fff;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #157347;
}
.back-btn {
    display: inline-block;
    margin-bottom: 15px;
    background: #6c757d;
    color: #fff;
    padding: 8px 14px;
    border-radius: 6px;
    text-decoration: none;
}
.back-btn:hover {
    background: #5a6268;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th {
    background: #0d6efd;
    color: #fff;
    padding: 12px;
}
td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
}
tr:hover td {
    background: #f3f8ff;
}
.edit {
    color: #ffc107;
    font-weight: bold;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="container">
    <h2>🏬 Master Gudang</h2>

    <a href="index.php" class="back-btn">⬅ Kembali ke Menu</a>

    <form method="POST">
        <label>Kode Gudang (Otomatis)</label>
        <input type="text" value="<?= generateKodeGudang($mysqli) ?>" disabled>

        <label>Nama Gudang</label>
        <input type="text" name="nama_gudang" required>

        <label>Plant</label>
        <input type="text" name="plant" required>

        <button type="submit" name="tambah">+ Tambah Gudang</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Kode Gudang</th>
            <th>Nama Gudang</th>
            <th>Plant</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = $data->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id_gudang'] ?></td>
            <td><?= $row['kode_gudang'] ?></td>
            <td><?= $row['nama_gudang'] ?></td>
            <td><?= $row['plant'] ?></td>
            <td>
                <a class="edit" href="edit_gudang.php?id=<?= $row['id_gudang'] ?>">Edit</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
