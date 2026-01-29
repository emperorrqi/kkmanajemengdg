<?php
include 'koneksi.php';
session_start();

$id_po = (int)($_GET['id_po'] ?? 0);
if ($id_po <= 0) die("PO tidak valid.");

// Ambil data PO + total barang masuk + total barang pesanan + tanggal masuk terakhir + admin terakhir
$stmt = $mysqli->prepare("
    SELECT 
        p.id_po,
        p.kode_po,
        b.nama_barang,
        v.nama_vendor,
        p.jumlah AS qty_po,
        COALESCE(SUM(pb.jumlah),0) AS total_masuk,
        COALESCE(SUM(bp.jumlah),0) AS total_pesanan,
        last_entry.tanggal_masuk,
        last_entry.nama_admin,
        p.unit_price
    FROM trx_purchase_order p
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    JOIN master_vendor v ON p.id_vendor = v.id_vendor
    LEFT JOIN trx_persediaan_barang pb ON p.id_po = pb.id_po
    LEFT JOIN trx_barang_pesanan bp ON p.id_barang = bp.id_barang
    LEFT JOIN (
        SELECT id_po, tanggal AS tanggal_masuk, 
               (SELECT nama_admin FROM master_administrasi WHERE id_admin = pb2.id_admin) AS nama_admin
        FROM trx_persediaan_barang pb2
        WHERE id_po = ?
        ORDER BY tanggal DESC
        LIMIT 1
    ) AS last_entry ON last_entry.id_po = p.id_po
    WHERE p.id_po = ?
    GROUP BY p.id_po, p.kode_po, b.nama_barang, v.nama_vendor, last_entry.tanggal_masuk, last_entry.nama_admin, p.unit_price
");
$stmt->bind_param("ii", $id_po, $id_po);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) die("Data PO tidak ditemukan.");

// Hitung stok akhir & total nilai
$stok_akhir = $data['total_masuk'] - $data['total_pesanan'];
$total_nilai = $stok_akhir * $data['unit_price'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Barang - <?= htmlspecialchars($data['kode_po']) ?></title>
<style>
body { font-family: 'Arial', sans-serif; margin: 30px; color: #111; }
header { text-align: center; margin-bottom: 30px; }
header h1 { margin: 0; font-size: 26px; }
header p { margin: 2px; font-size: 14px; color: #555; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #333; padding: 12px; text-align: center; }
th { background: #f0f0f0; }
tfoot td { font-weight: bold; }
.print-button { display: none; margin-bottom: 20px; }
@media print { .print-button { display: none; } }
</style>
</head>
<body>

<header>
    <h1>PT. Contoh Perusahaan</h1>
    <p>📋 Laporan Persediaan Barang</p>
    <p>PO: <?= htmlspecialchars($data['kode_po']) ?> | Vendor: <?= htmlspecialchars($data['nama_vendor']) ?></p>
    <p>Tanggal Masuk Terakhir: <?= $data['tanggal_masuk'] ? date('d-m-Y', strtotime($data['tanggal_masuk'])) : '-' ?></p>
    <p>Admin: <?= htmlspecialchars($data['nama_admin'] ?? '-') ?></p>
</header>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Barang</th>
            <th>Jumlah PO</th>
            <th>Barang Masuk</th>
            <th>Barang Pesanan</th>
            <th>Stok Akhir</th>
            <th>Unit Price</th>
            <th>Total Nilai</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td><?= htmlspecialchars($data['nama_barang']) ?></td>
            <td><?= $data['qty_po'] ?></td>
            <td><?= $data['total_masuk'] ?></td>
            <td><?= $data['total_pesanan'] ?></td>
            <td><?= $stok_akhir ?></td>
            <td><?= number_format($data['unit_price'],2,',','.') ?></td>
            <td><?= number_format($total_nilai,2,',','.') ?></td>
        </tr>
    </tbody>
</table>

<button class="print-button" onclick="window.print()">🖨️ Cetak Halaman</button>

<script>
window.onload = function() {
    window.print();
};
</script>

</body>
</html>
