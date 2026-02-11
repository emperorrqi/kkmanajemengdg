<?php
require 'koneksi.php';

/* ==========================
   VALIDASI
========================== */
$kode = $_GET['kode'] ?? '';
if ($kode == '') die('Kode tidak valid');

/* ==========================
   AMBIL DATA
========================== */
$stmt = $mysqli->prepare("
    SELECT 
        b.kode_basterima,
        b.penerima,
        b.sn_perangkat,
        br.nama_barang,
        br.keterangan
    FROM trx_berita_serah_terima b
    JOIN master_barang_elektronik br 
        ON b.id_barang = br.id_barang
    WHERE b.kode_basterima = ?
");
$stmt->bind_param("s", $kode);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) die('Data tidak ditemukan');

/* ==========================
   OLAH SN JADI ARRAY
========================== */
$sn_list = preg_split("/[\r\n,]+/", $data['sn_perangkat']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Checklist Perangkat <?= htmlspecialchars($kode) ?></title>

<style>
body{
    font-family:"Times New Roman", serif;
    font-size:12pt;
    margin:30px;
    color:#000;
}
.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:8px;
}
.header img{
    width:80px;
}
.header .title{
    flex:1;
    text-align:center;
}
.header .title h2{
    margin:0;
    font-size:14pt;
    text-decoration:underline;
}
.header .title h3{
    margin:3px 0 0;
    font-size:12pt;
    font-weight:normal;
}
hr{
    border:0;
    border-top:1px solid #000;
    margin:10px 0 15px;
}
.info{
    margin-bottom:12px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    border:1px solid #000;
    padding:6px;
    text-align:center;
}
th{
    background:#f5f5f5;
}
.ttd{
    margin-top:60px;
}
.ttd td{
    text-align:center;
}
button{
    margin-bottom:15px;
    padding:6px 14px;
}
@media print{
    button{display:none}
    body{margin:25px}
}
</style>
</head>

<body>

<button onclick="window.print()">🖨 Cetak</button>

<!-- HEADER -->
<div class="header">
    <img src="logo1.png" alt="Logo Kiri">

    <div class="title">
        <h2>BERITA ACARA SERAH TERIMA</h2>
        <h3>Form Check List Perangkat</h3>
    </div>

    <img src="logo2.png" alt="Logo Kanan">
</div>

<hr>

<div class="info">
    <strong>Nomor BAST</strong> : <?= htmlspecialchars($data['kode_basterima']) ?><br>
    <strong>Penerima</strong> : <?= htmlspecialchars($data['penerima']) ?><br>
    <strong>Jenis Perangkat</strong> : <?= htmlspecialchars($data['nama_barang']) ?>
</div>

<table>
<thead>
<tr>
    <th width="5%">No</th>
    <th width="25%">SN Perangkat</th>
    <th width="30%">Model</th>
    <th width="40%">Keterangan</th>
</tr>
</thead>
<tbody>
<?php
$no = 1;
foreach ($sn_list as $sn):
    $sn = trim($sn);
    if ($sn == '') continue;
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($sn) ?></td>
    <td><?= htmlspecialchars($data['nama_barang']) ?></td>
    <td>
        <?= $data['keterangan'] ? ' - '.htmlspecialchars($data['keterangan']) : '' ?>
    </td>
</tr>
<?php endforeach; ?>

<?php if ($no == 1): ?>
<tr>
    <td colspan="4">Tidak ada data perangkat</td>
</tr>
<?php endif; ?>
</tbody>
</table>

<table class="ttd">
<tr>
    <td width="50%">
        PIHAK PERTAMA<br><br><br><br>
        <b>( ____________________ )</b>
    </td>
    <td width="50%">
        PIHAK KEDUA<br><br><br><br>
        <b>( ____________________ )</b>
    </td>
</tr>
</table>

</body>
</html>
