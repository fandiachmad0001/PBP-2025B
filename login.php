<?php
session_start();
require_once 'bin/database.php';

$pesan = "";

if (isset($_GET['register']) && $_GET['register'] == 'success') {
    $pesan = "Registrasi berhasil! Silakan login.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username == "" || $password == "") {

        $pesan = "Username dan password wajib diisi.";

    } else {

        // Cari akun berdasarkan username
        $query = mysqli_prepare(
            $koneksi,
            "SELECT id_akun, username, password, role
             FROM akun
             WHERE username = ?"
        );

        mysqli_stmt_bind_param($query, "s", $username);
        mysqli_stmt_execute($query);

        $hasil = mysqli_stmt_get_result($query);
        $akun = mysqli_fetch_assoc($hasil);

        // Periksa password dalam bentuk teks biasa
        if ($akun && $password === $akun['password']) {

            // Login khusus pelanggan
            if ($akun['role'] === 'pelanggan') {

                session_regenerate_id(true);

                $_SESSION['id_akun'] = $akun['id_akun'];
                $_SESSION['username'] = $akun['username'];
                $_SESSION['role'] = $akun['role'];

                mysqli_stmt_close($query);

                header("Location: pelanggan/index.php");
                exit;

            } else {

                session_regenerate_id(true);

                $_SESSION['id_akun'] = $akun['id_akun'];
                $_SESSION['username'] = $akun['username'];
                $_SESSION['role'] = $akun['role'];

                mysqli_stmt_close($query);

                header("Location: admin/index.php");
                exit;
            }

        } else {

            $pesan = "Username atau password salah.";

        }

        mysqli_stmt_close($query);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - AMP STORE</title>

    <link rel="icon" href="bin/logo/logo.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 20px;

            display: flex;
            justify-content: center;
            align-items: center;

            font-family: Arial, sans-serif;

            background: linear-gradient(
                135deg,
                #101b32,
                #244d83,
                #b8d9fa
            );
        }

        .login-container {
            width: 100%;
            max-width: 430px;

            padding: 35px;

            background: #ffffff;
            border-radius: 18px;

            box-shadow: 0 12px 35px rgba(0,0,0,0.2);
        }

        .logo-login {
            width: 85px;
            height: 85px;

            object-fit: contain;

            display: block;
            margin: 0 auto 12px;
        }

        .login-container h2 {
            text-align: center;
            color: #172d59;

            font-weight: bold;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;

            font-size: 14px;
            margin-bottom: 28px;
        }

        .form-label {
            color: #243b60;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            padding: 12px;
            border-radius: 9px;
        }

        .form-control:focus {
            border-color: #3676b8;
            box-shadow: 0 0 0 3px rgba(54,118,184,0.15);
        }

        .btn-login {
            width: 100%;
            padding: 12px;

            background: linear-gradient(
                135deg,
                #172d59,
                #3676b8
            );

            color: white;
            font-weight: bold;

            border: none;
            border-radius: 9px;

            transition: 0.5s;
        }

        .btn-login:hover {
            background: #244d83;
            color: white;
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            margin-top: 22px;

            font-size: 14px;
            color: #64748b;
        }

        .register-link a {
            color: #2563eb;
            font-weight: bold;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .back-home {
            display: block;
            text-align: center;

            margin-top: 18px;

            color: #64748b;
            text-decoration: none;
            font-size: 13px;
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 25px 20px;
            }
        }

    </style>

</head>

<body>

<div class="login-container">

    <img src="bin/logo/logo.png"
         alt="AMP STORE"
         class="logo-login">

    <h2>Selamat Datang</h2>

    <p class="subtitle">
        Login ke akun AMP STORE kamu
    </p>

    <?php if ($pesan != ""): ?>

        <div class="alert alert-info">
            <?= htmlspecialchars($pesan) ?>
        </div>

    <?php endif; ?>

    <form method="POST" action="login.php">

        <div class="mb-3">

            <label class="form-label">Username</label>

            <input
                type="text"
                name="username"
                class="form-control"
                placeholder="Masukkan username"
                autocomplete="username"
                required
            >

        </div>

        <div class="mb-4">

            <label class="form-label">Password</label>

            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
            >

        </div>

        <button type="submit" class="btn btn-login">
            Login
        </button>

    </form>

    <div class="register-link">

        Belum punya akun?
        <a href="register.php">Register sekarang</a>

    </div>

    <a href="index.php" class="back-home">
        &larr; Kembali ke Beranda
    </a>

</div>

</body>
</html>