<?php
include 'koneksi.php';
session_start();

/* ================= CSRF ================= */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ================= SIMPAN PO ================= */
if (isset($_POST['simpan'])) {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die("CSRF token tidak valid");
    }

    $tanggal_po  = $_POST['tanggal_po'];
    $delivery    = $_POST['delivery_date'] ?: null;
    $buyer       = trim($_POST['buyer']);
    $vendor      = (int) $_POST['id_vendor'];
    $invoice_to  = trim($_POST['invoice_to']);
    $ship_to     = trim($_POST['ship_to']);
    $project     = trim($_POST['project_name']);
    $barang      = (int) $_POST['id_barang'];
    $jumlah      = (int) $_POST['jumlah'];
    $uom         = trim($_POST['uom']);
    $harga       = (float) $_POST['unit_price'];
    $total       = $jumlah * $harga;

    if ($tanggal_po == '' || $vendor == 0 || $barang == 0 || $jumlah <= 0 || $harga <= 0) {
        $_SESSION['error'] = "⚠️ Data wajib belum lengkap";
    } else {

        $stmt = $mysqli->prepare("
            INSERT INTO trx_purchase_order
            (tanggal_po, delivery_date, buyer,
             id_vendor, invoice_to, ship_to, project_name,
             id_barang, jumlah, uom, unit_price, total)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "ssssisssiisd",
            $tanggal_po,
            $delivery,
            $buyer,
            $vendor,
            $invoice_to,
            $ship_to,
            $project,
            $barang,
            $jumlah,
            $uom,
            $harga,
            $total
        );

        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "✅ Purchase Order berhasil dibuat";
        header("Location: entry_purchase_order.php");
        exit;
    }
}

/* ================= MASTER ================= */
$vendor = $mysqli->query("SELECT id_vendor, nama_vendor FROM master_vendor ORDER BY nama_vendor");
$barang = $mysqli->query("SELECT id_barang, kode_barang, nama_barang FROM master_barang_elektronik ORDER BY nama_barang");

/* ================= DATA PO ================= */
$po = $mysqli->query("
    SELECT p.kode_po, p.tanggal_po,
           v.nama_vendor,
           b.kode_barang, b.nama_barang,
           p.jumlah, p.uom, p.total
    FROM trx_purchase_order p
    JOIN master_vendor v ON p.id_vendor = v.id_vendor
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    ORDER BY p.id_po DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Entry Purchase Order</title>
<style>
body{font-family:Inter,Arial;background:#f3f4f6;padding:30px}
.container{max-width:1200px;margin:auto}
.card{background:#fff;padding:30px;border-radius:14px;box-shadow:0 10px 25px rgba(0,0,0,.08)}
h2{text-align:center;margin-bottom:25px}
form{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.full{grid-column:1/3}
input,select,button{
    padding:12px;border-radius:8px;border:1px solid #d1d5db;font-size:14px
}
button{
    background:#2563eb;color:#fff;font-weight:600;border:none;cursor:pointer
}
button:hover{background:#1e40af}
table{width:100%;border-collapse:collapse;margin-top:30px}
th,td{padding:14px;border-bottom:1px solid #e5e7eb}
th{background:#f3f4f6;text-align:left}
.alert-success{background:#dcfce7;padding:12px;border-radius:8px;margin-bottom:15px}
.alert-error{background:#fee2e2;padding:12px;border-radius:8px;margin-bottom:15px}
.badge{background:#e0e7ff;padding:4px 10px;border-radius:999px;font-size:12px}
.btn-print{
    padding:8px 14px;
    background:#16a34a;
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
}
.btn-print:hover{background:#15803d}
</style>
</head>
<body>

<div class="container">
<div class="card">

<a href="index.php">⬅ Kembali</a>
<h2>🧾 Entry Purchase Order</h2>

<?php if (!empty($_SESSION['success'])): ?>
<div class="alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- FORM -->
<form method="post">
<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

<label>Tanggal PO</label>
<input type="date" name="tanggal_po" required>

<label>Delivery Date</label>
<input type="date" name="delivery_date">

<label>Buyer</label>
<input type="text" name="buyer">

<label>Vendor</label>
<select name="id_vendor" required>
<option value="">-- Pilih Vendor --</option>
<?php while($v=$vendor->fetch_assoc()): ?>
<option value="<?= $v['id_vendor'] ?>"><?= htmlspecialchars($v['nama_vendor']) ?></option>
<?php endwhile; ?>
</select>

<label>Invoice To</label>
<input type="text" name="invoice_to">

<label>Ship To</label>
<input type="text" name="ship_to">

<label class="full">Project Name</label>
<input type="text" name="project_name" class="full">

<label>Barang</label>
<select name="id_barang" required>
<option value="">-- Pilih Barang --</option>
<?php while($b=$barang->fetch_assoc()): ?>
<option value="<?= $b['id_barang'] ?>">
<?= $b['kode_barang'].' - '.$b['nama_barang'] ?>
</option>
<?php endwhile; ?>
</select>

<label>Jumlah</label>
<input type="number" name="jumlah" min="1" required>

<label>UOM</label>
<input type="text" name="uom" placeholder="PCS / UNIT">

<label>Harga Satuan</label>
<input type="number" name="unit_price" step="0.01" required>

<div class="full">
<button name="simpan">💾 Tambah Purchase Order</button>
</div>
</form>

<!-- LIST PO -->
<h3 style="margin-top:40px;">📋 Daftar Purchase Order</h3>
<table>
<tr>
    <th>Kode PO</th>
    <th>Tanggal</th>
    <th>Vendor</th>
    <th>Barang</th>
    <th>Qty</th>
    <th>Total</th>
    <th>Aksi</th>
</tr>

<?php while($r=$po->fetch_assoc()): ?>
<tr>
    <td><span class="badge"><?= $r['kode_po'] ?></span></td>
    <td><?= date('d M Y', strtotime($r['tanggal_po'])) ?></td>
    <td><?= htmlspecialchars($r['nama_vendor']) ?></td>
    <td><?= htmlspecialchars($r['kode_barang'].' - '.$r['nama_barang']) ?></td>
    <td><?= $r['jumlah'].' '.$r['uom'] ?></td>
    <td>Rp <?= number_format($r['total'],2,',','.') ?></td>
    <td>
        <a href="cetak_purchase_order.php?kode_po=<?= urlencode($r['kode_po']) ?>"
           target="_blank"
           class="btn-print">
           🖨 print
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>
</body>
</html>
