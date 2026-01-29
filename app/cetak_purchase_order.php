<?php
include 'koneksi.php';

$kode_po = $_GET['kode_po'] ?? '';
if ($kode_po == '') {
    die('Kode PO tidak ditemukan');
}

$stmt = $mysqli->prepare("
    SELECT p.kode_po, p.pr_number, p.tanggal_po, p.delivery_date, p.buyer,
           p.invoice_to, p.ship_to, p.project_name,
           p.jumlah, p.uom, p.unit_price, p.total,
           v.nama_vendor, v.alamat, v.telepon,
           b.kode_barang, b.nama_barang
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
    die('Data PO tidak ditemukan');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Purchase Order</title>

<style>
@page {
    size: A4;
    margin: 20mm;
}
body {
    font-family: "Segoe UI", Arial, sans-serif;
    color: #111827;
    font-size: 13px;
}

/* ================= HEADER ================= */
.header {
    display: flex;
    justify-content: space-between;
    border-bottom: 3px solid #1e40af;
    padding-bottom: 12px;
    margin-bottom: 22px;
}
.company h1 {
    margin: 0;
    font-size: 22px;
    color: #1e40af;
}
.company p {
    margin: 2px 0;
    font-size: 12px;
}
.doc-title {
    text-align: right;
}
.doc-title h2 {
    margin: 0;
    font-size: 20px;
    letter-spacing: 1px;
}
.doc-title span {
    font-size: 12px;
}

/* ================= META ================= */
.meta {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px 20px;
    margin-bottom: 25px;
}
.meta div strong {
    display: inline-block;
    width: 120px;
}

/* ================= SECTION ================= */
.section {
    margin-bottom: 22px;
}
.section h3 {
    font-size: 14px;
    margin-bottom: 8px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 4px;
}

/* ================= TABLE ================= */
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid #d1d5db;
    padding: 10px;
}
th {
    background: #f3f4f6;
    text-align: left;
}
.text-right {
    text-align: right;
}

/* ================= TOTAL ================= */
.total-box {
    width: 40%;
    margin-left: auto;
    margin-top: 10px;
}
.total-box td {
    border: none;
    padding: 6px 0;
}

/* ================= NOTE ================= */
.note-box {
    margin-top: 30px;
    padding: 12px 14px;
    border: 1px dashed #9ca3af;
    background: #f9fafb;
    font-size: 12px;
    line-height: 1.6;
}
.note-box strong {
    display: block;
    margin-bottom: 6px;
}

/* ================= SIGNATURE ================= */
.footer {
    margin-top: 70px;
    display: flex;
    justify-content: space-between;
}
.signature {
    text-align: center;
    width: 220px;
}
.signature .line {
    margin-top: 60px;
    border-top: 1px solid #111827;
}

@media print {
    body {
        margin: 0;
    }
}
</style>
</head>

<body onload="window.print()">

<!-- HEADER -->
<div class="header">
    <div class="company">
        <h1>PT MAJU JAYA SEJAHTERA</h1>
        <p>Jl. Industri No. 123, Jakarta</p>
        <p>Telp. (021) 555-1234</p>
    </div>
    <div class="doc-title">
        <h2>PURCHASE ORDER</h2>
        <span><?= htmlspecialchars($data['kode_po']) ?></span>
    </div>
</div>

<!-- META -->
<div class="meta">
    <div><strong>PO Number</strong>: <?= htmlspecialchars($data['kode_po']) ?></div>
    <div><strong>PR Number</strong>: <?= htmlspecialchars($data['pr_number']) ?></div>

    <div><strong>Tanggal PO</strong>: <?= date('d M Y', strtotime($data['tanggal_po'])) ?></div>
    <div><strong>Delivery Date</strong>: <?= $data['delivery_date'] ? date('d M Y', strtotime($data['delivery_date'])) : '-' ?></div>

    <div><strong>Buyer</strong>: <?= htmlspecialchars($data['buyer']) ?></div>
    <div><strong>Project</strong>: <?= htmlspecialchars($data['project_name']) ?></div>
</div>

<!-- VENDOR -->
<div class="section">
    <h3>Vendor Information</h3>
    <strong><?= htmlspecialchars($data['nama_vendor']) ?></strong><br>
    <?= htmlspecialchars($data['alamat']) ?><br>
    Telp: <?= htmlspecialchars($data['telepon']) ?>
</div>

<!-- SHIPPING -->
<div class="section">
    <h3>Invoice & Shipping</h3>
    <p><strong>Invoice To:</strong> <?= htmlspecialchars($data['invoice_to']) ?></p>
    <p><strong>Ship To:</strong> <?= htmlspecialchars($data['ship_to']) ?></p>
</div>

<!-- ITEM -->
<div class="section">
    <h3>Order Detail</h3>
    <table>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Qty</th>
            <th>UOM</th>
            <th>Harga</th>
            <th>Total</th>
        </tr>
        <tr>
            <td>1</td>
            <td><?= htmlspecialchars($data['kode_barang']) ?></td>
            <td><?= htmlspecialchars($data['nama_barang']) ?></td>
            <td><?= $data['jumlah'] ?></td>
            <td><?= htmlspecialchars($data['uom']) ?></td>
            <td class="text-right">Rp <?= number_format($data['unit_price'],2,',','.') ?></td>
            <td class="text-right">Rp <?= number_format($data['total'],2,',','.') ?></td>
        </tr>
    </table>
</div>

<!-- TOTAL -->
<table class="total-box">
    <tr>
        <td><strong>Grand Total</strong></td>
        <td class="text-right"><strong>Rp <?= number_format($data['total'],2,',','.') ?></strong></td>
    </tr>
</table>

<!-- NOTE -->
<div class="note-box">
    <strong>Note:</strong>
    <ol style="margin:0 0 0 18px; padding:0">
        <li>Harga termasuk pekerjaan, harga, spesifikasi teknis (jika ada), cara pembayaran, dan syarat-syarat lainnya akan diuraikan dalam lampiran Purchase Order.</li>
        <li>Setiap Purchase Order ini harus dikonfirmasi dan dikembalikan sebagai tanda persetujuan atas syarat dan ketentuan yang berlaku.</li>
    </ol>
</div>

<!-- SIGNATURE -->
<div class="footer">
    <div class="signature">
        Disetujui Oleh
        <div class="line"></div>
    </div>
    <div class="signature">
        Dibuat Oleh
        <div class="line"></div>
    </div>
</div>

</body>
</html>
