<?php
require 'koneksi.php';
session_start();

/* ==========================
   CSRF TOKEN
========================== */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ==========================
   GENERATE KODE SURAT
========================== */
function generateKodeSurat($mysqli) {
    $tahun = date('Y');
    $q = $mysqli->query("
        SELECT COUNT(*) total 
        FROM trx_surat_jalan 
        WHERE YEAR(tanggal)='$tahun'
    ");
    $n = ($q->fetch_assoc()['total'] ?? 0) + 1;
    return 'SJ-'.$tahun.'-'.str_pad($n,4,'0',STR_PAD_LEFT);
}

/* ==========================
   SIMPAN DATA
========================== */
if (isset($_POST['simpan'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $tanggal   = $_POST['tanggal'];
    $id_driver = (int)$_POST['id_driver'];
    $id_gudang = (int)$_POST['id_gudang'];
    $ket       = trim($_POST['keterangan']);

    if ($tanggal=='' || $id_driver<=0 || $id_gudang<=0) {
        die("❌ Data tidak lengkap");
    }

    $kode = generateKodeSurat($mysqli);

    $stmt = $mysqli->prepare("
        INSERT INTO trx_surat_jalan
        (kode_surat,tanggal,id_driver,id_gudang,keterangan)
        VALUES (?,?,?,?,?)
    ");
    $stmt->bind_param("ssiis",$kode,$tanggal,$id_driver,$id_gudang,$ket);
    $stmt->execute();
    $stmt->close();

    header("Location: entry_surat_jalan.php?ok=1");
    exit;
}

/* ==========================
   DATA MASTER
========================== */
$driver = $mysqli->query("SELECT * FROM master_driver ORDER BY nama_driver");
$gudang = $mysqli->query("SELECT * FROM master_gudang ORDER BY nama_gudang");

/* ==========================
   LIST SURAT JALAN
========================== */
$list = $mysqli->query("
    SELECT
        sj.id_surat,
        sj.kode_surat,
        sj.tanggal,
        d.nama_driver,
        g.nama_gudang,
        sj.keterangan
    FROM trx_surat_jalan sj
    JOIN master_driver d ON sj.id_driver=d.id_driver
    JOIN master_gudang g ON sj.id_gudang=g.id_gudang
    ORDER BY sj.id_surat DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Surat Jalan</title>
<style>
body{font-family:Arial;background:#f3f4f6;padding:30px}
.card{background:#fff;padding:25px;max-width:1000px;margin:auto;border-radius:12px}
input,select,textarea,button{width:100%;padding:10px;margin-top:8px;border-radius:6px;border:1px solid #d1d5db}
button{background:#1d4ed8;color:#fff;font-weight:bold;border:none}
table{width:100%;border-collapse:collapse;margin-top:25px}
th,td{border-bottom:1px solid #e5e7eb;padding:10px;text-align:left}
th{background:#f9fafb}
.badge{background:#e0f2fe;padding:4px 8px;border-radius:6px}
.btn{padding:6px 10px;border-radius:6px;color:#fff;text-decoration:none;font-size:13px}
.btn-print{background:#16a34a}
.success{background:#dcfce7;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center}
</style>
</head>

<body>
<div class="card">
<a href="index.php">⬅ Kembali</a>
<h2 align="center">🚚 Entry Surat Jalan</h2>

<?php if(isset($_GET['ok'])): ?>
<div class="success">✔ Surat Jalan berhasil disimpan</div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

<label>Tanggal</label>
<input type="date" name="tanggal" required>

<label>Driver</label>
<select name="id_driver" required>
<option value="">-- Pilih Driver --</option>
<?php while($d=$driver->fetch_assoc()): ?>
<option value="<?= $d['id_driver'] ?>">
<?= $d['kode_driver'] ?> | <?= $d['nama_driver'] ?>
</option>
<?php endwhile; ?>
</select>

<label>Gudang Tujuan</label>
<select name="id_gudang" required>
<option value="">-- Pilih Gudang --</option>
<?php while($g=$gudang->fetch_assoc()): ?>
<option value="<?= $g['id_gudang'] ?>">
<?= $g['kode_gudang'] ?> | <?= $g['nama_gudang'] ?>
</option>
<?php endwhile; ?>
</select>

<label>Keterangan</label>
<textarea name="keterangan" rows="3"></textarea>

<button name="simpan">💾 Tambah Surat Jalan</button>
</form>

<hr>

<h3 align="center">📋 Daftar Surat Jalan</h3>
<table>
<thead>
<tr>
    <th>No</th>
    <th>Kode</th>
    <th>Tanggal</th>
    <th>Driver</th>
    <th>Gudang</th>
    <th>Keterangan</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php $no=1; while($r=$list->fetch_assoc()): ?>
<tr>
    <td><?= $no++ ?></td>
    <td><span class="badge"><?= $r['kode_surat'] ?></span></td>
    <td><?= $r['tanggal'] ?></td>
    <td><?= $r['nama_driver'] ?></td>
    <td><?= $r['nama_gudang'] ?></td>
    <td><?= $r['keterangan'] ?></td>
    <td>
        <a class="btn btn-print" target="_blank"
           href="cetak_surat_jalan.php?id=<?= $r['id_surat'] ?>">
           🖨 Cetak
        </a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</body>
</html>
