<?php
require 'koneksi.php';

/* ==========================
   VALIDASI PARAMETER
========================== */
$kode = $_GET['kode'] ?? '';
if ($kode == '') {
    die('Kode BAST tidak valid');
}

/* ==========================
   AMBIL DATA
========================== */
$stmt = $mysqli->prepare("
    SELECT 
        b.kode_basterima,
        b.tanggal,
        b.penerima,
        b.jumlah,
        b.sn_perangkat,
        br.nama_barang
    FROM trx_berita_serah_terima b
    JOIN master_barang_elektronik br 
        ON b.id_barang = br.id_barang
    WHERE b.kode_basterima = ?
");
$stmt->bind_param("s", $kode);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die('Data tidak ditemukan');
}

/* ==========================
   FORMAT TANGGAL
========================== */
function tglIndo($tgl)
{
    $bulan = [
        1=>'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];
    $x = explode('-', $tgl);
    return $x[2].' '.$bulan[(int)$x[1]].' '.$x[0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>BAST <?= htmlspecialchars($data['kode_basterima']) ?></title>

<style>
@page{
    size:A4;
    margin:2cm;
}
body{
    font-family:"Times New Roman", serif;
    font-size:12pt;
    line-height:1.5;
    color:#000;
}
button{
    margin-bottom:12px;
    padding:6px 14px;
}
.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:6px;
}
.header img{
    width:75px;
}
.judul{
    text-align:center;
    flex:1;
}
.judul h2{
    margin:0;
    font-size:16pt;
    text-decoration:underline;
}
.center{text-align:center;}
table{
    width:100%;
    border-collapse:collapse;
}
td{
    padding:2px 0;
    vertical-align:top;
}
.ttd td{
    padding-top:55px;
    text-align:center;
}
@media print{
    button{display:none;}
}
</style>
</head>

<body>

<button onclick="window.print()">🖨 Cetak</button>

<!-- HEADER -->
<div class="header">
    <img src="logo.png1" alt="Logo Kiri">

    <div class="judul">
        <h2>BERITA ACARA SERAH TERIMA</h2>
        <div>Nomor : <?= htmlspecialchars($data['kode_basterima']) ?></div>
    </div>

    <img src="logo.png2" alt="Logo Kanan">
</div>

<p>
Pada hari ini <b><?= tglIndo($data['tanggal']) ?></b>, kami yang bertanda tangan di bawah ini:
</p>

<table>
<tr>
    <td width="25%">Nama</td>
    <td>: <b>(Nama Petugas)</b></td>
</tr>
<tr>
    <td>Jabatan</td>
    <td>: (Jabatan)</td>
</tr>
<tr>
    <td>Berkedudukan</td>
    <td>: Pihak yang menyerahkan</td>
</tr>
</table>

<p>Selanjutnya disebut <b>PIHAK PERTAMA</b></p>

<table>
<tr>
    <td width="25%">Nama</td>
    <td>: <b><?= htmlspecialchars($data['penerima']) ?></b></td>
</tr>
<tr>
    <td>Jabatan</td>
    <td>: (Jabatan)</td>
</tr>
<tr>
    <td>Berkedudukan</td>
    <td>: Pihak yang menerima</td>
</tr>
</table>

<p>Selanjutnya disebut <b>PIHAK KEDUA</b></p>

<p>
PIHAK PERTAMA telah menyerahkan kepada PIHAK KEDUA berupa:
</p>

<table>
<tr>
    <td width="25%">Perangkat</td>
    <td>: <?= htmlspecialchars($data['nama_barang']) ?></td>
</tr>
<tr>
    <td>Jumlah</td>
    <td>: <?= (int)$data['jumlah'] ?> Unit</td>
</tr>
<tr>
    <td>Keterangan</td>
    <td>: <?= nl2br(htmlspecialchars($data['sn_perangkat'])) ?></td>
</tr>
</table>

<p>
Demikian Berita Acara Serah Terima ini dibuat dengan sebenarnya.
</p>

<table class="ttd">
<tr>
    <td width="50%">
        PIHAK PERTAMA<br><br>
        <b>( ____________________ )</b>
    </td>
    <td width="50%">
        PIHAK KEDUA<br><br>
        <b><?= htmlspecialchars($data['penerima']) ?></b>
    </td>
</tr>
</table>

</body>
</html>
