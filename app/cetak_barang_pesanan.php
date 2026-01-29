<?php
require 'koneksi.php';

$sto = $_GET['sto'] ?? '';
if (!$sto) {
    die('STO Number tidak valid');
}

/* ===============================
   HEADER STO
================================ */
$h = $mysqli->prepare("
    SELECT
        p.sto_number,
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
    WHERE p.sto_number = ?
    LIMIT 1
");
$h->bind_param("s", $sto);
$h->execute();
$header = $h->get_result()->fetch_assoc();
$h->close();

if (!$header) {
    die("Data STO tidak ditemukan");
}

/* ===============================
   DETAIL STO
================================ */
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
    WHERE p.sto_number = ?
    ORDER BY p.sto_item
");
$d->bind_param("s", $sto);
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

/* HEADER */
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
    letter-spacing:1px;
}
.company p{
    margin:2px 0;
    font-size:11px;
}

/* TITLE */
.doc-title{
    display:flex;
    justify-content:space-between;
    font-weight:bold;
    margin-bottom:6px;
}

/* TABLE */
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
.batch{font-size:11px;letter-spacing:.5px}

/* FOOTER */
.sign-table td{
    padding-top:45px;
    text-align:center;
}
hr{
    border:1px solid #000;
    margin:10px 0;
}
</style>
</head>

<body onload="window.print()">

<!-- HEADER PERUSAHAAN -->
<div class="header">
    <img src="logo.png" class="logo" alt="Logo">
    <div class="company">
        <h1>PT MAJU JAYA SEJAHTERA</h1>
        <p>Jl. Industri No. 123, Jakarta</p>
        <p>Telp. (021) 555-1234</p>
    </div>
</div>

<!-- JUDUL -->
<div class="doc-title">
    <div>G R / G I – S L I P</div>
    <div>No: <?= htmlspecialchars($header['sto_number']) ?></div>
</div>

<!-- INFO HEADER -->
<table>
<tr>
<td width="50%">
Posting Date  : <?= date('d.m.Y', strtotime($header['posting_date'])) ?><br>
Entry Date    : <?= date('d.m.Y', strtotime($header['entry_date'])) ?><br>
Document Date : <?= date('d.m.Y', strtotime($header['document_date'])) ?><br>
<strong>Batch :</strong> <?= htmlspecialchars($header['batch']) ?>
</td>
<td width="50%" class="right">
Plant         : <?= htmlspecialchars($header['kode_gudang']) ?><br>
Description   : <?= htmlspecialchars($header['nama_gudang']) ?><br>
Movement Type : <?= htmlspecialchars($header['mvt_type']) ?>
</td>
</tr>
</table>

<hr>

<!-- DETAIL BARANG -->
<table>
<tr>
<th width="6%">Itm</th>
<th width="12%">Material</th>
<th>Description</th>
<th width="10%">Batch</th>
<th width="18%">SLoc / SBU</th>
<th width="10%" class="right">Qty</th>
<th width="22%">Remarks</th>
</tr>

<?php while($r = $detail->fetch_assoc()): ?>
<tr>
<td class="center"><?= htmlspecialchars($r['sto_item']) ?></td>
<td><?= htmlspecialchars($r['kode_barang']) ?></td>
<td><?= htmlspecialchars($r['nama_barang']) ?></td>
<td class="batch"><?= htmlspecialchars($r['batch'] ?: '-') ?></td>
<td><?= htmlspecialchars($r['kode_sbu'].' / '.$r['location']) ?></td>
<td class="right"><?= number_format($r['jumlah'],0) ?></td>
<td class="small"><?= nl2br(htmlspecialchars($r['serial_number'])) ?></td>
</tr>
<?php endwhile; ?>
</table>

<hr>

<!-- TANDA TANGAN -->
<table class="sign-table">
<tr>
<td width="33%">
Issued By<br><br>
( <?= htmlspecialchars($header['nama_admin']) ?> )
</td>
<td width="33%">
Received By<br><br>
( __________________ )
</td>
<td width="33%">
Approved By<br><br>
( __________________ )
</td>
</tr>
</table>

</body>
</html>
