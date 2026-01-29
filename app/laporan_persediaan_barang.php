<?php
/**
 * =====================================================
 * LAPORAN PERSEDIAAN BARANG (FINAL – ENTERPRISE)
 * Author  : Senior PHP & System Engineer
 * Konsep  : ERP Inventory Report
 * =====================================================
 */

require 'koneksi.php';

/* ===========================
   MODE CETAK
=========================== */
$isPrint = isset($_GET['id_po']) && $_GET['id_po'] !== '';
$id_po   = $isPrint ? intval($_GET['id_po']) : null;

/* ===========================
   FILTER TANGGAL
=========================== */
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

/* ===========================
   WHERE DINAMIS (AMAN)
=========================== */
$whereArr = [];

if ($isPrint) {
    $whereArr[] = "p.id_po = $id_po";
}

if ($tgl_awal && $tgl_akhir) {
    $whereArr[] = "p.tanggal_po BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$where = '';
if (!empty($whereArr)) {
    $where = 'WHERE ' . implode(' AND ', $whereArr);
}

/* ===========================
   QUERY UTAMA (AKURAT)
=========================== */
$sql = "
    SELECT
        p.id_po,
        p.kode_po,
        p.tanggal_po,
        b.nama_barang,
        v.nama_vendor,

        IFNULL(m.total_masuk,0) AS barang_masuk,
        IFNULL(k.total_keluar,0) AS barang_keluar,
        (IFNULL(m.total_masuk,0) - IFNULL(k.total_keluar,0)) AS stok_akhir,

        p.unit_price,
        ((IFNULL(m.total_masuk,0) - IFNULL(k.total_keluar,0)) * p.unit_price) AS total_nilai

    FROM trx_purchase_order p
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    JOIN master_vendor v ON p.id_vendor = v.id_vendor

    LEFT JOIN (
        SELECT id_po, SUM(jumlah) total_masuk
        FROM trx_persediaan_barang
        GROUP BY id_po
    ) m ON p.id_po = m.id_po

    LEFT JOIN (
        SELECT id_barang, SUM(jumlah) total_keluar
        FROM trx_barang_pesanan
        GROUP BY id_barang
    ) k ON p.id_barang = k.id_barang

    $where
    ORDER BY stok_akhir DESC, p.kode_po DESC
";

$result = $mysqli->query($sql);

/* ===========================
   SUMMARY
=========================== */
$total_po = $total_stok = $total_nilai = 0;
$data = [];

while ($row = $result->fetch_assoc()) {
    $total_po++;
    $total_stok  += $row['stok_akhir'];
    $total_nilai += $row['total_nilai'];
    $data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Persediaan Barang</title>

<style>
body{
    font-family:Inter,Arial,Helvetica;
    background:#f1f5f9;
    padding:30px;
    color:#0f172a
}
.container{max-width:1400px;margin:auto}
.card{
    background:#fff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 12px 35px rgba(0,0,0,.08)
}
h1{text-align:center;margin-bottom:20px}

/* FILTER */
.filter{
    display:flex;
    gap:15px;
    margin-bottom:30px;
    align-items:end
}
.filter input{
    padding:8px;
    border-radius:8px;
    border:1px solid #cbd5f5
}

/* SUMMARY */
.summary{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-bottom:35px
}
.box{
    background:#f8fafc;
    border-radius:14px;
    padding:22px;
    text-align:center
}
.box span{font-size:13px;color:#64748b}
.box h3{margin:6px 0 0;font-size:26px}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px
}
th,td{
    padding:14px 10px;
    border-bottom:1px solid #e5e7eb;
    text-align:center
}
th{background:#f8fafc;font-weight:600}
td.left{text-align:left}

/* STATUS */
.stok-aman{color:#065f46;font-weight:700}
.stok-habis{color:#b91c1c;font-weight:800}

/* BUTTON */
.btn{
    padding:8px 14px;
    border-radius:8px;
    background:#2563eb;
    color:#fff;
    border:none;
    cursor:pointer;
    font-size:13px
}
.btn.secondary{background:#64748b}
.btn:hover{opacity:.9}

/* PRINT */
@media print{
    body{background:#fff;padding:0}
    .btn,.summary,.filter{display:none}
}
</style>
</head>

<body>

<div class="container">
<div class="card">

<h1>
📦 <?= $isPrint ? 'DETAIL PERSEDIAAN PURCHASE ORDER' : 'LAPORAN PERSEDIAAN BARANG' ?>
</h1>

<?php if($isPrint && $tgl_awal && $tgl_akhir): ?>
<p style="text-align:center;font-size:13px;color:#475569">
Periode: <?= date('d M Y',strtotime($tgl_awal)) ?> – <?= date('d M Y',strtotime($tgl_akhir)) ?>
</p>
<?php endif; ?>

<?php if(!$isPrint): ?>
<!-- FILTER -->
<form method="get" class="filter">
    <div>
        <label>Tanggal Awal</label><br>
        <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
    </div>
    <div>
        <label>Tanggal Akhir</label><br>
        <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
    </div>
    <div>
        <button class="btn">🔍 Filter</button>
        <a href="laporan_persediaan_barang.php" class="btn secondary">Reset</a>
    </div>
</form>

<!-- SUMMARY -->
<div class="summary">
    <div class="box">
        <span>Total Purchase Order</span>
        <h3><?= $total_po ?></h3>
    </div>
    <div class="box">
        <span>Total Stok Tersedia</span>
        <h3><?= number_format($total_stok) ?></h3>
    </div>
    <div class="box">
        <span>Total Nilai Persediaan</span>
        <h3>Rp <?= number_format($total_nilai,0,',','.') ?></h3>
    </div>
</div>
<?php endif; ?>

<table>
<thead>
<tr>
    <th>PO</th>
    <th>Tanggal</th>
    <th>Barang</th>
    <th>Vendor</th>
    <th>Masuk</th>
    <th>Keluar</th>
    <th>Stok Akhir</th>
    <th>Nilai</th>
    <?php if(!$isPrint): ?><th>Aksi</th><?php endif; ?>
</tr>
</thead>
<tbody>

<?php if(empty($data)): ?>
<tr><td colspan="9">Data tidak ditemukan</td></tr>
<?php endif; ?>

<?php foreach($data as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['kode_po']) ?></td>
    <td><?= date('d-m-Y',strtotime($r['tanggal_po'])) ?></td>
    <td class="left"><?= htmlspecialchars($r['nama_barang']) ?></td>
    <td><?= htmlspecialchars($r['nama_vendor']) ?></td>
    <td><?= $r['barang_masuk'] ?></td>
    <td><?= $r['barang_keluar'] ?></td>

    <?php $cls = $r['stok_akhir'] <= 0 ? 'stok-habis' : 'stok-aman'; ?>
    <td class="<?= $cls ?>"><?= $r['stok_akhir'] ?></td>

    <td><strong>Rp <?= number_format($r['total_nilai'],0,',','.') ?></strong></td>

    <?php if(!$isPrint): ?>
    <td>
        <form method="get" target="_blank">
            <input type="hidden" name="id_po" value="<?= $r['id_po'] ?>">
            <input type="hidden" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
            <input type="hidden" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
            <button class="btn">🖨️ Cetak</button>
        </form>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
</div>

<?php if($isPrint): ?>
<script>
window.onload = () => window.print();
</script>
<?php endif; ?>

</body>
</html>
