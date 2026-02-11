<?php
require 'koneksi.php';

/* ==========================
   VALIDASI
========================== */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID tidak valid");

/* ==========================
   AMBIL DATA
========================== */
$stmt = $mysqli->prepare("
    SELECT
        sj.kode_surat,
        sj.tanggal,
        sj.keterangan,
        d.nama_driver,
        g.nama_gudang
    FROM trx_surat_jalan sj
    JOIN master_driver d ON sj.id_driver = d.id_driver
    JOIN master_gudang g ON sj.id_gudang = g.id_gudang
    WHERE sj.id_surat = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) die("Data tidak ditemukan");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Surat Jalan</title>

<style>
/* ==========================
   PAGE SETUP
========================== */
@page{
    size: A4;
    margin: 2.5cm 2cm;
}
body{
    font-family: "Times New Roman", serif;
    font-size: 12pt;
    color:#000;
}
button{
    padding:8px 16px;
    margin-bottom:20px;
    font-size:12px;
}
@media print{
    button{display:none}
}

/* ==========================
   HEADER
========================== */
.header{
    display:grid;
    grid-template-columns: 120px auto 120px;
    align-items:center;
    margin-bottom:10px;
}
.logo{
    text-align:center;
}
.logo img{
    max-height:70px;
}
.title{
    text-align:center;
}
.title h1{
    margin:0;
    font-size:16pt;
    letter-spacing:1px;
    text-transform:uppercase;
}
.title p{
    margin:4px 0 0;
    font-size:11pt;
}

/* ==========================
   DIVIDER
========================== */
.hr{
    border-top:2px solid #000;
    margin:12px 0 20px;
}

/* ==========================
   INFO TABLE
========================== */
.info{
    width:100%;
    margin-bottom:18px;
}
.info td{
    padding:6px 4px;
    vertical-align:top;
}
.info .label{
    width:30%;
}

/* ==========================
   KETERANGAN
========================== */
.keterangan{
    border:1px solid #000;
    padding:12px;
    min-height:70px;
    margin-top:6px;
}

/* ==========================
   TTD
========================== */
.ttd{
    width:100%;
    margin-top:90px;
}
.ttd td{
    text-align:center;
}
.ttd .nama{
    margin-top:70px;
    font-weight:bold;
}

/* ==========================
   FOOTER
========================== */
.footer{
    position:fixed;
    bottom:1.5cm;
    left:2cm;
    right:2cm;
    font-size:9pt;
    text-align:center;
    color:#333;
}
</style>
</head>

<body>

<button onclick="window.print()">🖨 Cetak Surat Jalan</button>

<!-- ==========================
     HEADER
========================== -->
<div class="header">
    <div class="logo">
        <img src="logo1.png" alt="Logo Kiri">
    </div>

    <div class="title">
        <h1>SURAT JALAN</h1>
        <p>Dokumen Pengiriman Barang</p>
    </div>

    <div class="logo">
        <img src="logo2.png" alt="Logo Kanan">
    </div>
</div>

<div class="hr"></div>

<!-- ==========================
     INFORMASI
========================== -->
<table class="info">
<tr>
    <td class="label">Nomor Surat</td>
    <td>: <?= htmlspecialchars($data['kode_surat']) ?></td>
</tr>
<tr>
    <td class="label">Tanggal</td>
    <td>: <?= date('d F Y', strtotime($data['tanggal'])) ?></td>
</tr>
<tr>
    <td class="label">Nama Driver</td>
    <td>: <?= htmlspecialchars($data['nama_driver']) ?></td>
</tr>
<tr>
    <td class="label">Gudang Tujuan</td>
    <td>: <?= htmlspecialchars($data['nama_gudang']) ?></td>
</tr>
</table>

<!-- ==========================
     KETERANGAN
========================== -->
<strong>Keterangan Pengiriman:</strong>
<div class="keterangan">
<?= nl2br(htmlspecialchars($data['keterangan'])) ?>
</div>

<!-- ==========================
     TANDA TANGAN
========================== -->
<table class="ttd">
<tr>
    <td width="50%">
        Driver
        <div class="nama"><?= htmlspecialchars($data['nama_driver']) ?></div>
    </td>
    <td width="50%">
        Bagian Gudang
        <div class="nama">( ____________________ )</div>
    </td>
</tr>
</table>

<!-- ==========================
     FOOTER
========================== -->
<div class="footer">
    Dicetak otomatis oleh sistem • <?= date('d/m/Y H:i') ?>
</div>

</body>
</html>
