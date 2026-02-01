<?php
require 'koneksi.php';

$kode_po = $_GET['kode_po'] ?? '';
if ($kode_po === '') {
    die('Kode PO tidak ditemukan');
}

/* =========================
   AMBIL DATA PURCHASE ORDER
========================= */
$stmt = $mysqli->prepare("
    SELECT 
        p.kode_po,
        p.pr_number,
        p.tanggal_po,
        p.delivery_date,
        p.buyer,
        p.invoice_to,
        p.ship_to,
        p.project_name,
        p.jumlah,
        p.uom,
        p.unit_price,
        p.total,
        v.nama_vendor,
        v.alamat,
        v.telepon,
        b.kode_barang,
        b.nama_barang
    FROM trx_purchase_order p
    JOIN master_vendor v ON p.id_vendor = v.id_vendor
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    WHERE p.kode_po = ?
");
$stmt->bind_param("s", $kode_po);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die('Data Purchase Order tidak ditemukan');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Purchase Order - <?= htmlspecialchars($data['kode_po']) ?></title>

<style>
@page { size:A4; margin:20mm }

body{
    font-family:"Segoe UI", Arial, sans-serif;
    font-size:13px;
    color:#111827;
}

/* ================= HEADER ================= */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:3px solid #000;
    padding-bottom:12px;
    margin-bottom:20px;
}
.company{
    display:flex;
    align-items:center;
    gap:14px;
}
.company img{height:55px}
.company h1{margin:0;font-size:20px}
.company p{margin:2px 0;font-size:12px}
.doc-title{text-align:right}
.doc-title h2{margin:0;font-size:18px}

/* ================= META ================= */
.meta{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:6px 20px;
    margin-bottom:18px;
}
.meta strong{width:130px;display:inline-block}

/* ================= SECTION ================= */
.section{margin-bottom:18px}
.section h3{
    font-size:14px;
    margin-bottom:6px;
    border-bottom:1px solid #000;
}

/* ================= TABLE ================= */
table{
    width:100%;
    border-collapse:collapse;
}
th, td{
    border:1px solid #000;
    padding:8px;
}
th{
    background:#f2f2f2;
}
.text-right{text-align:right}

/* ================= TOTAL ================= */
.total-box{
    width:40%;
    margin-left:auto;
    margin-top:10px;
}
.total-box td{
    border:none;
    padding:6px 0;
}

/* ================= NOTE ================= */
.note-box{
    margin-top:22px;
    padding:10px;
    border:1px solid #000;
    font-size:12px;
}

/* ================= SIGNATURE ================= */
.sign-table{
    margin-top:50px;
    width:100%;
    border-collapse:collapse;
}
.sign-table th,
.sign-table td{
    border:1px solid #000;
    padding:8px;
    vertical-align:top;
}
.sign-space{height:70px}

@media print{
    body{margin:0}
}
</style>
</head>

<body onload="window.print()">

<!-- ================= HEADER ================= -->
<div class="header">
    <div class="company">
        <img src="logo.png" alt="Logo">
        <div>
            <h1>PT Icon Plus</h1>
            <p>Jl. KH. Abdul Rochim No.1</p>
            <p>Kuningan Barat, Mampang</p>
            <p>Jakarta Selatan 12710</p>
        </div>
    </div>
    <div class="doc-title">
        <h2>PURCHASE ORDER</h2>
        <div><?= htmlspecialchars($data['kode_po']) ?></div>
    </div>
</div>

<!-- ================= META ================= -->
<div class="meta">
    <div><strong>PO Number</strong>: <?= htmlspecialchars($data['kode_po']) ?></div>
    <div><strong>PR Number</strong>: <?= htmlspecialchars($data['pr_number'] ?: '-') ?></div>
    <div><strong>Tanggal PO</strong>: <?= date('d M Y', strtotime($data['tanggal_po'])) ?></div>
    <div><strong>Buyer</strong>: <?= htmlspecialchars($data['buyer']) ?></div>
    <div><strong>Project</strong>: <?= htmlspecialchars($data['project_name']) ?></div>
</div>

<!-- ================= VENDOR ================= -->
<div class="section">
    <h3>Vendor Information</h3>
    <strong><?= htmlspecialchars($data['nama_vendor']) ?></strong><br>
    <?= htmlspecialchars($data['alamat']) ?><br>
    Telp: <?= htmlspecialchars($data['telepon']) ?>
</div>

<!-- ================= SHIPPING ================= -->
<div class="section">
    <h3>Invoice & Shipping</h3>
    <strong>Invoice To:</strong> <?= htmlspecialchars($data['invoice_to']) ?><br>
    <strong>Ship To:</strong> <?= htmlspecialchars($data['ship_to']) ?>
</div>

<!-- ================= ITEM ================= -->
<div class="section">
<h3>Order Detail</h3>
<table>
<tr>
    <th>No</th>
    <th>Material</th>
    <th>Description</th>
    <th>Qty</th>
    <th>UOM</th>
    <th>Delivery Date</th>
    <th>Harga</th>
    <th>Total</th>
</tr>
<tr>
    <td>1</td>
    <td><?= htmlspecialchars($data['kode_barang']) ?></td>
    <td><?= htmlspecialchars($data['nama_barang']) ?></td>
    <td><?= (int)$data['jumlah'] ?></td>
    <td><?= htmlspecialchars($data['uom']) ?></td>
    <td>
        <?= $data['delivery_date'] 
            ? date('d M Y', strtotime($data['delivery_date'])) 
            : '-' ?>
    </td>
    <td class="text-right">
        Rp <?= number_format($data['unit_price'],2,',','.') ?>
    </td>
    <td class="text-right">
        Rp <?= number_format($data['total'],2,',','.') ?>
    </td>
</tr>
</table>
</div>

<!-- ================= TOTAL ================= -->
<table class="total-box">
<tr>
    <td><strong>Grand Total</strong></td>
    <td class="text-right">
        <strong>Rp <?= number_format($data['total'],2,',','.') ?></strong>
    </td>
</tr>
</table>

<!-- ================= NOTE ================= -->
<div class="note-box">
<strong>Catatan:</strong>
<ol style="margin:6px 0 0 18px">
    <li>Purchase Order ini sah dan mengikat setelah ditandatangani oleh kedua belah pihak.</li>
    <li>Harga sudah termasuk seluruh kewajiban sesuai perjanjian yang disepakati.</li>
</ol>
</div>

<!-- SIGNATURE (SESUAI FOTO) -->
<table class="sign-table">
<tr>
    <th width="50%">Diterbitkan Oleh :</th>
    <th width="50%">Diterima dan disetujui :</th>
</tr>
<tr>
    <td>
        <strong>PT. Indonesia Comnets Plus</strong>
        <div class="sign-space"></div>
        Puspa Ichsan Prakoso<br>
        VP Pengadaan PLN
    </td>
    <td>
        <strong>Tanggal :</strong><br><br>
        <strong>Perusahaan :</strong>
        <div class="sign-space"></div>
        <em>(Materai yang berlaku)</em>
    </td>
</tr>
</table>

</body>
</html>