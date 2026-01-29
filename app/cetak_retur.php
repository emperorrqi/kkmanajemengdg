<?php
require 'koneksi.php';

/* ==========================
   VALIDASI
========================== */
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID tidak valid");

/* ==========================
   AMBIL DATA RETUR
========================== */
$stmt = $mysqli->prepare("
    SELECT
        r.kode_retur,
        r.tanggal,
        r.jumlah,
        r.alasan,
        b.nama_barang
    FROM trx_retur r
    JOIN master_barang_elektronik b ON r.id_barang = b.id_barang
    WHERE r.id_retur = ?
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
<title>Cetak Retur Barang</title>

<style>
@page{
    size:A4;
    margin:2.5cm 2cm;
}
body{
    font-family:"Times New Roman", serif;
    font-size:12pt;
    color:#000;
}
button{
    padding:8px 16px;
    margin-bottom:20px;
}
@media print{
    button{display:none}
}

/* HEADER */
.header{
    display:grid;
    grid-template-columns:120px auto 120px;
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
    text-transform:uppercase;
}
.title p{
    margin-top:4px;
    font-size:11pt;
}

/* LINE */
.hr{
    border-top:2px solid #000;
    margin:12px 0 20px;
}

/* INFO */
table{
    width:100%;
}
td{
    padding:6px 4px;
    vertical-align:top;
}
.label{
    width:30%;
}

/* KETERANGAN */
.box{
    border:1px solid #000;
    padding:12px;
    min-height:70px;
    margin-top:6px;
}

/* TTD */
.ttd{
    margin-top:90px;
}
.ttd td{
    text-align:center;
}
.nama{
    margin-top:70px;
    font-weight:bold;
}

/* FOOTER */
.footer{
    position:fixed;
    bottom:1.5cm;
    left:2cm;
    right:2cm;
    font-size:9pt;
    text-align:center;
}
</style>
</head>

<body>

<button onclick="window.print()">🖨 Cetak Retur</button>

<!-- HEADER -->
<div class="header">
    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>

    <div class="title">
        <h1>RETUR BARANG</h1>
        <p>Dokumen Retur Barang</p>
    </div>

    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>
</div>

<div class="hr"></div>

<!-- INFO -->
<table>
<tr>
    <td class="label">Kode Retur</td>
    <td>: <?= htmlspecialchars($data['kode_retur']) ?></td>
</tr>
<tr>
    <td class="label">Tanggal</td>
    <td>: <?= date('d F Y', strtotime($data['tanggal'])) ?></td>
</tr>
<tr>
    <td class="label">Nama Barang</td>
    <td>: <?= htmlspecialchars($data['nama_barang']) ?></td>
</tr>
<tr>
    <td class="label">Jumlah Retur</td>
    <td>: <?= (int)$data['jumlah'] ?></td>
</tr>
</table>

<!-- ALASAN -->
<strong>Alasan Retur:</strong>
<div class="box">
<?= nl2br(htmlspecialchars($data['alasan'])) ?>
</div>

<!-- TTD -->
<table class="ttd">
<tr>
    <td width="50%">
        Petugas Gudang
        <div class="nama">( ____________________ )</div>
    </td>
    <td width="50%">
        Penyerah
        <div class="nama">( ____________________ )</div>
    </td>
</tr>
</table>

<div class="footer">
    Dicetak otomatis oleh sistem • <?= date('d/m/Y H:i') ?>
</div>

</body>
</html>
