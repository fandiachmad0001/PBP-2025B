<?php
session_start();
require_once 'bin/database.php';

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Validasi input
    if ($username == "" || $password == "" || $konfirmasi == "") {

        $pesan = "Semua kolom wajib diisi.";

    } elseif (strlen($username) > 100) {

        $pesan = "Username maksimal 100 karakter.";

    } elseif (strlen($password) < 8) {

        $pesan = "Password minimal 8 karakter.";

    } elseif ($password !== $konfirmasi) {

        $pesan = "Konfirmasi password tidak sesuai.";

    } else {

        // Cek username di database
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id_akun FROM akun WHERE username = ?"
        );

        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {

            $pesan = "Username sudah digunakan.";

        } else {

            // Password disimpan langsung sebagai teks biasa
            $password_db = $password;

            // Role otomatis pelanggan
            $role = "pelanggan";

            // Insert data akun
            $query = mysqli_prepare(
                $koneksi,
                "INSERT INTO akun (username, password, role)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $query,
                "sss",
                $username,
                $password_db,
                $role
            );

            if (mysqli_stmt_execute($query)) {

                mysqli_stmt_close($query);
                mysqli_stmt_close($cek);

                header("Location: login.php?register=success");
                exit;

            } else {

                $pesan = "Registrasi gagal. Silakan coba kembali.";

            }

            mysqli_stmt_close($query);
        }

        mysqli_stmt_close($cek);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - AMP STORE</title>

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

        .register-container {
            width: 100%;
            max-width: 430px;

            padding: 35px;

            background: #ffffff;
            border-radius: 18px;

            box-shadow: 0 12px 35px rgba(0,0,0,0.2);
        }

        .logo-register {
            width: 85px;
            height: 85px;

            object-fit: contain;

            display: block;
            margin: 0 auto 12px;
        }

        .register-container h2 {
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

        .btn-register {
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

            transition: 0.3s;
        }

        .btn-register:hover {
            background: #244d83;
            color: white;
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            margin-top: 22px;

            font-size: 14px;
            color: #64748b;
        }

        .login-link a {
            color: #2563eb;
            font-weight: bold;
            text-decoration: none;
        }

        .login-link a:hover {
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
            .register-container {
                padding: 25px 20px;
            }
        }

    </style>

</head>

<body>

<div class="register-container">

    <img src="bin/logo/logo.png"
         alt="AMP STORE"
         class="logo-register">

    <h2>Buat Akun</h2>

    <p class="subtitle">
        Daftar dan mulai jelajahi AMP STORE
    </p>

    <?php if ($pesan != ""): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($pesan) ?>
        </div>

    <?php endif; ?>

    <form method="POST" action="register.php">

        <div class="mb-3">

            <label class="form-label">Username</label>

            <input
                type="text"
                name="username"
                class="form-control"
                placeholder="Masukkan username"
                maxlength="100"
                autocomplete="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                required
            >

        </div>

        <div class="mb-3">

            <label class="form-label">Password</label>

            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Minimal 8 karakter"
                minlength="8"
                autocomplete="new-password"
                required
            >

        </div>

        <div class="mb-4">

            <label class="form-label">Konfirmasi Password</label>

            <input
                type="password"
                name="konfirmasi"
                class="form-control"
                placeholder="Ulangi password"
                minlength="8"
                autocomplete="new-password"
                required
            >

        </div>

        <button type="submit" class="btn btn-register">
            Register
        </button>

    </form>

    <div class="login-link">
        Sudah punya akun?
        <a href="login.php">Login di sini</a>
    </div>

    <a href="index.php" class="back-home">
        &larr; Kembali ke Beranda
    </a>

</div>

</body>
</html>