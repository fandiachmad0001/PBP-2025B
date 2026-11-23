<?php
session_start();
require_once "../../bin/database.php";

$username = $_SESSION['username'] ?? null;

if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ../../index.php");
    exit;
}

if (isset($_POST['home'])) {
    header("Location: ../index.php");
    exit;
}

// Cek apakah tabel `ml` memiliki kolom status penjualan
$kolom_ml = [];
$res_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM ml");
if ($res_kolom) {
    while ($col = mysqli_fetch_assoc($res_kolom)) {
        $kolom_ml[] = strtolower($col['Field']);
    }
}

$punya_kolom_status  = in_array('status', $kolom_ml, true);
$punya_kolom_terjual = in_array('terjual', $kolom_ml, true);

// Proses Reset Perhitungan
if (isset($_GET['reset_statistik']) && $_GET['reset_statistik'] === '1') {
    if ($punya_kolom_status) {
        mysqli_query($koneksi, "UPDATE ml SET status = 'tersedia' WHERE LOWER(TRIM(status)) IN ('terjual', 'sold', 'laku', '1')");
    } elseif ($punya_kolom_terjual) {
        mysqli_query($koneksi, "UPDATE ml SET terjual = 0 WHERE LOWER(TRIM(terjual)) IN ('1', 'ya', 'yes', 'true', 'terjual')");
    } else {
        mysqli_query($koneksi, "TRUNCATE TABLE ml");
    }

    echo "<script>alert('Perhitungan statistik berhasil direset!'); window.location='mlbb.php';</script>";
    exit;
}

$total_tersedia = 0;
$total_terjual  = 0;
$akun_terjual   = [];
$data_stok      = [];

$res_all = mysqli_query($koneksi, "SELECT * FROM ml ORDER BY id_ml DESC");
if ($res_all && mysqli_num_rows($res_all) > 0) {
    while ($item = mysqli_fetch_assoc($res_all)) {
        $sudah_terjual = false;

        if ($punya_kolom_status) {
            $val_status = strtolower(trim((string) ($item['status'] ?? '')));
            if (in_array($val_status, ['terjual', 'sold', 'laku', '1'], true)) {
                $sudah_terjual = true;
            }
        } elseif ($punya_kolom_terjual) {
            $val_terjual = strtolower(trim((string) ($item['terjual'] ?? '')));
            if (in_array($val_terjual, ['1', 'ya', 'yes', 'true', 'terjual'], true)) {
                $sudah_terjual = true;
            }
        }

        if ($sudah_terjual) {
            $total_terjual++;
            $akun_terjual[] = $item;
        } else {
            $total_tersedia++;
            $data_stok[] = $item;
        }
    }
}

// Hitung jenis akun (rank) paling banyak terjual & rentang harga terjual
$frekuensi_rank = [];
$frekuensi_rentang_harga = [
    '< Rp 100.000'            => 0,
    'Rp 100.000 - Rp 250.000' => 0,
    'Rp 250.001 - Rp 500.000' => 0,
    '> Rp 500.000'            => 0,
];

foreach ($akun_terjual as $sold) {
    $rank_nama = trim((string) ($sold['rank'] ?? 'Tidak Diketahui'));
    if ($rank_nama === '') {
        $rank_nama = 'Tidak Diketahui';
    }
    $frekuensi_rank[$rank_nama] = ($frekuensi_rank[$rank_nama] ?? 0) + 1;

    $harga_jual = (float) ($sold['harga'] ?? 0);
    if ($harga_jual < 100000) {
        $frekuensi_rentang_harga['< Rp 100.000']++;
    } elseif ($harga_jual <= 250000) {
        $frekuensi_rentang_harga['Rp 100.000 - Rp 250.000']++;
    } elseif ($harga_jual <= 500000) {
        $frekuensi_rentang_harga['Rp 250.001 - Rp 500.000']++;
    } else {
        $frekuensi_rentang_harga['> Rp 500.000']++;
    }
}

