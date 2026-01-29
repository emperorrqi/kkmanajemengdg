<?php
require 'koneksi.php';

/* ==========================
   FILTER
========================== */
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$kode      = trim($_GET['kode'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($tgl_awal && $tgl_akhir) {
    $where[]  = "r.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
    $types   .= 'ss';
}

if ($kode !== '') {
    $where[]  = "r.kode_retur LIKE ?";
    $params[] = "%$kode%";
    $types   .= 's';
}

$whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

$sql = "
    SELECT
        r.id_retur,
        r.kode_retur,
        r.tanggal,
        b.nama_barang,
        r.jumlah,
        r.alasan
    FROM trx_retur r
    JOIN master_barang_elektronik b ON r.id_barang=b.id_barang
    $whereSql
    ORDER BY r.tanggal DESC
";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$data = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Retur Barang</title>
<style>
body{
    font-family:Inter,Arial;
    background:#f1f5f9;
    padding:30px
}
.card{
    background:#fff;
    padding:25px;
    max-width:1100px;
    margin:auto;
    border-radius:16px;
    box-shadow:0 6px 20px rgba(0,0,0,.08)
}
h2{text-align:center;margin-bottom:20px}
.filter{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:20px
}
input,button{
    padding:10px;
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
button:hover{background:#1e40af}
.table-wrap{overflow-x:auto}
table{
    width:100%;
    border-collapse:collapse
}
th,td{
    padding:10px;
    border-bottom:1px solid #e5e7eb;
    text-align:center;
    font-size:14px
}
th{background:#f8fafc}
.badge{
    background:#fee2e2;
    color:#b91c1c;
    padding:4px 8px;
    border-radius:6px;
    font-size:12px;
    font-weight:600
}
.btn-print{
    background:#16a34a;
    color:#fff;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:12px
}
.btn-print:hover{background:#15803d}

@media print{
    body{background:#fff;padding:0;font-size:12px}
    .filter,.btn-print,.print-hide{display:none}
    .card{box-shadow:none;border-radius:0;padding:0}
    .print-only{display:block}
}
.print-only{
    display:none;
    text-align:center;
    margin-bottom:10px
}
</style>
</head>
<body>

<div class="card">
<a href="index.php" class="print-hide">⬅ Kembali</a>

<h2>📊 Laporan Retur Barang</h2>

<div class="print-only">
    <strong>LAPORAN RETUR BARANG</strong><br>
    Periode:
    <?= $tgl_awal ?: '-' ?> s/d <?= $tgl_akhir ?: '-' ?>
</div>

<form method="get" class="filter">
    <input type="date" name="tgl_awal" value="<?= $tgl_awal ?>">
    <input type="date" name="tgl_akhir" value="<?= $tgl_akhir ?>">
    <input type="text" name="kode" placeholder="Kode Retur"
           value="<?= htmlspecialchars($kode) ?>">
    <button type="submit">🔍 Filter</button>
</form>

<div style="text-align:right;margin-bottom:15px">
    <button onclick="window.print()" class="btn-print">
        🖨 Cetak Laporan
    </button>
</div>

<div class="table-wrap">
<table>
<thead>
<tr>
    <th>Tanggal</th>
    <th>Kode Retur</th>
    <th>Barang</th>
    <th>Jumlah</th>
    <th>Alasan</th>
    <th class="print-hide">Aksi</th>
</tr>
</thead>
<tbody>

<?php if($data->num_rows==0): ?>
<tr>
    <td colspan="6" style="color:#6b7280">
        Data tidak ditemukan
    </td>
</tr>
<?php endif; ?>

<?php while($r=$data->fetch_assoc()): ?>
<tr>
    <td><?= $r['tanggal'] ?></td>
    <td><span class="badge"><?= $r['kode_retur'] ?></span></td>
    <td><?= htmlspecialchars($r['nama_barang']) ?></td>
    <td><?= (int)$r['jumlah'] ?></td>
    <td><?= nl2br(htmlspecialchars($r['alasan'])) ?></td>
    <td class="print-hide">
        <a target="_blank"
           class="btn-print"
           href="cetak_retur.php?id=<?= $r['id_retur'] ?>">
           🖨 Print
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>
</div>

</div>
</body>
</html>
