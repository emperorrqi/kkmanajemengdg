<?php
require 'koneksi.php';
session_start();

/* =====================================================
   SIMPAN BARANG PESANAN (GOODS ISSUE / STO)
===================================================== */
if (isset($_POST['simpan'])) {

    $posting_date  = $_POST['posting_date'] ?? '';
    $batch         = trim($_POST['batch'] ?? '');
    $id_admin      = (int)($_POST['id_admin'] ?? 0);
    $id_barang     = (int)($_POST['id_barang'] ?? 0);
    $id_sbu        = (int)($_POST['id_sbu'] ?? 0);
    $id_gudang     = (int)($_POST['id_gudang'] ?? 0);
    $jumlah        = (int)($_POST['jumlah'] ?? 0);
    $serial_number = trim($_POST['serial_number'] ?? '');

    if (
        !$posting_date || !$batch || !$id_admin ||
        !$id_barang || !$id_sbu || !$id_gudang || $jumlah <= 0
    ) {
        $_SESSION['error'] = "Data wajib belum lengkap";
        header("Location: entry_barang_pesanan.php");
        exit;
    }

    if (!DateTime::createFromFormat('Y-m-d', $posting_date)) {
        $_SESSION['error'] = "Format tanggal tidak valid";
        header("Location: entry_barang_pesanan.php");
        exit;
    }

    try {
        $mysqli->begin_transaction();

        /* ===============================
           CEK STOK TERSEDIA (LOCK)
        =============================== */
        $cek = $mysqli->prepare("
            SELECT 
                IFNULL(SUM(m.jumlah),0) -
                IFNULL((
                    SELECT SUM(p.jumlah)
                    FROM trx_barang_pesanan p
                    WHERE p.id_barang = ?
                ),0) AS stok
            FROM trx_persediaan_barang m
            WHERE m.id_barang = ?
            FOR UPDATE
        ");
        $cek->bind_param("ii", $id_barang, $id_barang);
        $cek->execute();
        $stok = $cek->get_result()->fetch_assoc();
        $cek->close();

        if ($jumlah > $stok['stok']) {
            throw new Exception("Stok tidak mencukupi. Sisa stok: ".$stok['stok']);
        }

        /* ===============================
           INSERT BARANG PESANAN
        =============================== */
        $stmt = $mysqli->prepare("
            INSERT INTO trx_barang_pesanan
            (
                posting_date,
                entry_date,
                document_date,
                batch,
                mvt_type,
                recipient,
                issued_by,
                id_admin,
                id_barang,
                id_sbu,
                id_gudang,
                jumlah,
                serial_number
            ) VALUES (
                ?, CURDATE(), CURDATE(),
                ?, '201', '-', '-',
                ?, ?, ?, ?,
                ?, ?
            )
        ");

        $stmt->bind_param(
            "ssiiiiis",
            $posting_date,
            $batch,
            $id_admin,
            $id_barang,
            $id_sbu,
            $id_gudang,
            $jumlah,
            $serial_number
        );

        $stmt->execute();
        $stmt->close();

        $mysqli->commit();
        $_SESSION['success'] = "Barang pesanan berhasil disimpan";

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: entry_barang_pesanan.php");
    exit;
}

/* =====================================================
   DROPDOWN DATA
===================================================== */
$barang = $mysqli->query("
    SELECT 
        b.id_barang,
        b.nama_barang,
        IFNULL(SUM(m.jumlah),0) -
        IFNULL((
            SELECT SUM(p.jumlah)
            FROM trx_barang_pesanan p
            WHERE p.id_barang = b.id_barang
        ),0) AS stok
    FROM master_barang_elektronik b
    LEFT JOIN trx_persediaan_barang m ON b.id_barang = m.id_barang
    GROUP BY b.id_barang, b.nama_barang
    HAVING stok > 0
");

$admin  = $mysqli->query("SELECT id_admin, nama_admin FROM master_administrasi ORDER BY nama_admin");
$sbu    = $mysqli->query("SELECT id_sbu, kode_sbu, location FROM master_sbu ORDER BY kode_sbu");
$gudang = $mysqli->query("SELECT id_gudang, kode_gudang, nama_gudang FROM master_gudang ORDER BY kode_gudang");

/* =====================================================
   LIST BARANG PESANAN
===================================================== */
$list = $mysqli->query("
    SELECT
        p.posting_date,
        p.sto_number,
        p.sto_item,
        p.batch,
        a.nama_admin,
        b.nama_barang,
        s.kode_sbu,
        s.location,
        g.nama_gudang,
        p.jumlah,
        p.serial_number
    FROM trx_barang_pesanan p
    JOIN master_administrasi a ON p.id_admin = a.id_admin
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    JOIN master_sbu s ON p.id_sbu = s.id_sbu
    JOIN master_gudang g ON p.id_gudang = g.id_gudang
    ORDER BY p.id_pesanan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Barang Pesanan</title>
<style>
body{font-family:Inter,Arial;background:#f3f4f6;padding:30px}
.card{background:#fff;padding:25px;border-radius:12px;max-width:1100px;margin:auto;box-shadow:0 6px 20px rgba(0,0,0,.08)}
input,select,textarea,button{width:100%;padding:12px;margin-top:8px;border-radius:8px;border:1px solid #d1d5db}
button{background:#dc2626;color:#fff;border:none;font-weight:600;cursor:pointer}
.success{background:#dcfce7;padding:10px;border-radius:8px;margin-bottom:15px;text-align:center}
.error{background:#fee2e2;padding:10px;border-radius:8px;margin-bottom:15px;text-align:center}
table{width:100%;border-collapse:collapse;margin-top:30px}
th,td{padding:10px;border-bottom:1px solid #e5e7eb;text-align:center}
th{background:#f9fafb}
.cetak{padding:6px 10px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none;font-size:13px}
</style>
</head>
<body>

<div class="card">
<a href="index.php">⬅ Kembali</a>
<h3>📤 Entry Barang Pesanan (Goods Issue / STO)</h3>

<?php if (!empty($_SESSION['success'])): ?>
<div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
<div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="post">

<label>Posting Date</label>
<input type="date" name="posting_date" required>

<label>Batch</label>
<input type="text" name="batch" placeholder="BATCH-<?= date('Ym') ?>" required>

<label>Admin</label>
<select name="id_admin" required>
<option value="">-- Pilih Admin --</option>
<?php while($a=$admin->fetch_assoc()): ?>
<option value="<?= $a['id_admin'] ?>"><?= htmlspecialchars($a['nama_admin']) ?></option>
<?php endwhile; ?>
</select>

<label>Gudang</label>
<select name="id_gudang" required>
<option value="">-- Pilih Gudang --</option>
<?php while($g=$gudang->fetch_assoc()): ?>
<option value="<?= $g['id_gudang'] ?>">
<?= $g['kode_gudang'].' - '.$g['nama_gudang'] ?>
</option>
<?php endwhile; ?>
</select>

<label>Barang (stok tersedia)</label>
<select name="id_barang" required>
<option value="">-- Pilih Barang --</option>
<?php while($b=$barang->fetch_assoc()): ?>
<option value="<?= $b['id_barang'] ?>">
<?= htmlspecialchars($b['nama_barang']) ?> | Stok: <?= $b['stok'] ?>
</option>
<?php endwhile; ?>
</select>

<label>SBU</label>
<select name="id_sbu" required>
<option value="">-- Pilih SBU --</option>
<?php while($s=$sbu->fetch_assoc()): ?>
<option value="<?= $s['id_sbu'] ?>">
<?= $s['kode_sbu'].' - '.$s['location'] ?>
</option>
<?php endwhile; ?>
</select>

<label>Qty</label>
<input type="number" name="jumlah" min="1" required>

<label>Serial Number / Keterangan</label>
<textarea name="serial_number" rows="3"></textarea>

<button name="simpan">💾 Tambah Barang Pesanan</button>
</form>

<hr style="margin:40px 0">

<h3>📋 Daftar Barang Pesanan</h3>
<table>
<tr>
<th>Tanggal</th>
<th>STO</th>
<th>Item</th>
<th>Batch</th>
<th>Admin</th>
<th>Barang</th>
<th>SBU</th>
<th>Gudang</th>
<th>Qty</th>
<th>SN</th>
<th>Aksi</th>
</tr>
<?php while($r=$list->fetch_assoc()): ?>
<tr>
<td><?= $r['posting_date'] ?></td>
<td><?= $r['sto_number'] ?></td>
<td><?= $r['sto_item'] ?></td>
<td><?= htmlspecialchars($r['batch']) ?></td>
<td><?= htmlspecialchars($r['nama_admin']) ?></td>
<td><?= htmlspecialchars($r['nama_barang']) ?></td>
<td><?= $r['kode_sbu'].' - '.$r['location'] ?></td>
<td><?= htmlspecialchars($r['nama_gudang']) ?></td>
<td><?= $r['jumlah'] ?></td>
<td><?= htmlspecialchars($r['serial_number']) ?></td>
<td>
<a class="cetak"
   href="cetak_barang_pesanan.php?sto=<?= urlencode($r['sto_number']) ?>"
   target="_blank">🖨 print</a>
</td>
</tr>
<?php endwhile; ?>
</table>

</div>
</body>
</html>
