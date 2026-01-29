<?php
include 'koneksi.php';

$kode_po = $_GET['kode_po'] ?? '';
if (!$kode_po) die('Kode PO tidak ditemukan');

// Ambil data PO
$stmt = $mysqli->prepare("
    SELECT p.*, v.kode_vendor, v.nama_vendor, v.alamat AS alamat_vendor,
           b.kode_barang, b.nama_barang
    FROM trx_purchase_order p
    JOIN master_vendor v ON p.id_vendor = v.id_vendor
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    WHERE p.kode_po=?
");
$stmt->bind_param("s", $kode_po);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) die('PO tidak ditemukan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Purchase Order <?= htmlspecialchars($data['kode_po']) ?></title>
<style>
@page { size: A4; margin: 20mm; }

body {
    font-family: "Segoe UI", Arial, sans-serif;
    color: #333;
    line-height: 1.4;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #000;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

header .company {
    display: flex;
    align-items: center;
    font-weight: 600;
}

header .company img {
    width: 80px; /* ukuran logo */
    height: auto;
    margin-right: 15px;
}

header .company .company-info h2 {
    margin: 0;
    font-size: 22px;
    text-transform: uppercase;
}

header .company .company-info p {
    margin: 2px 0;
    font-size: 12px;
}

header .po-info {
    text-align: right;
}

header .po-info h1 {
    margin: 0;
    font-size: 28px;
    letter-spacing: 2px;
}

header .po-info p {
    font-size: 13px;
    margin: 2px 0;
}

.section {
    margin-bottom: 25px;
}

.info-grid {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.info-box {
    border: 1px solid #333;
    padding: 10px 15px;
    width: 48%;
    border-radius: 6px;
}

.info-box h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    text-transform: uppercase;
    font-weight: 600;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 13px;
}

table th, table td {
    border: 1px solid #333;
    padding: 10px;
    text-align: left;
}

table th {
    background-color: #f5f5f5;
    text-transform: uppercase;
    font-size: 12px;
}

table tfoot td {
    font-weight: 600;
    text-align: right;
    font-size: 13px;
}

.total-box {
    text-align: right;
    padding-right: 10px;
}

.ttd {
    display: flex;
    justify-content: space-between;
    margin-top: 60px;
}

.ttd div {
    width: 30%;
    text-align: center;
}

.ttd p {
    margin-bottom: 80px;
}

.print-only {
    display: block;
}

@media print {
    .no-print { display: none; }
}
</style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px;">
    <button onclick="window.print()" style="padding:10px 20px; background:#2563eb; color:white; border:none; border-radius:6px; font-weight:600; cursor:pointer;">🖨️ Print PO</button>
</div>

<header>
    <div class="company">
        <!-- Logo Perusahaan -->
        <img src="logo.jpeg" alt="Logo Perusahaan">
        <div class="company-info">
            <h2>PT PLN Indonesia Comnets Plus (PLN Icon Plus)</h2>
            <p>Jl. KH Abdul Rochim No 1</p>
            <p>Kunigan Barat, Mampang</p>
            <p>Jakarta Selatan 12710</p>
            <p>Indonesia</p>
        </div>
    </div>
    <div class="po-info">
        <h1>Purchase Order</h1>
        <p>No: <strong><?= htmlspecialchars($data['kode_po']) ?></strong></p>
        <p>Tanggal: <?= date('d M Y', strtotime($data['tanggal_po'])) ?></p>
    </div>
</header>

<div class="section info-grid">
    <div class="info-box">
        <h4>Vendor</h4>
        <p><strong><?= htmlspecialchars($data['nama_vendor']) ?></strong></p>
        <p><?= htmlspecialchars($data['alamat_vendor'] ?? '-') ?></p>
    </div>
    <div class="info-box">
        <h4>Informasi Pengiriman</h4>
        <p><strong>Invoice To:</strong> <?= htmlspecialchars($data['invoice_to']) ?></p>
        <p><strong>Ship To:</strong> <?= htmlspecialchars($data['ship_to']) ?></p>
        <p><strong>Project:</strong> <?= htmlspecialchars($data['project_name']) ?></p>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:5%;">No</th>
            <th>Deskripsi Barang</th>
            <th style="width:10%;">Qty</th>
            <th style="width:10%;">UOM</th>
            <th style="width:15%;">Unit Price</th>
            <th style="width:15%;">Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="center">1</td>
            <td><?= htmlspecialchars($data['kode_barang'] . ' - ' . $data['nama_barang']) ?></td>
            <td align="center"><?= $data['jumlah'] ?></td>
            <td align="center"><?= htmlspecialchars($data['uom']) ?></td>
            <td align="right">Rp <?= number_format($data['unit_price'],0,',','.') ?></td>
            <td align="right">Rp <?= number_format($data['total'],0,',','.') ?></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="total-box">TOTAL</td>
            <td>Rp <?= number_format($data['total'],0,',','.') ?></td>
        </tr>
    </tfoot>
</table>

<div class="ttd">
    <div>
        <p>Diterbitkan Oleh</p>
        <P> PT,Indonesia Comnets Plus<P>



        _______________________
    </div>
    <div>
        <p>Diterima dan Disetujui</p>
       <P> Tanggal=<P>
       <P> Perusahaan=<P>



        _______________________
    </div>
    <div>
     