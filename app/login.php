<?php
session_start();
require 'koneksi.php';

/* =============================
   REDIRECT JIKA SUDAH LOGIN
============================= */
if (!empty($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

/* =============================
   PROSES LOGIN
============================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi';
    } else {

        $stmt = $mysqli->prepare("
            SELECT id, username, password 
            FROM users 
            WHERE username = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $user, $hash);
            $stmt->fetch();

            if (password_verify($password, $hash)) {

                session_regenerate_id(true); // anti session fixation
                $_SESSION['user_id']  = $id;
                $_SESSION['username'] = $user;

                header("Location: index.php");
                exit;
            } else {
                $error = 'Username atau password salah';
            }
        } else {
            $error = 'Username atau password salah';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login | Sistem Persediaan Barang Elektronik</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #0d47a1, #1976d2);
    font-family: "Segoe UI", Tahoma, Arial, sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* =============================
   CARD LOGIN
============================= */
.login-card {
    width: 100%;
    max-width: 420px;
    background: #ffffff;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 18px 40px rgba(0,0,0,.25);
    animation: fadeIn .4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* =============================
   HEADER
============================= */
.header {
    text-align: center;
    margin-bottom: 25px;
}

.header img {
    max-width: 110px;
    margin-bottom: 10px;
}

.header h1 {
    font-size: 20px;
    margin: 0;
    color: #0d47a1;
    font-weight: 600;
}

.header p {
    margin: 5px 0 0;
    font-size: 14px;
    color: #555;
}

/* =============================
   FORM
============================= */
.form-group {
    margin-bottom: 15px;
}

label {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    display: block;
    margin-bottom: 6px;
}

input {
    width: 100%;
    padding: 11px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: .2s;
}

input:focus {
    border-color: #1976d2;
    outline: none;
    box-shadow: 0 0 0 2px rgba(25,118,210,.15);
}

/* =============================
   BUTTON
============================= */
button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    background: #1976d2;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}

button:hover {
    background: #0d47a1;
}

/* =============================
   ERROR
============================= */
.error {
    background: #fdecea;
    color: #b71c1c;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
    text-align: center;
    margin-bottom: 15px;
}

/* =============================
   FOOTER
============================= */
.footer {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: #666;
}

.footer a {
    color: #1976d2;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="login-card">

    <div class="header">
        <img src="logo.png" alt="PT Icon Plus">
        <h1>Sistem Persediaan Barang Elektronik</h1>
        <p>PT Icon Plus</p>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">MASUK SISTEM</button>
    </form>

    <div class="footer">
      ####///  Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>

</div>

</body>
</html>
