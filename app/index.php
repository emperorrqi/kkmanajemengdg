<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard | Sistem Persediaan Barang Elektronik</title>

  <style>
    * { box-sizing: border-box; }

    body {
      margin: 0;
      padding: 0;
      background: #f4f7f8;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .header {
      background: #007bff;
      padding: 20px;
      text-align: center;
      color: #fff;
      font-size: 24px;
      font-weight: bold;
    }

    .container {
      max-width: 900px;
      margin: 30px auto;
      padding: 30px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .username {
      text-align: center;
      color: #555;
      margin-bottom: 30px;
      font-size: 15px;
    }

    .section {
      margin-bottom: 35px;
    }

    .section h3 {
      margin: 0 0 20px 0;
      padding-bottom: 8px;
      border-bottom: 2px solid #eee;
      color: #333;
      font-size: 18px;
    }

    /* Menu grid */
    ul.menu {
      list-style: none;
      padding: 0;
      margin: 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 15px;
    }

    ul.menu a {
      display: block;
      padding: 12px 18px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      text-align: center;
      font-weight: bold;
      border-radius: 6px;
      transition: 0.3s ease-in-out;
    }

    ul.menu a:hover {
      background: #0056b3;
      box-shadow: 0 4px 12px rgba(0,86,179,0.4);
    }

    .logout {
      text-align: center;
      margin-top: 40px;
    }

    .logout a {
      color: #e74c3c;
      font-weight: bold;
      text-decoration: none;
      font-size: 15px;
    }

    .logout a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <!-- Header -->
  <div class="header">
    📦 Sistem Persediaan Barang
  </div>

  <div class="container">

    <!-- User -->
    <p class="username">
      Selamat datang, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
    </p>

    <!-- MASTER DATA -->
    <div class="section">
      <h3>🗂️ Master Data</h3>
      <ul class="menu">
        <li><a href="administrasi.php">Master Administrasi</a></li>
         <li><a href="vendor.php">Master Vendor</a></li>
        <li><a href="barang_elektronik.php">Master Barang Elektronik</a></li>
        <li><a href="driver.php">Master Driver</a></li>
        <li><a href="gudang.php">Master Gudang</a></li>
        <li><a href="sbu.php">Master SBU</a></li>
      </ul>
    </div>

    <!-- TRANSAKSI -->
    <div class="section">
      <h3>🔄 Transaksi</h3>
      <ul class="menu">
        <li><a href="entry_barang_pesanan.php">Entry barang pesanan</a></li>
        <li><a href="entry_purchase_order.php">Entry purchase order</a></li>
        <li><a href="entry_persediaan_barang.php">Entry Persediaan barang</a></li>
        <li><a href="entry_retur.php">Entry Retur</a></li>
        <li><a href="entry_basterima.php">Entry Berita serah terima</a></li>
        <li><a href="entry_surat_jalan.php">Entry Surat Jalan</a></li>
      </ul>
    </div>

    <!-- LAPORAN -->
    <div class="section">
      <h3>📊 Laporan</h3>
      <ul class="menu">
        <li><a href="laporan_barang_pesanan.php">Laporan barang pesanan</a></li>
        <li><a href="laporan_purchase_order.php">Laporan purchase order </a></li>
        <li><a href="laporan_persediaan_barang.php">Laporan persediaan barang </a></li>
        <li><a href="laporan_retur.php">Laporan retur</a></li>
        <li><a href="laporan_basterima.php">Laporan basterima</a></li>
        <li><a href="laporan_surat_jalan.php">Laporan surat jalan </a></li>
      </ul>
    </div>

    <!-- LOGOUT -->
    <div class="logout">
      <a href="keluar.php">← Keluar Aplikasi</a>
    </div>

  </div>

</body>
</html>

