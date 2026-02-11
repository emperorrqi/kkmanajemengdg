<?php
require 'koneksi.php';

/* ===========================
   FILTER INPUT
=========================== */
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$kode_po   = $_GET['kode_po'] ?? '';

$where = [];
$params = [];
$types = '';

if($tgl_awal) {
    $where[] = "p.tanggal_po >= ?";
    $params[] = $tgl_awal;
    $types .= 's';
}
if($tgl_akhir) {
    $where[] = "p.tanggal_po <= ?";
    $params[] = $tgl_akhir;
    $types .= 's';
}
if($kode_po) {
    $where[] = "p.kode_po LIKE ?";
    $params[] = "%$kode_po%";
    $types .= 's';
}

$where_sql = '';
if($where) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

/* ===========================
   AMBIL DATA PURCHASE ORDER
=========================== */
$stmt = $mysqli->prepare("
    SELECT p.kode_po, p.tanggal_po, v.nama_vendor, b.nama_barang,
           p.jumlah, p.uom, p.unit_price, p.total, p.invoice_to, p.ship_to, p.project_name
    FROM trx_purchase_order p
    JOIN master_vendor v ON p.id_vendor = v.id_vendor
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    $where_sql
    ORDER BY p.id_po DESC
");

if($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$po = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Purchase Order</title>
<style>
body{font-family:Inter,Arial;background:#f9fafb;padding:20px;color:#111}
.container{max-width:1200px;margin:auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.05)}
h1,h2,h3{text-align:center;margin:5px 0}
h1{font-size:28px;color:#1d4ed8}
h2{font-size:20px;color:#0f172a;margin-bottom:30px}
button, input[type="date"], input[type="text"]{padding:10px;border-radius:6px;border:1px solid #d1d5db;font-size:14px}
button{background:#2563eb;color:#fff;border:none;cursor:pointer;font-weight:600;margin-left:10px}
form{margin-bottom:20px;text-align:center}
form input, form button{margin:5px}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{padding:12px;border:1px solid #e5e7eb;text-align:center;font-size:14px}
th{background:#f3f4f6}
tfoot td{font-weight:bold;background:#f9fafb}
@media print{
    form, button{display:none}
    body{background:#fff;padding:0}
    .container{box-shadow:none;padding:0;margin:0}
}
</style>
</head>
<body>
<div class="container">

<h1>📄 Laporan Purchase Order</h1>
<a href="index.php">⬅ Kembali</a>
<h2>Manajemen Gudang</h2>

<!-- FORM FILTER -->
<form method="get">
    <label>Tanggal Awal: <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>"></label>
    <label>Tanggal Akhir: <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>"></label>
    <label>Kode PO: <input type="text" name="kode_po" placeholder="" value="<?= htmlspecialchars($kode_po) ?>"></label>
    <button type="submit">🔍 Preview</button>
    <button type="button" onclick="window.print()">🖨 print Laporan</button>
</form>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Kode PO</th>
    <th>Tanggal</th>
    <th>Vendor</th>
    <th>Barang</th>
    <th>Qty</th>
    <th>Harga Satuan</th>
    <th>Total</th>
</tr>
</thead>
<tbody>
<?php 
$no=1;
$grand_total = 0;
while($r = $po->fetch_assoc()):
    $grand_total += $r['total'];
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($r['kode_po'] ?? '') ?></td>
    <td><?= htmlspecialchars($r['tanggal_po'] ?? '') ?></td>
    <td><?= htmlspecialchars($r['nama_vendor'] ?? '') ?></td>
    <td><?= htmlspecialchars($r['nama_barang'] ?? '') ?></td>
    <td><?= $r['jumlah'].' '.$r['uom'] ?></td>
    <td><?= number_format($r['unit_price'],2,',','.') ?></td>
    <td><?= number_format($r['total'],2,',','.') ?></td>
</tr>
<?php endwhile; ?>
</tbody>
<tfoot>
<tr>
    <td colspan="7">Grand Total</td>
    <td><?= number_format($grand_total,2,',','.') ?></td>
</tr>
</tfoot>
</table>

</div>
</body>
</html>
