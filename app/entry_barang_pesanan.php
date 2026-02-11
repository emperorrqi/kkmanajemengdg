<?php
require 'koneksi.php';
session_start();

/* ======================================================
   GENERATE KODE PESANAN OTOMATIS
   Format: GI-YYYYMM-0001
====================================================== */
function generateKodePesanan($mysqli, $tanggal)
{
    $periode = date('Ym', strtotime($tanggal));

    $stmt = $mysqli->prepare("
        SELECT COUNT(*) as total
        FROM trx_barang_pesanan
        WHERE DATE_FORMAT(posting_date,'%Y%m') = ?
    ");
    $stmt->bind_param("s", $periode);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $urutan = str_pad($result['total'] + 1, 4, '0', STR_PAD_LEFT);

    return "GI-" . $periode . "-" . $urutan;
}

/* ======================================================
   PROSES SIMPAN DATA
====================================================== */
if (isset($_POST['simpan'])) {

    $posting_date  = $_POST['posting_date'] ?? '';
    $batch         = trim($_POST['batch'] ?? '');
    $id_admin      = (int)($_POST['id_admin'] ?? 0);
    $id_barang     = (int)($_POST['id_barang'] ?? 0);
    $id_sbu        = (int)($_POST['id_sbu'] ?? 0);
    $id_gudang     = (int)($_POST['id_gudang'] ?? 0);
    $jumlah        = (int)($_POST['jumlah'] ?? 0);
    $serial_number = trim($_POST['serial_number'] ?? '');

    if (!$posting_date || !$batch || !$id_admin || !$id_barang || !$id_sbu || !$id_gudang || $jumlah <= 0) {
        $_SESSION['error'] = "Semua field wajib diisi dengan benar.";
        header("Location: entry_barang_pesanan.php");
        exit;
    }

    try {

        $mysqli->begin_transaction();

        /* ===============================
           CEK STOK (LOCK DATA)
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
            throw new Exception("Stok tidak mencukupi. Sisa stok: " . $stok['stok']);
        }

        /* ===============================
           GENERATE KODE
        =============================== */
        $kode_pesanan = generateKodePesanan($mysqli, $posting_date);

        /* ===============================
           INSERT DATA
        =============================== */
        $stmt = $mysqli->prepare("
            INSERT INTO trx_barang_pesanan (
                kode_pesanan,
                posting_date,
                entry_date,
                document_date,
                batch,
                mvt_type,
                recipient,
                issued_by,
                sto_number,
                sto_item,
                id_admin,
                id_barang,
                id_sbu,
                id_gudang,
                jumlah,
                serial_number
            ) VALUES (
                ?, ?, CURDATE(), CURDATE(),
                ?, '201',
                '-', '-',
                ?, '001',
                ?, ?, ?, ?,
                ?, ?
            )
        ");

        $stmt->bind_param(
            "ssssiiiiis",
            $kode_pesanan,
            $posting_date,
            $batch,
            $kode_pesanan,
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
        $_SESSION['success'] = "Transaksi berhasil disimpan. Kode: " . $kode_pesanan;

    } catch (Exception $e) {
        $mysqli->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: entry_barang_pesanan.php");
    exit;
}

/* ======================================================
   LOAD MASTER DATA
====================================================== */
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
    GROUP BY b.id_barang
    HAVING stok > 0
");

$admin  = $mysqli->query("SELECT id_admin, nama_admin FROM master_administrasi ORDER BY nama_admin");
$sbu    = $mysqli->query("SELECT id_sbu, kode_sbu, location FROM master_sbu ORDER BY kode_sbu");
$gudang = $mysqli->query("SELECT id_gudang, kode_gudang, nama_gudang FROM master_gudang ORDER BY kode_gudang");

$riwayat = $mysqli->query("
    SELECT 
        p.kode_pesanan,
        p.posting_date,
        b.nama_barang,
        p.jumlah,
        s.kode_sbu,
        g.nama_gudang
    FROM trx_barang_pesanan p
    JOIN master_barang_elektronik b ON p.id_barang = b.id_barang
    JOIN master_sbu s ON p.id_sbu = s.id_sbu
    JOIN master_gudang g ON p.id_gudang = g.id_gudang
    ORDER BY p.id_pesanan DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Entry Barang Pesanan</title>
<style>
body { font-family: Arial; background:#f4f6f9; }
.container {
    width:1000px;
    margin:auto;
    background:white;
    padding:30px;
    margin-top:30px;
    border-radius:8px;
    box-shadow:0 3px 8px rgba(0,0,0,0.1);
}
h2 { margin-bottom:20px; }
input, select, textarea {
    width:100%;
    padding:8px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:4px;
}
button {
    background:#2c3e50;
    color:white;
    padding:10px 20px;
    border:none;
    border-radius:4px;
    cursor:pointer;
}
button:hover { background:#34495e; }
table {
    width:100%;
    border-collapse:collapse;
    margin-top:30px;
}
table th, table td {
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}
table th {
    background:#2c3e50;
    color:white;
}
.success { color:green; }
.error { color:red; }
</style>
</head>
<body>

<div class="container">
<h2>Entry Barang Pesanan (Goods Issue / STO)</h2>

<?php if(isset($_SESSION['success'])){ echo "<p class='success'>".$_SESSION['success']."</p>"; unset($_SESSION['success']); } ?>
<?php if(isset($_SESSION['error'])){ echo "<p class='error'>".$_SESSION['error']."</p>"; unset($_SESSION['error']); } ?>

<form method="POST">

<label>Posting Date</label>
<input type="date" name="posting_date" required>

<label>Batch</label>
<input type="text" name="batch" required>

<label>Admin</label>
<select name="id_admin" required>
<option value="">-- Pilih Admin --</option>
<?php while($a=$admin->fetch_assoc()){ ?>
<option value="<?= $a['id_admin'] ?>"><?= $a['nama_admin'] ?></option>
<?php } ?>
</select>

<label>Barang (Stok tersedia)</label>
<select name="id_barang" required>
<option value="">-- Pilih Barang --</option>
<?php while($b=$barang->fetch_assoc()){ ?>
<option value="<?= $b['id_barang'] ?>">
<?= $b['nama_barang'] ?> (Stok: <?= $b['stok'] ?>)
</option>
<?php } ?>
</select>

<label>SBU</label>
<select name="id_sbu" required>
<option value="">-- Pilih SBU --</option>
<?php while($s=$sbu->fetch_assoc()){ ?>
<option value="<?= $s['id_sbu'] ?>">
<?= $s['kode_sbu'] ?> - <?= $s['location'] ?>
</option>
<?php } ?>
</select>

<label>Gudang</label>
<select name="id_gudang" required>
<option value="">-- Pilih Gudang --</option>
<?php while($g=$gudang->fetch_assoc()){ ?>
<option value="<?= $g['id_gudang'] ?>">
<?= $g['kode_gudang'] ?> - <?= $g['nama_gudang'] ?>
</option>
<?php } ?>
</select>

<label>Jumlah</label>
<input type="number" name="jumlah" min="1" required>

<label>Serial Number / Keterangan</label>
<textarea name="serial_number"></textarea>

<button type="submit" name="simpan">Simpan Transaksi</button>
<a href="index.php">⬅ Kembali</a>
</form>

<h3>Riwayat Transaksi</h3>
<table>
<tr>
<th>Kode</th>
<th>Tanggal</th>
<th>Barang</th>
<th>Jumlah</th>
<th>SBU</th>
<th>Gudang</th>
<th>Print</th>
</tr>

<?php while($r=$riwayat->fetch_assoc()){ ?>
<tr>
<td><?= $r['kode_pesanan'] ?></td>
<td><?= $r['posting_date'] ?></td>
<td><?= $r['nama_barang'] ?></td>
<td><?= $r['jumlah'] ?></td>
<td><?= $r['kode_sbu'] ?></td>
<td><?= $r['nama_gudang'] ?></td>
<td>
<a href="cetak_barang_pesanan.php?kode=<?= $r['kode_pesanan'] ?>" target="_blank">
<button type="button">Print</button>
</a>
</td>
</tr>
<?php } ?>

</table>

</div>
</body>
</html>
