<?php
require 'koneksi.php';

/* ==========================
   FILTER INPUT
========================== */
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$kode      = trim($_GET['kode'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($tgl_awal && $tgl_akhir) {
    $where[]  = "sj.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_awal;
    $params[] = $tgl_akhir;
    $types   .= 'ss';
}

if ($kode !== '') {
    $where[]  = "sj.kode_surat LIKE ?";
    $params[] = "%$kode%";
    $types   .= 's';
}

$whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

$sql = "
    SELECT
        sj.id_surat,
        sj.kode_surat,
        sj.tanggal,
        d.nama_driver,
        g.kode_gudang,
        g.nama_gudang,
        sj.keterangan
    FROM trx_surat_jalan sj
    JOIN master_driver d ON sj.id_driver=d.id_driver
    JOIN master_gudang g ON sj.id_gudang=g.id_gudang
    $whereSql
    ORDER BY sj.tanggal DESC, sj.kode_surat DESC
";

$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$data = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Surat Jalan</title>

<style>
body{
    font-family:Inter,Arial;
    background:#f1f5f9;
    padding:30px;
    color:#0f172a
}
.container{max-width:1200px;margin:auto}
.card{
    background:#fff;
    padding:28px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.08)
}
h2{text-align:center;margin-bottom:5px}
.subtitle{text-align:center;font-size:13px;color:#64748b;margin-bottom:20px}

/* FILTER */
.filter{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
    margin-bottom:15px
}
.filter input,.filter button{
    padding:12px;
    border-radius:10px;
    border:1px solid #d1d5db
}
.filter button{
    background:#2563eb;
    color:#fff;
    border:none;
    font-weight:600;
    cursor:pointer
}

/* BUTTON */
.action{
    text-align:right;
    margin-bottom:15px
}
.btn-print{
    background:#16a34a;
    color:#fff;
    padding:10px 14px;
    border-radius:10px;
    text-decoration:none;
    font-size:14px;
    border:none;
    cursor:pointer
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px
}
th,td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    text-align:center
}
th{
    background:#f8fafc;
    font-weight:600
}
.left{text-align:left}
.badge{
    background:#e0f2fe;
    color:#0369a1;
    padding:4px 8px;
    border-radius:6px;
    font-size:13px
}

/* PRINT */
.print-only{display:none}

@media print{
    body{
        background:#fff;
        padding:0;
        font-size:12px
    }
    .filter,.action,.btn-print{display:none}
    .card{
        box-shadow:none;
        border-radius:0;
        padding:0
    }
    .print-only{
        display:block;
        text-align:center;
        margin-bottom:10px
    }
}
</style>
</head>

<body>
<div class="container">
<div class="card">

<h2>📊 LAPORAN SURAT JALAN</h2>
<div class="subtitle">Sistem Manajemen Gudang</div>

<div class="print-only">
    <strong>PERIODE LAPORAN</strong><br>
    <?= $tgl_awal ?: '-' ?> s/d <?= $tgl_akhir ?: '-' ?>
</div>

<!-- FILTER -->
<form method="get" class="filter">
    <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">
    <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">
    <input type="text" name="kode" placeholder="Kode Surat Jalan" value="<?= htmlspecialchars($kode) ?>">
    <button type="submit">🔍 Tampilkan</button>
</form>

<!-- ACTION -->
<div class="action">
    <button onclick="window.print()" class="btn-print">🖨 Cetak Laporan</button>
</div>

<!-- TABLE -->
<table>
<thead>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Kode Surat</th>
    <th>Driver</th>
    <th>Gudang Tujuan</th>
    <th>Keterangan</th>
    <th class="no-print">Aksi</th>
</tr>
</thead>
<tbody>

<?php if ($data->num_rows == 0): ?>
<tr>
    <td colspan="7">Data tidak ditemukan</td>
</tr>
<?php endif; ?>

<?php $no=1; while($r=$data->fetch_assoc()): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $r['tanggal'] ?></td>
    <td><span class="badge"><?= $r['kode_surat'] ?></span></td>
    <td><?= htmlspecialchars($r['nama_driver']) ?></td>
    <td><?= $r['kode_gudang'].' - '.$r['nama_gudang'] ?></td>
    <td class="left"><?= htmlspecialchars($r['keterangan']) ?></td>
    <td>
        <a class="btn-print" target="_blank"
           href="cetak_surat_jalan.php?id=<?= $r['id_surat'] ?>">
           🖨 Print
        </a>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>
</div>
</body>
</html>
