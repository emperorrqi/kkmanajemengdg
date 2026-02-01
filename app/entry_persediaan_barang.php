<?php
include 'koneksi.php';
session_start();

/* ===========================
   CSRF TOKEN
=========================== */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ===========================
   SIMPAN BARANG MASUK
=========================== */
if (isset($_POST['simpan'])) {
    // CSRF check
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $tanggal   = $_POST['tanggal'] ?? '';
    $id_po     = (int)($_POST['id_po'] ?? 0);
    $id_admin  = (int)($_POST['id_admin'] ?? 0);
    $jumlah_in = (int)($_POST['jumlah'] ?? 0);

    if ($tanggal=='' || $id_po==0 || $id_admin==0 || $jumlah_in<=0) {
        $_SESSION['error'] = "Data wajib belum lengkap";
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$date || $date->format('Y-m-d') !== $tanggal) {
            $_SESSION['error'] = "Format tanggal tidak valid";
        } else {
            // Ambil data PO
            $q = $mysqli->prepare("
                SELECT p.id_po, p.id_barang, p.id_vendor, p.jumlah AS qty_po,
                       IFNULL(SUM(pb.jumlah),0) AS total_masuk
                FROM trx_purchase_order p
                LEFT JOIN trx_persediaan_barang pb ON p.id_po = pb.id_po
                WHERE p.id_po = ?
                GROUP BY p.id_po
            ");
            $q->bind_param("i", $id_po);
            $q->execute();
            $po = $q->get_result()->fetch_assoc();
            $q->close();

            if (!$po) {
                $_SESSION['error'] = "Data PO tidak ditemukan";
            } else {
                $sisa_po = $po['qty_po'] - $po['total_masuk'];
                if ($jumlah_in > $sisa_po) {
                    $_SESSION['error'] = "Jumlah masuk melebihi sisa PO (Sisa: $sisa_po)";
                } else {
                    // Insert barang masuk
                    $stmt = $mysqli->prepare("
                        INSERT INTO trx_persediaan_barang
                        (tanggal, id_po, id_admin, id_barang, id_vendor, jumlah)
                        VALUES (?,?,?,?,?,?)
                    ");
                    $stmt->bind_param(
                        "siiiii",
                        $tanggal,
                        $id_po,
                        $id_admin,
                        $po['id_barang'],
                        $po['id_vendor'],
                        $jumlah_in
                    );
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Barang berhasil masuk ke persediaan";
                        header("Location: entry_persediaan_barang.php");
                        exit;
                    } else {
                        $_SESSION['error'] = "Gagal menyimpan: ".$mysqli->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

/* ===========================
   DATA MASTER
=========================== */
// Admin
$admin = $mysqli->query("SELECT id_admin, nama_admin FROM master_administrasi ORDER BY nama_admin");

// PO dengan sisa barang
$po = $mysqli->query("
    SELECT p.id_po, p.kode_po, b.nama_barang, p.jumlah AS qty_po,
           IFNULL(SUM(pb.jumlah),0) AS total_masuk
    FROM trx_purchase_order p
    LEFT JOIN trx_persediaan_barang pb ON p.id_po = pb.id_po
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    GROUP BY p.id_po
    HAVING (qty_po - total_masuk) > 0
    ORDER BY p.id_po DESC
");

/* ===========================
   DATA PERSEDIAAN FINAL + STOK AKHIR + TOTAL NILAI
=========================== */
$data = $mysqli->query("
    SELECT 
        p.id_po,
        p.kode_po,
        b.nama_barang,
        v.nama_vendor,
        p.jumlah AS barang_po,
        IFNULL(SUM(pb.jumlah),0) AS barang_masuk,
        IFNULL(SUM(bp.jumlah),0) AS barang_pesanan,
        (IFNULL(SUM(pb.jumlah),0) - IFNULL(SUM(bp.jumlah),0)) AS stok_akhir,
        p.unit_price,
        ((IFNULL(SUM(pb.jumlah),0) - IFNULL(SUM(bp.jumlah),0)) * p.unit_price) AS total_nilai
    FROM trx_purchase_order p
    LEFT JOIN trx_persediaan_barang pb ON p.id_po = pb.id_po
    LEFT JOIN trx_barang_pesanan bp ON p.id_barang = bp.id_barang
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    JOIN master_vendor v ON p.id_vendor = v.id_vendor
    GROUP BY p.id_po
    ORDER BY p.kode_po DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Persediaan Barang</title>
<style>
body{font-family:Inter,Arial;background:#f3f4f6;padding:30px}
.container{max-width:1200px;margin:auto}
.card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.08)}
form{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:30px}
.full{grid-column:1/3}
input,select,button{padding:12px;border-radius:8px;border:1px solid #d1d5db}
button{background:#2563eb;color:#fff;font-weight:600;border:none;cursor:pointer}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:center}
th{background:#f3f4f6}
.alert-success{background:#dcfce7;padding:10px;border-radius:6px;margin-bottom:10px}
.alert-error{background:#fee2e2;padding:10px;border-radius:6px;margin-bottom:10px}
</style>
</head>
<body>

<div class="container">
<div class="card">
    
<a href="index.php">⬅ Kembali</a>
<h2 style="text-align:center;">📥 Entry & Laporan Persediaan Barang</h2>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
<div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- FORM ENTRY -->
<form method="post">
<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

<label>Tanggal Masuk</label>
<input type="date" name="tanggal" required>

<label>Administrasi</label>
<select name="id_admin" required>
    <option value="">-- Pilih Admin --</option>
    <?php while($a=$admin->fetch_assoc()): ?>
        <option value="<?= $a['id_admin'] ?>"><?= htmlspecialchars($a['nama_admin']) ?></option>
    <?php endwhile; ?>
</select>

<label class="full">Purchase Order (Sisa PO)</label>
<select name="id_po" class="full" required>
    <option value="">-- Pilih PO --</option>
    <?php while($p=$po->fetch_assoc()): ?>
        <?php $sisa = $p['qty_po'] - $p['total_masuk']; ?>
        <option value="<?= $p['id_po'] ?>">
            <?= $p['kode_po'] ?> | <?= htmlspecialchars($p['nama_barang']) ?> | Sisa: <?= $sisa ?>
        </option>
    <?php endwhile; ?>
</select>

<label class="full">Jumlah Barang Masuk</label>
<input type="number" name="jumlah" class="full" min="1" required>

<div class="full">
<button name="simpan">💾 Tambah Persediaan Barang</button>
</div>
</form>

<!-- TABEL PERSEDIAAN -->
<table>
<tr>
    <th>PO</th>
    <th>Barang</th>
    <th>Admin</th>
    <th>Barang PO</th>
    <th>Barang Masuk</th>
    <th>Barang Pesanan</th>
    <th>Stok Akhir</th>
    <th>Unit Price</th>
    <th>Total Nilai</th>
    <th>Aksi</th>
</tr>
<?php while($r=$data->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($r['kode_po']) ?></td>
    <td><?= htmlspecialchars($r['nama_barang']) ?></td>
    <td><?= htmlspecialchars($r['nama_vendor']) ?></td>
    <td><?= $r['barang_po'] ?></td>
    <td><?= $r['barang_masuk'] ?></td>
    <td><?= $r['barang_pesanan'] ?></td>
    <td><?= $r['stok_akhir'] ?></td>
    <td><?= number_format($r['unit_price'],2,',','.') ?></td>
    <td><?= number_format($r['total_nilai'],2,',','.') ?></td>
    <td>
        <form method="get" action="print_persediaan_barang.php" target="_blank">
            <input type="hidden" name="id_po" value="<?= $r['id_po'] ?>">
            <button type="submit">🖨️ Cetak</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>
</body>
</html>
