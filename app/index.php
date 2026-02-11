<?php
/**
 * =====================================================
 * DASHBOARD UTAMA - SISTEM PERSEDIAAN BARANG ELEKTRONIK
 * PT ICON PLUS
 * =====================================================
 */
session_start();

if (empty($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Sistem Persediaan Barang</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: "Segoe UI", Tahoma, Arial, sans-serif;
    background: #f4f6fa;
    color: #333;
}

/* ======================
   SIDEBAR
====================== */
.sidebar {
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #0d47a1, #1565c0);
    position: fixed;
    padding: 25px 20px;
    color: #fff;
}

.sidebar .brand {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 30px;
}

.sidebar a {
    display: block;
    padding: 12px 15px;
    color: #e3f2fd;
    text-decoration: none;
    border-radius: 8px;
    margin-bottom: 6px;
    font-weight: 500;
    transition: .2s;
    cursor: pointer;
}

.sidebar a:hover,
.sidebar a.active {
    background: rgba(255,255,255,.2);
}

.sidebar .divider {
    margin: 20px 0;
    height: 1px;
    background: rgba(255,255,255,.2);
}

.sidebar .logout {
    color: #ffcdd2;
}

/* ======================
   MAIN CONTENT
====================== */
.main {
    margin-left: 260px;
    padding: 30px;
}

.header {
    background: #fff;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header h1 {
    margin: 0;
    font-size: 22px;
}

/* ======================
   CONTENT SECTIONS
====================== */
.section {
    display: none;
}

.section.active {
    display: block;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 22px;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
}

.card h3 {
    margin: 0 0 15px;
    font-size: 18px;
}

.card ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.card ul li {
    margin-bottom: 10px;
}

.card ul li a {
    color: #1976d2;
    text-decoration: none;
    font-weight: 500;
}

.card ul li a:hover {
    text-decoration: underline;
}

.footer {
    text-align: center;
    font-size: 13px;
    color: #777;
    margin-top: 40px;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="brand">📦 Inventory System</div>

    <a onclick="showSection('dashboard')" class="active">🏠 Dashboard</a>
    <a onclick="showSection('master')">🗂️ Master Data</a>
    <a onclick="showSection('transaksi')">🔄 Transaksi</a>
    <a onclick="showSection('laporan')">📊 Laporan</a>

    <div class="divider"></div>

    <a href="keluar.php" class="logout">🚪 Logout</a>
</div>

<!-- MAIN -->
<div class="main">

<div class="header">
    <h1 id="title">Dashboard</h1>
    <div>Login sebagai <strong><?= $username ?></strong></div>
</div>

<!-- DASHBOARD -->
<div class="section active" id="dashboard">
    <div class="card">
        <h3>👋 Selamat Datang</h3>
        <p>Sistem Persediaan Barang Elektronik PT Icon Plus.<br>
        Gunakan menu di samping untuk mengelola data.</p>
    </div>
</div>

<!-- MASTER DATA -->
<div class="section" id="master">
<div class="cards">
    <div class="card">
        <h3>🗂️ Master Data</h3>
        <ul>
            <li><a href="administrasi.php">Administrasi</a></li>
            <li><a href="vendor.php">Vendor</a></li>
            <li><a href="barang_elektronik.php">Data Barang Elektronik</a></li>
            <li><a href="driver.php">Driver</a></li>
            <li><a href="gudang.php">Gudang</a></li>
            <li><a href="sbu.php">SBU</a></li>
        </ul>
    </div>
</div>
</div>

<!-- TRANSAKSI -->
<div class="section" id="transaksi">
<div class="cards">
    <div class="card">
        <h3>🔄 Transaksi</h3>
        <ul>
            <li><a href="entry_barang_pesanan.php">Barang Pesanan</a></li>
            <li><a href="entry_purchase_order.php">Purchase Order</a></li>
            <li><a href="entry_persediaan_barang.php">Persediaan Barang</a></li>
            <li><a href="entry_retur.php">Retur</a></li>
            <li><a href="entry_basterima.php">Berita Serah Terima</a></li>
            <li><a href="entry_surat_jalan.php">Surat Jalan</a></li>
        </ul>
    </div>
</div>
</div>

<!-- LAPORAN -->
<div class="section" id="laporan">
<div class="cards">
    <div class="card">
        <h3>📊 Laporan</h3>
        <ul>
            <li><a href="laporan_barang_pesanan.php">Barang Pesanan</a></li>
            <li><a href="laporan_purchase_order.php">Purchase Order</a></li>
            <li><a href="laporan_persediaan_barang.php">Persediaan Barang</a></li>
            <li><a href="laporan_retur.php">Retur</a></li>
            <li><a href="laporan_basterima.php">Berita Serah Terima</a></li>
            <li><a href="laporan_surat_jalan.php">Surat Jalan</a></li>
        </ul>
    </div>
</div>
</div>

<div class="footer">
    © <?= date('Y') ?> Sistem Persediaan Barang Elektronik — PT Icon Plus
</div>

</div>

<script>
function showSection(id) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');

    document.querySelectorAll('.sidebar a').forEach(a => a.classList.remove('active'));
    event.target.classList.add('active');

    document.getElementById('title').innerText =
        id.charAt(0).toUpperCase() + id.slice(1);
}
</script>

</body>
</html>
