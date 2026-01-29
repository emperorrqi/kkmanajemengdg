<?php
require 'koneksi.php';

/* ===========================
   FILTER INPUT
=========================== */
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$sto       = trim($_GET['sto'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($tgl_awal && $tgl_akhir) {
    $where[] = "p.posting_date BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
    $types   .= 'ss';
}

if ($sto !== '') {
    $where[] = "p.sto_number LIKE ?";
    $params[] = "%$sto%";
    $types   .= 's';
}

$whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

/* ===========================
   QUERY LAPORAN
=========================== */
$sql = "
    SELECT
        p.posting_date,
        p.sto_number,
        p.sto_item,
        a.nama_admin,
        b.nama_barang,
        s.kode_sbu,
        s.location,
        g.kode_gudang,
        g.nama_gudang,
        p.jumlah,
        p.serial_number
    FROM trx_barang_pesanan p
    JOIN master_administrasi a ON p.id_admin = a.id_admin
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    JOIN master_sbu s ON p.id_sbu = s.id_sbu
    JOIN master_gudang g ON p.id_gudang = g.id_gudang
    $whereSql
    ORDER BY p.posting_date DESC, p.sto_number DESC, p.sto_item ASC
";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* ===========================
   SUMMARY
=========================== */
$total_transaksi = 0;
$total_qty       = 0;
$data            = [];

while ($r = $result->fetch_assoc()) {
    $total_transaksi++;
    $total_qty += $r['jumlah'];
    $data[] = $r;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Barang Pesanan</title>

<style>
body{
    font-family:Inter,Arial;
    background:#f1f5f9;
    padding:30px;
    color:#0f172a
}
.container{max-width:1400px;margin:auto}
.card{
    background:#fff;
    padding:28px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.08)
}
h1{text-align:center;margin-bottom:6px}
.subtitle{text-align:center;color:#64748b;font-size:14px;margin-bottom:25px}

/* FILTER */
.filter{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:14px;
    margin-bottom:25px
}
.filter input,.filter button{
    padding:12px;
    border-radius:10px;
    border:1px solid #d1d5db
}
.filter button{
    background:#2563eb;
    color:#fff;
    border:none;
    font-weight:600;
    cursor:pointer
}

/* SUMMARY */
.summary{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-bottom:20px
}
.box{
    background:#f8fafc;
    border-radius:14px;
    padding:20px;
    text-align:center
}
.box span{font-size:13px;color:#64748b}
.box h3{margin:6px 0 0;font-size:26px}

/* ACTION */
.actions{
    display:flex;
    justify-content:flex-end;
    margin-bottom:20px
}
.print-btn{
    padding:10px 16px;
    border-radius:10px;
    background:#16a34a;
    color:#fff;
    border:none;
    font-weight:600;
    cursor:pointer
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px
}
th,td{
    padding:11px;
    border-bottom:1px solid #e5e7eb;
    text-align:center
}
th{background:#f8fafc;font-weight:600}
.left{text-align:left}

/* PRINT MODE */
.print-header{display:none;text-align:center;margin-bottom:20px}
.print-header h2{margin:0}
.print-header small{color:#475569}

@media print{
    body{background:#fff;padding:0}
    .filter,.actions{display:none}
    .card{box-shadow:none;padding:0}
    .print-header{display:block}
    table{font-size:12px}
    tr{page-break-inside:avoid}
}
</style>
</head>

<body>
<div class="container">
<div class="card">

<!-- PRINT HEADER -->
 <a href="index.php">⬅ Kembali</a>
<div class="print-header">
    <h2>LAPORAN BARANG PESANAN (GOODS ISSUE / STO)</h2>
    <small>
        Periode:
        <?= $tgl_awal ?: 'Semua' ?> s/d <?= $tgl_akhir ?: 'Semua' ?>
        | Dicetak: <?= date('d-m-Y H:i') ?>
    </small>
</div>

<h1>📤 Laporan Barang Pesanan</h1>
<div class="subtitle">Monitoring transaksi pengeluaran barang (STO)</div>

<!-- FILTER -->
<form method="get" class="filter">
    <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
    <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
    <input type="text" name="sto" placeholder="Kode STO" value="<?= htmlspecialchars($sto) ?>">
    <button type="submit">🔍 preview </button>
</form>

<!-- SUMMARY -->
<div class="summary">
    <div class="box">
        <span>Total Transaksi</span>
        <h3><?= $total_transaksi ?></h3>
    </div>
    <div class="box">
        <span>Total Qty Keluar</span>
        <h3><?= number_format($total_qty) ?></h3>
    </div>
    <div class="box">
        <span>Status</span>
        <h3>VALID</h3>
    </div>
</div>

<!-- ACTION -->
<div class="actions">
    <button onclick="window.print()" class="print-btn">🖨 Print Laporan</button>
</div>

<!-- TABLE -->
<table>
<thead>
<tr>
    <th>Tanggal</th>
    <th>STO</th>
    <th>Item</th>
    <th>Barang</th>
    <th>Admin</th>
    <th>SBU</th>
    <th>Gudang</th>
    <th>Qty</th>
    <th>SN / Keterangan</th>
</tr>
</thead>
<tbody>

<?php if (empty($data)): ?>
<tr><td colspan="9">Data tidak ditemukan</td></tr>
<?php endif; ?>

<?php foreach ($data as $r): ?>
<tr>
    <td><?= $r['posting_date'] ?></td>
    <td><?= htmlspecialchars($r['sto_number']) ?></td>
    <td><?= $r['sto_item'] ?></td>
    <td class="left"><?= htmlspecialchars($r['nama_barang']) ?></td>
    <td><?= htmlspecialchars($r['nama_admin']) ?></td>
    <td><?= $r['kode_sbu'].' - '.$r['location'] ?></td>
    <td><?= $r['kode_gudang'].' - '.$r['nama_gudang'] ?></td>
    <td><strong><?= $r['jumlah'] ?></strong></td>
    <td class="left"><?= htmlspecialchars($r['serial_number']) ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
</div>
</body>
</html>
