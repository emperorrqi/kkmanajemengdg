<?php
include 'koneksi.php';
session_start();

/* ==========================
   CSRF TOKEN
========================== */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ==========================
   GENERATE KODE BARANG
========================== */
function generateKodeBarang($mysqli) {
    $q = $mysqli->query("SELECT MAX(kode_barang) AS max_kode FROM master_barang_elektronik");
    $d = $q->fetch_assoc()['max_kode'];

    if ($d) {
        $num = (int) substr($d, 2) + 1;
    } else {
        $num = 1;
    }

    return 'BE' . str_pad($num, 4, '0', STR_PAD_LEFT);
}

/* ==========================
   SIMPAN DATA
========================== */
if (isset($_POST['tambah'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $nama_barang = trim($_POST['nama_barang']);
    $keterangan  = trim($_POST['keterangan']);

    if ($nama_barang === '') {
        $_SESSION['error'] = "Nama barang wajib diisi";
    } else {

        $kode_barang = generateKodeBarang($mysqli);

        $stmt = $mysqli->prepare("
            INSERT INTO master_barang_elektronik 
            (kode_barang, nama_barang, keterangan)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $kode_barang, $nama_barang, $keterangan);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Barang elektronik berhasil ditambahkan";
        header("Location: barang_elektronik.php");
        exit;
    }
}

/* ==========================
   AMBIL DATA
========================== */
$data = $mysqli->query("
    SELECT id_barang, kode_barang, nama_barang, keterangan
    FROM master_barang_elektronik
    ORDER BY id_barang DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Master Barang Elektronik</title>
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
form{display:grid;gap:14px;margin-bottom:30px;}
input, textarea, button{
    padding:12px;
    border-radius:8px;
    border:1px solid #d1d5db;
}
textarea{resize:vertical;}
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
}
th,td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
}
th{background:#f3f4f6;text-align:left;}
.alert-success{background:#dcfce7;padding:10px;border-radius:6px;margin-bottom:15px;}
.alert-error{background:#fee2e2;padding:10px;border-radius:6px;margin-bottom:15px;}
.action a{
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}
</style>
</head>
<body>

<div class="container">
<div class="card">

<div class="header">
    <h2>📦 Master Barang Elektronik</h2>
    <a href="index.php">⬅ Kembali</a>
</div>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- FORM ENTRY -->
<form method="post">
    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

    <label>Nama Barang Elektronik</label>
    <input type="text" name="nama_barang" placeholder="Contoh: HP EliteBook X360" required>

    <label>Keterangan</label>
    <textarea name="keterangan" placeholder="Spesifikasi / catatan tambahan"></textarea>

    <button name="tambah">+ Tambah Barang</button>
</form>

<!-- DATA TABLE -->
<table>
<tr>
    <th>Kode Barang</th>
    <th>Nama Barang</th>
    <th>Keterangan</th>
    <th>Aksi</th>
</tr>

<?php while ($r = $data->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($r['kode_barang']) ?></td>
    <td><?= htmlspecialchars($r['nama_barang']) ?></td>
    <td><?= nl2br(htmlspecialchars($r['keterangan'])) ?></td>
    <td class="action">
        <a href="edit_barang_elektronik.php?id=<?= $r['id_barang'] ?>">Edit</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

</body>
</html>