arsort($frekuensi_rank);
$rank_terlaris_list = [];
if (!empty($frekuensi_rank)) {
    $max_rank = max($frekuensi_rank);
    foreach ($frekuensi_rank as $r_name => $r_count) {
        if ($r_count === $max_rank) {
            $rank_terlaris_list[] = $r_name . " (" . $r_count . ")";
        }
    }
}

$rentang_terlaris_list = [];
if ($total_terjual > 0) {
    $max_rentang = max($frekuensi_rentang_harga);
    if ($max_rentang > 0) {
        foreach ($frekuensi_rentang_harga as $range_label => $range_count) {
            if ($range_count === $max_rentang) {
                $rentang_terlaris_list[] = $range_label . " (" . $range_count . ")";
            }
        }
    }
}

$teks_rank_terlaris    = !empty($rank_terlaris_list) ? implode(', ', $rank_terlaris_list) : 'Belum ada data terjual';
$teks_rentang_terlaris = !empty($rentang_terlaris_list) ? implode(', ', $rentang_terlaris_list) : 'Belum ada data terjual';

// Proses Export ke .xls
if (isset($_GET['export']) && $_GET['export'] === 'xls') {
    $filename = "statistik_mlbb_" . date("Y-m-d") . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF";
    echo "<table border='1'>";
    echo "<tr><th colspan='2'>RINGKASAN STATISTIK MLBB</th></tr>";
    echo "<tr><td>Akun Tersedia</td><td>" . (int) $total_tersedia . "</td></tr>";
    echo "<tr><td>Akun Terjual</td><td>" . (int) $total_terjual . "</td></tr>";
    echo "<tr><td>Jenis Akun Paling Laku</td><td>" . htmlspecialchars($teks_rank_terlaris, ENT_QUOTES, 'UTF-8') . "</td></tr>";
    echo "<tr><td>Rentang Harga Sering Laku</td><td>" . htmlspecialchars($teks_rentang_terlaris, ENT_QUOTES, 'UTF-8') . "</td></tr>";
    echo "</table><br>";

    echo "<table border='1'>";
    echo "<tr><th colspan='2'>DISTRIBUSI RENTANG HARGA TERJUAL</th></tr>";
    echo "<tr><th>Rentang Harga</th><th>Jumlah Terjual</th></tr>";
    foreach ($frekuensi_rentang_harga as $label_range => $jml_range) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($label_range, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . (int) $jml_range . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";

    echo "<table border='1'>";
    echo "<tr><th colspan='5'>DETAIL DATA AKUN MLBB</th></tr>";
    echo "<tr><th>ID</th><th>Judul</th><th>Rank</th><th>Jumlah Skin</th><th>Harga (Rp)</th></tr>";

    $res_xls = mysqli_query($koneksi, "SELECT id_ml, judul, rank, jumlah_skin, harga FROM ml ORDER BY id_ml DESC");
    if ($res_xls) {
        while ($d = mysqli_fetch_assoc($res_xls)) {
            echo "<tr>";
            echo "<td>" . (int) $d['id_ml'] . "</td>";
            echo "<td>" . htmlspecialchars($d['judul'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars($d['rank'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . (int) $d['jumlah_skin'] . "</td>";
            echo "<td>" . (float) $d['harga'] . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../bin/logo/logo.png">
    <title>AMP STORE - Stok MLBB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../style.css" rel="stylesheet">
    <style>
        .poster-stok {
            height: 190px;
            width: 100%;
            object-fit: cover;
        }
        .card-stok {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-stok:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.14) !important;
        }
        .card-link-wrapper {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }
        .stat-card {
            border-radius: 1rem;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-amp sticky-top" id="navbar">
    <div class="container-fluid px-4">

        <!-- LOGO DI KIRI -->
        <a href="../index.php" class="navbar-brand logo-link">
            <img src="../../bin/logo/logo.png" alt="AMP STORE" class="logo-amp">
        </a>
        <h1 id="namanav">ADMIN AMP STORE</h1>

        <!-- Tombol toggler (kanan) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Panel menu -->
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <form action="" method="POST" class="d-inline">
                        <button type="submit" name="home" class="nav-link">Home</button>
                    </form>
                </li>
                <li class="nav-item">
                    <form action="" method="POST" class="d-inline">
                        <button type="submit" name="logout" id="logout" class="btn btn-logout">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- STATISTIK & DAFTAR STOK CARD DINAMIS -->
<div class="container my-5">

    <!-- Header Aksi Export & Reset -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="fw-bold mb-0">Statistik Penjualan MLBB</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="?export=xls" class="btn btn-success btn-sm fw-semibold">
                Export ke .xls
            </a>
            <a href="?reset_statistik=1" class="btn btn-outline-danger btn-sm fw-semibold" onclick="return confirm('Yakin ingin mereset perhitungan statistik ini?')">
                Reset Perhitungan
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mb-4">
        <div class="col">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Akun Tersedia</div>
                    <div class="fs-3 fw-bold text-primary mt-1"><?= $total_tersedia; ?></div>
                    <div class="small text-muted">Siap dijual</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Akun Terjual</div>
                    <div class="fs-3 fw-bold text-success mt-1"><?= $total_terjual; ?></div>
                    <div class="small text-muted">Total terjual</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Jenis Akun Paling Laku</div>
                    <div class="fs-6 fw-bold text-dark mt-2">
                        <?= htmlspecialchars($teks_rank_terlaris, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="small text-muted mt-1">Berdasarkan rank akun terjual</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small fw-semibold">Rentang Harga Sering Laku</div>
                    <div class="fs-6 fw-bold text-dark mt-2">
                        <?= htmlspecialchars($teks_rentang_terlaris, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="small text-muted mt-1">Berdasarkan harga terjual</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rincian Rentang Harga Terjual -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold small text-muted">Distribusi Rentang Harga Terjual:</span>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($frekuensi_rentang_harga as $label_range => $jml_range): ?>
                        <span class="badge bg-light text-dark border px-3 py-2">
                            <?= htmlspecialchars($label_range, ENT_QUOTES, 'UTF-8'); ?>: <strong><?= (int) $jml_range; ?></strong>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Card Stok Tersedia -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        if (!empty($data_stok)) {
            foreach ($data_stok as $row) {
                $id_row     = (int) $row['id_ml'];
                $raw_gambar = $row['gambar'] ?? '';

                $daftar_file = json_decode($raw_gambar, true);
                if (!is_array($daftar_file)) {
                    $daftar_file = !empty($raw_gambar) ? [$raw_gambar] : [];
                }

                // Foto pertama sebagai poster card
                $foto_pertama = $daftar_file[0] ?? '';
                $gambar_aman  = htmlspecialchars(basename((string) $foto_pertama), ENT_QUOTES, 'UTF-8');
                $jumlah_foto  = count($daftar_file);
                $src_poster   = $gambar_aman !== ''
                    ? "../../bin/img_stok/mlbb/" . $gambar_aman
                    : "../../bin/logo/logo.png";
        ?>
        <div class="col">
            <a href="../../item/mlbb/mlbb.php?id=<?= $id_row; ?>" class="card-link-wrapper">
                <div class="card card-stok h-100 border rounded-4 overflow-hidden shadow-sm">
                    <div class="position-relative">
                        <img src="<?= $src_poster; ?>" class="card-img-top poster-stok" alt="<?= htmlspecialchars($row['judul'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($jumlah_foto > 1): ?>
                            <span class="badge bg-dark position-absolute top-0 end-0 m-2">+<?= $jumlah_foto - 1; ?> foto</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-info text-dark">Rank: <?= htmlspecialchars($row['rank'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <h6 class="card-title fw-bold mb-2">
                            <?= htmlspecialchars($row['judul'], ENT_QUOTES, 'UTF-8'); ?>
                        </h6>
                        <div class="fw-bold text-success fs-6 mt-auto">
                            Rp <?= number_format((float) $row['harga'], 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php
            }
        } else {
            echo "<div class='col-12'><div class='text-center text-muted py-5'>Stok kosong</div></div>";
        }
        ?>
    </div>
</div>

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