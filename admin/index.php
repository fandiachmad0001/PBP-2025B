<?php
    session_start();
    include "../bin/database.php";
    $username = $_SESSION['username'] ?? '';

    if(isset($_POST["logout"])){
        session_unset();
        session_destroy();
        header('location: ../index.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../bin/logo/logo.png">
    <title>AMP STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-amp sticky-top" id="navbar">
    <div class="container-fluid px-4">

        <!-- LOGO DI KIRI -->
        <a href="index.php" class="navbar-brand logo-link">
            <img src="../bin/logo/logo.png" alt="AMP STORE" class="logo-amp">
        </a>
        <h1 id="namanav">ADMIN AMP STORE</h1>

        <!-- Tombol toggler (kanan) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Panel menu -->
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item ms-lg-2">
                    <form action="" method="POST" class="d-inline">
                        <button type="submit" name="logout" id="logout" class="btn btn-logout">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- PILIHAN GAME -->
<section class="container-fluid game-section">
    <div class="text-center mb-4">
        <h2 class="section-title">Stock Game</h2>
        <p class="section-subtitle">Temukan berbagai akun game yang tersedia di AMP STORE.</p>
    </div>
    <div class="row g-4 justify-content-center game-container">
        <!-- MOBILE LEGENDS -->
        <div class="col-12 col-md-4 d-flex justify-content-center">
            <div class="game-link">
                <div class="game-card">
                    <img src="https://tse3.mm.bing.net/th/id/OIP.nSUOZ6LhpV6FB1EeJK8FpwHaHa?r=0&pid=Api&h=220&P=0" alt="Mobile Legends" class="kategorigambar">
                    <h5>Mobile Legends</h5>
                    <p>Lihat stok akun Mobile Legends</p>
                    <a href="stok/mlbb.php" class="btn btn-stock">Lihat Stock</a>
                    <a href="tambah/mlbb.php" class="btn btn-tambah-stock">Tambah Stock</a>
                </div>
            </div>
        </div>
        <!-- FREE FIRE -->
        <div class="col-12 col-md-4 d-flex justify-content-center">
            <div class="game-link">
                <div class="game-card">
                    <img src="https://tse4.mm.bing.net/th/id/OIP.u4tJT--GFtBwf_pOFq5YHQHaHa?r=0&pid=Api&h=220&P=0" alt="Free Fire" class="kategorigambar">
                    <h5>Free Fire</h5>
                    <p>Lihat stok akun Free Fire</p>
                    <a href="stok/freefire.php" class="btn btn-stock">Lihat Stock</a>
                    <a href="tambah/freefire.php" class="btn btn-tambah-stock">Tambah Stock</a>
                </div>
            </div>
        </div>
        <!-- eFOOTBALL -->
        <div class="col-12 col-md-4 d-flex justify-content-center">
            <div class="game-link">
                <div class="game-card">
                    <img src="https://tse3.mm.bing.net/th/id/OIP.zqDm3Dh3pFjrPh_ytg_tmAHaHa?r=0&pid=Api&h=220&P=0" alt="eFootball" class="kategorigambar">
                    <h5>eFootball</h5>
                    <p>Lihat stok akun eFootball</p>
                    <a href="stok/efootball.php" class="btn btn-stock">Lihat Stock</a>
                    <a href="tambah/efootball.php" class="btn btn-tambah-stock">Tambah Stock</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer-amp">
    <div class="container text-center">
        <h5>AMP STORE</h5>
        <p>Marketplace jual beli akun game.</p>
        <p class="copyright">&copy; 2026 AMP STORE. All Rights Reserved.</p>
    </div>
</footer>

<!-- BOOTSTRAP JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>