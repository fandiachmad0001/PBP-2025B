<?php
    session_start();
    include "../bin/database.php";
    $username = $_SESSION['username'];

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
    <link rel="icon" type="png" href="/projectfinalpbp/bin/logo/logo.png">
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
            <img src="/projectfinalpbp/bin/logo/logo.png" alt="AMP STORE" class="logo-amp">
        </a>
        <h1 id="namanav">AMP STORE</h1>

        <!-- Tombol toggler (kanan) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Panel menu -->
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li>
                    <h1 id="namanav"><?=$username?></h1>
                </li>
                <li class="nav-item ms-lg-2">
                    <form action="index.php" method="POST" class="d-inline">
                        <button name="logout" id="logout"><a class="btn btn-logout" name="logout">Logout</a></button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- CAROUSEL -->
<section class="container-fluid px-0">
    <div id="carouselAmp" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000" data-bs-pause="hover">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="/projectfinalpbp/bin/carousel/1.jpeg" class="d-block w-100" alt="AMP STORE">
            </div>
            <div class="carousel-item">
                <img src="/projectfinalpbp/bin/carousel/2.jpeg" class="d-block w-100" alt="Mobile Legends">
            </div>
            <div class="carousel-item">
                <img src="/projectfinalpbp/bin/carousel/3.jpeg" class="d-block w-100" alt="Free Fire">
            </div>
            <div class="carousel-item">
                <img src="/projectfinalpbp/bin/carousel/4.jpeg" class="d-block w-100" alt="eFootball">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselAmp" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselAmp" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- PILIHAN GAME -->
<section class="container-fluid game-section">
    <div class="text-center mb-4">
        <h2 class="section-title">Pilih Game Favoritmu</h2>
        <p class="section-subtitle">Temukan berbagai akun game yang tersedia di AMP STORE.</p>
    </div>
    <div class="row g-4 justify-content-center game-container">
        <!-- MOBILE LEGENDS -->
        <div class="col-12 col-md-4 d-flex justify-content-center">
            <a href="games/mlbb.php" class="game-link">
                <div class="game-card">
                    <img src="https://tse3.mm.bing.net/th/id/OIP.nSUOZ6LhpV6FB1EeJK8FpwHaHa?r=0&pid=Api&h=220&P=0" alt="Mobile Legends" class="kategorigambar">
                    <h5>Mobile Legends</h5>
                    <p>Lihat akun Mobile Legends</p>
                </div>
            </a>
        </div>
        <!-- FREE FIRE -->
        <div class="col-12 col-md-4 d-flex justify-content-center">
            <a href="marketplace.php?kategori=Free%20Fire" class="game-link">
                <div class="game-card">
                    <img src="https://tse4.mm.bing.net/th/id/OIP.u4tJT--GFtBwf_pOFq5YHQHaHa?r=0&pid=Api&h=220&P=0" alt="Free Fire" class="kategorigambar">
                    <h5>Free Fire</h5>
                    <p>Lihat akun Free Fire</p>
                </div>
            </a>
        </div>
        <!-- eFOOTBALL -->
        <div class="col-12 col-md-4 d-flex justify-content-center">
            <a href="marketplace.php?kategori=eFootball" class="game-link">
                <div class="game-card">
                    <img src="https://tse3.mm.bing.net/th/id/OIP.zqDm3Dh3pFjrPh_ytg_tmAHaHa?r=0&pid=Api&h=220&P=0" alt="eFootball" class="kategorigambar">
                    <h5>eFootball</h5>
                    <p>Lihat akun eFootball</p>
                </div>
            </a>
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