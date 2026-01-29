<?php
require_once 'koneksi.php';

$id_persediaan = $_POST['id_persediaan'];
$posting_date  = $_POST['posting_date'];
$serial        = $_POST['serial_number'];
$location      = $_POST['location_text'];
$plant         = $_POST['plant'];
$recipient     = $_POST['recipient'];
$issued_by     = $_POST['issued_by'];

/* ===============================
   CEK STOK TERSEDIA
================================ */
$q = $mysqli->query("
    SELECT stok_akhir, id_barang
    FROM trx_persediaan_barang
    WHERE id_persediaan = '$id_persediaan'
");

$data = $q->fetch_assoc();

if (!$data || $data['stok_akhir'] <= 0) {
    echo "<script>
        alert('Stok tidak mencukupi!');
        window.history.back();
    </script>";
    exit;
}

/* ===============================
   INSERT BARANG PESANAN
================================ */
$stmt = $mysqli->prepare("
    INSERT INTO trx_barang_pesanan
    (posting_date, id_persediaan, serial_number,
     location_text, plant, recipient, issued_by)
    VALUES (?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "sisssss",
    $posting_date,
    $id_persediaan,
    $serial,
    $location,
    $plant,
    $recipient,
    $issued_by
);

$stmt->execute();

/* ===============================
   UPDATE STOK (KURANGI 1)
================================ */
$mysqli->query("
    UPDATE trx_persediaan_barang
    SET stok_akhir = stok_akhir - 1
    WHERE id_persediaan = '$id_persediaan'
");

header("Location: entry_barang_pesanan.php");
exit;
