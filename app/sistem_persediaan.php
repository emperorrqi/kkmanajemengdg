<?php
require_once 'koneksi.php';

$id_barang    = $_POST['id_barang'];
$barang_masuk = (int) $_POST['barang_masuk'];
$unit_price   = (float) $_POST['unit_price'];

/* ===============================
   VALIDASI SISA PO
================================ */
$q = $mysqli->query("
    SELECT 
        IFNULL(SUM(po.jumlah),0) - IFNULL(SUM(p.barang_masuk),0) AS sisa
    FROM trx_purchase_order po
    LEFT JOIN trx_persediaan_barang p ON po.id_barang = p.id_barang
    WHERE po.id_barang = '$id_barang'
");

$sisa = (int) $q->fetch_assoc()['sisa'];

if ($barang_masuk > $sisa) {
    echo "<script>
        alert('Jumlah barang masuk melebihi sisa PO!');
        window.history.back();
    </script>";
    exit;
}

/* ===============================
   HITUNG STOK AKHIR
================================ */
$stok = $mysqli->query("
    SELECT stok FROM master_barang_elektronik
    WHERE id_barang = '$id_barang'
")->fetch_assoc()['stok'];

$stok_akhir = $stok + $barang_masuk;
$total_nilai = $barang_masuk * $unit_price;

/* ===============================
   INSERT PERSEDIAAN
================================ */
$stmt = $mysqli->prepare("
    INSERT INTO trx_persediaan_barang
    (id_barang, barang_masuk, stok_akhir, unit_price, total_nilai)
    VALUES (?,?,?,?,?)
");

$stmt->bind_param(
    "iiidd",
    $id_barang,
    $barang_masuk,
    $stok_akhir,
    $unit_price,
    $total_nilai
);

$stmt->execute();

header("Location: entry_persediaan_barang.php");
exit;
