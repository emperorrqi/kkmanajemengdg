<?php
require 'koneksi.php';

/* ==========================================
   AMBIL PARAMETER KODE PESANAN
========================================== */
$kode = $_GET['kode'] ?? '';

if (!$kode) {
    die('Kode Pesanan tidak valid');
}

/* ==========================================
   HEADER DATA
========================================== */
$h = $mysqli->prepare("
    SELECT
        p.kode_pesanan,
        p.posting_date,
        p.entry_date,
        p.document_date,
        p.mvt_type,
        p.batch,
        a.nama_admin,
        g.kode_gudang,
        g.nama_gudang
    FROM trx_barang_pesanan p
    JOIN master_administrasi a ON p.id_admin = a.id_admin
    JOIN master_gudang g ON p.id_gudang = g.id_gudang
    WHERE p.kode_pesanan = ?
    LIMIT 1
");
$h->bind_param("s", $kode);
$h->execute();
$header = $h->get_result()->fetch_assoc();
$h->close();

if (!$header) {
    die("Data tidak ditemukan");
}

/* ==========================================
   DETAIL DATA
========================================== */
$d = $mysqli->prepare("
    SELECT
        p.sto_item,
        p.batch,
        b.kode_barang,
        b.nama_barang,
        s.kode_sbu,
        s.location,
        p.jumlah,
        p.serial_number
    FROM trx_barang_pesanan p
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    JOIN master_sbu s ON p.id_sbu = s.id_sbu
    WHERE p.kode_pesanan = ?
    ORDER BY p.sto_item
");
$d->bind_param("s", $kode);
$d->execute();
$detail = $d->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Goods Issue Slip</title>

<style>
body{
    font-family:"Courier New", monospace;
    font-size:12px;
    color:#000;
    margin:30px;
}
@media print{
    body{ margin:20px }
}

.header{
    display:flex;
    align-items:center;
    border-bottom:2px solid #000;
    padding-bottom:10px;
    margin-bottom:14px;
}
.logo{
    width:70px;
    margin-right:15px;
}
.company h1{
    font-size:16px;
    margin:0;
}
.company p{
    margin:2px 0;
    font-size:11px;
}

.doc-title{
    display:flex;
    justify-content:space-between;
    font-weight:bold;
    margin-bottom:6px;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:6px;
}
th{
    border-bottom:1.5px solid #000;
    padding:6px 4px;
    text-align:left;
}
td{
    padding:5px 4px;
    vertical-align:top;
}
.center{text-align:center}
.right{text-align:right}
.small{font-size:11px}

hr{
    border:1px solid #000;
    margin:10px 0;
}
</style>
</head>

<body onload="window.print()">

<!-- HEADER -->
<div class="header">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="company">
        <h1>PT Icon Plus</h1>
        <p>Jl. KH. Abdul Rochim No.1</p>
        <p>Kuningan Barat, Mampang</p>
        <p>Jakarta Selatan 12710</p>
    </div>
</div>

<!-- TITLE -->
<div class="doc-title">
    <div>GOODS ISSUE SLIP</div>
    <div>No: <?= htmlspecialchars($header['kode_pesanan']) ?></div>
</div>

<!-- INFO HEADER -->
<table>
<tr>
<td width="50%">
Posting Date  : <?= date('d.m.Y', strtotime($header['posting_date'])) ?><br>
Entry Date    : <?= date('d.m.Y', strtotime($header['entry_date'])) ?><br>
Document Date : <?= date('d.m.Y', strtotime($header['document_date'])) ?><br>
</td>
<td width="50%" class="right">
Plant         : <?= htmlspecialchars($header['kode_gudang']) ?><br>
Description   : <?= htmlspecialchars($header['nama_gudang']) ?><br>
Movement Type : <?= htmlspecialchars($header['mvt_type']) ?>
</td>
</tr>
</table>

<hr>

<!-- DETAIL -->
<table>
<tr>
<th width="6%">Itm</th>
<th width="12%">Material</th>
<th>Description</th>
<th width="10%">Batch</th>
<th width="18%">SBU / Location</th>
<th width="10%" class="right">Qty</th>
<th width="22%">Serial Number</th>
</tr>

<?php while($r = $detail->fetch_assoc()): ?>
<tr>
<td class="center"><?= htmlspecialchars($r['sto_item'] ?? '001') ?></td>
<td><?= htmlspecialchars($r['kode_barang']) ?></td>
<td><?= htmlspecialchars($r['nama_barang']) ?></td>
<td><?= htmlspecialchars($r['batch'] ?: '-') ?></td>
<td><?= htmlspecialchars($r['kode_sbu'].' / '.$r['location']) ?></td>
<td class="right"><?= number_format($r['jumlah'],0) ?></td>
<td class="small">
<?= $r['serial_number'] ? nl2br(htmlspecialchars($r['serial_number'])) : '-' ?>
</td>
</tr>
<?php endwhile; ?>
</table>

<hr>

<!-- SIGNATURE -->
<br><br>
<table width="100%" style="text-align:center;">
<tr>
<td width="33%">
Recipient<br><br><br><br>
(_____________________)
</td>
<td width="33%">
Issued By<br><br><br><br>
<?= htmlspecialchars($header['nama_admin']) ?>
</td>
<td width="33%">
Approved By<br><br><br><br>
(_____________________)
</td>
</tr>
</table>

</body>
</html>
