<?php
require 'koneksi.php';
session_start();

/* ==========================
   CSRF TOKEN
========================== */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ==========================
   GENERATE KODE RETUR
========================== */
function generateKodeRetur($mysqli) {
    $tahun = date('Y');
    $q = $mysqli->query("
        SELECT COUNT(*) total 
        FROM trx_retur 
        WHERE YEAR(tanggal)= '$tahun'
    ");
    $n = ($q->fetch_assoc()['total'] ?? 0) + 1;
    return 'RET-' . $tahun . '-' . str_pad($n, 4, '0', STR_PAD_LEFT);
}

/* ==========================
   SIMPAN RETUR
========================== */
if (isset($_POST['simpan'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $kode_retur = generateKodeRetur($mysqli);
    $tanggal    = $_POST['tanggal'] ?? '';
    $id_barang  = (int) ($_POST['id_barang'] ?? 0);
    $jumlah     = (int) ($_POST['jumlah'] ?? 0);
    $alasan     = trim($_POST['alasan'] ?? '');

    if ($tanggal=='' || $id_barang<=0 || $jumlah<=0 || $alasan=='') {
        die("❌ Data tidak lengkap");
    }

    $stmt = $mysqli->prepare("
        INSERT INTO trx_retur
        (kode_retur,tanggal,id_barang,jumlah,alasan)
        VALUES (?,?,?,?,?)
    ");
    $stmt->bind_param(
        "ssiis",
        $kode_retur,
        $tanggal,
        $id_barang,
        $jumlah,
        $alasan
    );
    $stmt->execute();
    $stmt->close();

    header("Location: entry_retur.php?ok=1");
    exit;
}

/* ==========================
   DATA BARANG
========================== */
$barang = $mysqli->query("
    SELECT id_barang,nama_barang
    FROM master_barang_elektronik
    ORDER BY nama_barang ASC
");

/* ==========================
   LIST RETUR
========================== */
$list = $mysqli->query("
    SELECT
        r.id_retur,
        r.kode_retur,
        r.tanggal,
        b.nama_barang,
        r.jumlah,
        r.alasan
    FROM trx_retur r
    JOIN master_barang_elektronik b ON r.id_barang=b.id_barang
    ORDER BY r.id_retur DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Retur Barang</title>
<style>
body{
    font-family:Inter,Arial;
    background:#f3f4f6;
    padding:30px
}
.card{
    background:#fff;
    max-width:900px;
    margin:auto;
    padding:25px;
    border-radius:14px;
    box-shadow:0 6px 20px rgba(0,0,0,.08)
}
input,select,textarea,button{
    width:100%;
    padding:12px;
    margin-top:8px;
    border-radius:8px;
    border:1px solid #d1d5db
}
button{
    background:#dc2626;
    color:#fff;
    font-weight:600;
    border:none;
    cursor:pointer
}
button:hover{background:#b91c1c}
.success{
    background:#dcfce7;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:25px
}
th,td{
    padding:10px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px
}
th{background:#f9fafb}
.badge{
    background:#fee2e2;
    color:#b91c1c;
    padding:4px 8px;
    border-radius:6px;
    font-size:12px;
    font-weight:600
}
.btn-print{
    background:#2563eb;
    color:#fff;
    padding:6px 12px;
    border-radius:6px;
    font-size:12px;
    text-decoration:none
}
.btn-print:hover{background:#1e40af}
</style>
</head>

<body>
<div class="card">

<a href="index.php">⬅ Kembali</a>
<h2 style="text-align:center;">↩️ Entry Retur Barang</h2>

<?php if(isset($_GET['ok'])): ?>
<div class="success">✔ Retur berhasil disimpan</div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

<label>Tanggal</label>
<input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>

<label>Barang</label>
<select name="id_barang" required>
<option value="">-- Pilih Barang --</option>
<?php while($b=$barang->fetch_assoc()): ?>
<option value="<?= $b['id_barang'] ?>">
<?= htmlspecialchars($b['nama_barang']) ?>
</option>
<?php endwhile; ?>
</select>

<label>Jumlah Retur</label>
<input type="number" name="jumlah" min="1" required>

<label>Alasan Retur</label>
<textarea name="alasan" rows="3" required></textarea>

<button name="simpan">💾 Tambah Retur</button>
</form>

<hr style="margin:35px 0">

<h3 style="text-align:center;">📄 Daftar Retur Barang</h3>

<table>
<thead>
<tr>
<th>Kode Retur</th>
<th>Tanggal</th>
<th>Barang</th>
<th>Jumlah</th>
<th>Alasan</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>

<?php if($list->num_rows==0): ?>
<tr>
<td colspan="6" style="text-align:center;color:#6b7280">
Belum ada data retur
</td>
</tr>
<?php endif; ?>

<?php while($r=$list->fetch_assoc()): ?>
<tr>
<td><span class="badge"><?= $r['kode_retur'] ?></span></td>
<td><?= $r['tanggal'] ?></td>
<td><?= htmlspecialchars($r['nama_barang']) ?></td>
<td><?= (int)$r['jumlah'] ?></td>
<td><?= nl2br(htmlspecialchars($r['alasan'])) ?></td>
<td>
<a href="cetak_retur.php?id=<?= $r['id_retur'] ?>"
   target="_blank"
   class="btn-print"
   onclick="return confirm('Cetak retur <?= $r['kode_retur'] ?> ?')">
   🖨 Print
</a>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>
</body>
</html>
