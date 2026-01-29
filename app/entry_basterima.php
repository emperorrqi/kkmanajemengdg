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
   GENERATE KODE BAST
   BST20260001
========================== */
function generateKodeBAST($mysqli)
{
    $tahun = date('Y');
    $q = $mysqli->query("
        SELECT COUNT(*) total 
        FROM trx_berita_serah_terima
        WHERE YEAR(tanggal) = '$tahun'
    ");
    $n = ($q->fetch_assoc()['total'] ?? 0) + 1;
    return 'BST' . $tahun . str_pad($n, 4, '0', STR_PAD_LEFT);
}

/* ==========================
   SIMPAN DATA
========================== */
if (isset($_POST['simpan'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("❌ CSRF token tidak valid");
    }

    $kode   = generateKodeBAST($mysqli);
    $tgl    = $_POST['tanggal'];
    $id_psn = (int)$_POST['id_pesanan'];
    $pener  = trim($_POST['penerima']);
    $jml    = (int)$_POST['jumlah'];
    $sn     = trim($_POST['sn_perangkat']);

    if ($tgl=='' || $id_psn<=0 || $pener=='' || $jml<=0) {
        die("❌ Data belum lengkap");
    }

    /* Cek sisa pesanan */
    $cek = $mysqli->prepare("
        SELECT 
            p.id_barang,
            p.jumlah,
            IFNULL(SUM(b.jumlah),0) AS sudah
        FROM trx_barang_pesanan p
        LEFT JOIN trx_berita_serah_terima b ON p.id_pesanan=b.id_pesanan
        WHERE p.id_pesanan=?
        GROUP BY p.id_pesanan
    ");
    $cek->bind_param("i", $id_psn);
    $cek->execute();
    $row = $cek->get_result()->fetch_assoc();
    $cek->close();

    if (!$row) die("❌ Pesanan tidak ditemukan");

    $sisa = $row['jumlah'] - $row['sudah'];
    if ($jml > $sisa) die("❌ Jumlah melebihi sisa ($sisa)");

    /* Simpan */
    $ins = $mysqli->prepare("
        INSERT INTO trx_berita_serah_terima
        (kode_basterima,tanggal,id_pesanan,penerima,id_barang,jumlah,sn_perangkat)
        VALUES (?,?,?,?,?,?,?)
    ");
    $ins->bind_param(
        "ssisiis",
        $kode,
        $tgl,
        $id_psn,
        $pener,
        $row['id_barang'],
        $jml,
        $sn
    );
    $ins->execute();
    $ins->close();

    header("Location: entry_basterima.php?ok=1");
    exit;
}

/* ==========================
   DATA PESANAN
========================== */
$pesanan = $mysqli->query("
    SELECT 
        p.id_pesanan,
        p.kode_pesanan,
        b.nama_barang,
        p.jumlah,
        IFNULL(SUM(x.jumlah),0) AS sudah,
        (p.jumlah-IFNULL(SUM(x.jumlah),0)) AS sisa
    FROM trx_barang_pesanan p
    JOIN master_barang_elektronik b ON p.id_barang=b.id_barang
    LEFT JOIN trx_berita_serah_terima x ON x.id_pesanan=p.id_pesanan
    GROUP BY p.id_pesanan
    HAVING sisa>0
    ORDER BY p.id_pesanan DESC
");

/* ==========================
   LIST BAST
========================== */
$list = $mysqli->query("
    SELECT 
        b.kode_basterima,
        b.tanggal,
        p.kode_pesanan,
        br.nama_barang,
        b.penerima,
        b.jumlah,
        b.sn_perangkat
    FROM trx_berita_serah_terima b
    JOIN trx_barang_pesanan p ON b.id_pesanan=p.id_pesanan
    JOIN master_barang_elektronik br ON b.id_barang=br.id_barang
    ORDER BY b.id_serah DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry BAST</title>
<style>
body{font-family:Arial;background:#f3f4f6;padding:30px}
.card{background:#fff;padding:25px;max-width:1150px;margin:auto;border-radius:12px}
input,select,textarea,button{width:100%;padding:12px;margin-top:10px;border-radius:8px;border:1px solid #d1d5db}
button{background:#0f766e;color:#fff;font-weight:bold;border:none}
table{width:100%;border-collapse:collapse;margin-top:25px}
th,td{padding:10px;border-bottom:1px solid #e5e7eb;text-align:center}
th{background:#f9fafb}
.success{background:#dcfce7;padding:10px;border-radius:8px;margin-bottom:15px;text-align:center}
.badge{background:#e0f2fe;color:#0369a1;padding:4px 8px;border-radius:6px}
.btn{padding:6px 10px;border-radius:6px;color:#fff;text-decoration:none;font-size:13px}
.btn-bast{background:#2563eb}
.btn-list{background:#16a34a}
</style>
</head>

<body>
<div class="card">
<a href="index.php">⬅ Kembali</a>
<h2 align="center">📄 Entry Berita Acara Serah Terima</h2>

<?php if(isset($_GET['ok'])): ?>
<div class="success">✔ BAST berhasil disimpan</div>
<?php endif; ?>

<form method="post">
<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

<label>Tanggal</label>
<input type="date" name="tanggal" required>

<label>Pesanan</label>
<select name="id_pesanan" required>
<option value="">-- Pilih Pesanan --</option>
<?php while($p=$pesanan->fetch_assoc()): ?>
<option value="<?= $p['id_pesanan'] ?>">
<?= $p['kode_pesanan'] ?> | <?= $p['nama_barang'] ?> | Sisa: <?= $p['sisa'] ?>
</option>
<?php endwhile; ?>
</select>

<label>Penerima</label>
<input type="text" name="penerima" required>

<label>Jumlah</label>
<input type="number" name="jumlah" min="1" required>

<label>SN perangkat </label>
<textarea name="sn_perangkat" rows="3"></textarea>

<button name="simpan">💾 Tambah BAST</button>
</form>

<hr>

<h3 align="center">📑 Daftar BAST</h3>
<table>
<thead>
<tr>
    <th>Kode</th>
    <th>Tanggal</th>
    <th>Barang</th>
    <th>Penerima</th>
    <th>Jumlah</th>
    <th>SN</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody>
<?php if($list->num_rows==0): ?>
<tr><td colspan="8">Belum ada data</td></tr>
<?php endif; ?>
<?php while($b=$list->fetch_assoc()): ?>
<tr>
    <td><span class="badge"><?= $b['kode_basterima'] ?></span></td>
    <td><?= $b['tanggal'] ?></td>
    <td><?= $b['nama_barang'] ?></td>
    <td><?= $b['penerima'] ?></td>
    <td><?= $b['jumlah'] ?></td>
    <td><?= nl2br(htmlspecialchars($b['sn_perangkat'])) ?></td>
    <td>
        <a target="_blank" class="btn btn-bast"
           href="cetak_basterima.php?kode=<?= urlencode($b['kode_basterima']) ?>">🖨 BAST</a>
        <a target="_blank" class="btn btn-list"
           href="cetak_list_perangkat.php?kode=<?= urlencode($b['kode_basterima']) ?>">📋 List</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</body>
</html>
