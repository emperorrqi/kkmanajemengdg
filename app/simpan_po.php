<?php
require_once 'koneksi.php';

/* ===============================
   VALIDASI INPUT WAJIB
================================ */
$required = [
    'tanggal_po',
    'id_vendor',
    'id_barang',
    'uom',
    'jumlah',
    'unit_price',
    'invoice_to',
    'ship_to'
];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die("Data tidak lengkap: $field");
    }
}

/* ===============================
   AMBIL & SANITASI DATA
================================ */
$tanggal_po  = $_POST['tanggal_po'];
$id_vendor   = (int) $_POST['id_vendor'];
$id_barang   = (int) $_POST['id_barang'];
$uom         = trim($_POST['uom']);
$jumlah      = (int) $_POST['jumlah'];
$unit_price  = (float) $_POST['unit_price'];
$invoice_to  = trim($_POST['invoice_to']);
$ship_to     = trim($_POST['ship_to']);
$project     = $_POST['project_name'] ?? null;

/* ===============================
   VALIDASI LOGIS
================================ */
if ($jumlah <= 0 || $unit_price < 0) {
    die("Jumlah atau harga tidak valid");
}

/* ===============================
   HITUNG TOTAL (SERVER SIDE)
================================ */
$total = $jumlah * $unit_price;

try {
    /* ===============================
       INSERT PURCHASE ORDER
    ================================ */
    $stmt = $mysqli->prepare("
        INSERT INTO trx_purchase_order
        (tanggal_po, id_vendor, id_barang, uom, jumlah, unit_price, total, invoice_to, ship_to, project_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Prepare gagal: " . $mysqli->error);
    }

    $stmt->bind_param(
        "siisiddsss",
        $tanggal_po,
        $id_vendor,
        $id_barang,
        $uom,
        $jumlah,
        $unit_price,
        $total,
        $invoice_to,
        $ship_to,
        $project
    );

    $stmt->execute();

    $stmt->close();

    /* ===============================
       REDIRECT JIKA BERHASIL
    ================================ */
    header("Location: entry_purchase_order.php?status=success");
    exit;

} catch (Exception $e) {

    echo "<h3>Gagal menyimpan Purchase Order</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
