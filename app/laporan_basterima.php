<?php
require 'koneksi.php';
session_start();

/* ==========================
   PRINT MODE
========================== */
$isPrint = isset($_GET['print']) && $_GET['print'] == '1';

/* ==========================
   FILTER INPUT
========================== */
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$kode      = trim($_GET['kode'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($tgl_awal && $tgl_akhir) {
    $where[] = "b.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
    $types   .= 'ss';
}

if ($kode != '') {
    $where[] = "b.kode_basterima LIKE ?";
    $params[] = "%$kode%";
    $types   .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

/* ==========================
   QUERY DATA
========================== */
$sql = "
    SELECT 
        b.kode_basterima,
        b.tanggal,
        p.kode_pesanan,
        br.nama_barang,
        b.penerima,
        b.jumlah,
        b.sn_perangkat
    FROM trx_berita_serah_terima b
    JOIN trx_barang_pesanan p ON b.id_pesanan = p.id_pesanan
    JOIN master_barang_elektronik br ON b.id_barang = br.id_barang
    $whereSQL
    ORDER BY b.tanggal DESC
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
<title>Laporan BAST</title>

<style>
body{
    font-family:Arial;
    background:#f3f4f6;
    padding:30px
}
.card{
    background:#fff;
    padding:25px;
    max-width:1200px;
    margin:auto;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,.05)
}
h2{text-align:center;margin-bottom:10px}
.subtitle{
    text-align:center;
    font-size:13px;
    color:#555;
    margin-bottom:20px
}
.filter{
    display:grid;
    grid-template-columns:1fr 1fr 1fr auto;
    gap:15px;
    margin-bottom:20px
}
input,button{
    padding:12px;
    border-radius:10px;
    border:1px solid #d1d5db
}
button{
    background:#2563eb;
    color:#fff;
    font-weight:bold;
    border:none;
    cursor:pointer
}
button:hover{background:#1d4ed8}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px
}
th,td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    text-align:center
}
th{background:#f9fafb}
.badge{
    background:#e0f2fe;
    color:#0369a1;
    padding:4px 10px;
    border-radius:999px;
    font-size:13px
}
.actions{text-align:right;margin-bottom:15px}
.btn-print{
    background:#16a34a;
    padding:10px 15px;
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold
}
.empty{
    text-align:center;
    padding:20px;
    color:#6b7280
}

/* ==========================
   PRINT STYLE
========================== */
@media print {
    body{background:#fff;padding:0}
    .card{box-shadow:none;border-radius:0;padding:0;max-width:100%}
    .filter,.actions,a{display:none!important}
    th{
        background:#eee!important;
        -webkit-print-color-adjust:exact
    }
}
</style>
</head>

<body>
<div class="card">

<?php if(!$isPrint): ?>
<a href="index.php">⬅ Kembali</a>
<?php endif; ?>

<h2>BERITA ACARA SERAH TERIMA BARANG</h2>

<div class="subtitle">
    Periode: <?= $tgl_awal ?: '-' ?> s/d <?= $tgl_akhir ?: '-' ?><br>
    Dicetak: <?= date('d-m-Y H:i') ?>
</div>

<?php if(!$isPrint): ?>
<form method="get" class="filter">
    <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
    <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
    <input type="text" name="kode" value="<?= htmlspecialchars($kode) ?>" placeholder="">
    <button>🔍 Filter</button>
</form>
<?php endif; ?>

<?php if($data->num_rows > 0 && !$isPrint): ?>
<div class="actions">
    <a target="_blank" class="btn-print"
       href="?print=1&tgl_awal=<?= urlencode($tgl_awal) ?>&tgl_akhir=<?= urlencode($tgl_akhir) ?>&kode=<?= urlencode($kode) ?>">
       🖨 Cetak Laporan
    </a>
</div>
<?php endif; ?>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Kode BAST</th>
    <th>Tanggal</th>
    <th>Pesanan</th>
    <th>Barang</th>
    <th>Penerima</th>
    <th>Jumlah</th>
    <th>SN Perangkat</th>
</tr>
</thead>
<tbody>

<?php if($data->num_rows == 0): ?>
<tr><td colspan="8" class="empty">Tidak ada data</td></tr>
<?php endif; ?>

<?php $no=1; while($r = $data->fetch_assoc()): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><span class="badge"><?= $r['kode_basterima'] ?></span></td>
    <td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
    <td><?= $r['kode_pesanan'] ?></td>
    <td><?= $r['nama_barang'] ?></td>
    <td><?= $r['penerima'] ?></td>
    <td><?= $r['jumlah'] ?></td>
    <td style="text-align:left"><?= nl2br(htmlspecialchars($r['sn_perangkat'])) ?></td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

<?php if($isPrint): ?>
<br><br>
</tr>
</table>

<script>window.print();</script>
<?php endif; ?>

</div>
</body>
</html>
