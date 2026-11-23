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

// Deteksi kolom status pada tabel ml jika ada
$kolom_ml = [];
$res_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM ml");
if ($res_kolom) {
    while ($col = mysqli_fetch_assoc($res_kolom)) {
        $kolom_ml[] = strtolower($col['Field']);
    }
}
$punya_kolom_status  = in_array('status', $kolom_ml, true);
$punya_kolom_terjual = in_array('terjual', $kolom_ml, true);

// Ambil nilai filter dari GET
$cari_judul  = trim((string) ($_GET['cari'] ?? ''));
$filter_rank = trim((string) ($_GET['rank'] ?? ''));

$harga_min_raw = trim((string) ($_GET['harga_min'] ?? ''));
$harga_max_raw = trim((string) ($_GET['harga_max'] ?? ''));
$skin_min_raw  = trim((string) ($_GET['skin_min'] ?? ''));
$skin_max_raw  = trim((string) ($_GET['skin_max'] ?? ''));

$harga_min = ($harga_min_raw !== '' && is_numeric($harga_min_raw)) ? max(0, (int) $harga_min_raw) : null;
$harga_max = ($harga_max_raw !== '' && is_numeric($harga_max_raw)) ? max(0, (int) $harga_max_raw) : null;
$skin_min  = ($skin_min_raw !== '' && is_numeric($skin_min_raw)) ? max(0, (int) $skin_min_raw) : null;
$skin_max  = ($skin_max_raw !== '' && is_numeric($skin_max_raw)) ? max(0, (int) $skin_max_raw) : null;

$where_clauses = [];

if ($punya_kolom_status) {
    $where_clauses[] = "LOWER(TRIM(COALESCE(status, 'tersedia'))) NOT IN ('terjual', 'sold', 'laku', '1')";
} elseif ($punya_kolom_terjual) {
    $where_clauses[] = "LOWER(TRIM(COALESCE(terjual, '0'))) NOT IN ('1', 'ya', 'yes', 'true', 'terjual')";
}

if ($cari_judul !== '') {
    $cari_esc = mysqli_real_escape_string($koneksi, $cari_judul);
    $where_clauses[] = "judul LIKE '%$cari_esc%'";
}

if ($filter_rank !== '') {
    $rank_esc = mysqli_real_escape_string($koneksi, $filter_rank);
    $where_clauses[] = "rank = '$rank_esc'";
}

if ($harga_min !== null) {
    $where_clauses[] = "harga >= " . $harga_min;
}
if ($harga_max !== null) {
    $where_clauses[] = "harga <= " . $harga_max;
}

if ($skin_min !== null) {
    $where_clauses[] = "jumlah_skin >= " . $skin_min;
}
if ($skin_max !== null) {
    $where_clauses[] = "jumlah_skin <= " . $skin_max;
}

$sql = "SELECT * FROM ml";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY id_ml DESC";

$data = mysqli_query($koneksi, $sql);

$ada_filter_aktif = (
    $cari_judul !== '' ||
    $filter_rank !== '' ||
    $harga_min !== null ||
    $harga_max !== null ||
    $skin_min !== null ||
    $skin_max !== null
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../../bin/logo/logo.png">
    <title>AMP STORE - Katalog Akun MLBB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../style.css" rel="stylesheet">
    <style>
        .poster-stok {
            height: 195px;
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
        <h1 id="namanav">AMP STORE</h1>

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
                <?php if ($username): ?>
                <li class="nav-item">
                    <form action="" method="POST" class="d-inline">
                        <button type="submit" name="logout" id="logout" class="btn btn-logout">Logout</button>
                    </form>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a href="../../login.php" class="btn btn-primary btn-sm fw-semibold">Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- KONTEN KATALOG AKUN MLBB PELANGGAN -->
<div class="container my-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Akun Mobile Legends (MLBB)</h3>
            <p class="text-muted small mb-0">Pilih akun untuk melihat detail lengkap, galeri foto, dan melakukan pembelian.</p>
        </div>
    </div>

    <!-- Form Filter: Jenis Akun (Dropdown), Rentang Harga, & Rentang Jumlah Skin -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-3">
                    <label class="form-label small fw-semibold">Cari Judul Akun</label>
                    <input type="text" name="cari" class="form-control" placeholder="Contoh: Sultan Ex Mythic" value="<?= htmlspecialchars($cari_judul, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-md-6 col-lg-3">
                    <label class="form-label small fw-semibold">Jenis Akun (Rank)</label>
                    <select name="rank" class="form-select">
                        <option value="">-- Semua Jenis Akun --</option>
                        <?php
                        $daftar_rank = ['Epic', 'Legend', 'Mythic', 'Mythical Honor', 'Mythical Glory', 'Mythical Immortal'];
                        foreach ($daftar_rank as $r_opt):
                            $selected = ($filter_rank === $r_opt) ? 'selected' : '';
                        ?>
                            <option value="<?= htmlspecialchars($r_opt, ENT_QUOTES, 'UTF-8'); ?>" <?= $selected; ?>>
                                <?= htmlspecialchars($r_opt, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label class="form-label small fw-semibold">Rentang Harga (Rp)</label>
                    <div class="input-group">
                        <input type="number" name="harga_min" class="form-control" min="0" step="1000" placeholder="Min" value="<?= $harga_min !== null ? $harga_min : ''; ?>">
                        <span class="input-group-text">-</span>
                        <input type="number" name="harga_max" class="form-control" min="0" step="1000" placeholder="Max" value="<?= $harga_max !== null ? $harga_max : ''; ?>">
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <label class="form-label small fw-semibold">Rentang Jumlah Skin</label>
                    <div class="input-group">
                        <input type="number" name="skin_min" class="form-control" min="0" placeholder="Min" value="<?= $skin_min !== null ? $skin_min : ''; ?>">
                        <span class="input-group-text">-</span>
                        <input type="number" name="skin_max" class="form-control" min="0" placeholder="Max" value="<?= $skin_max !== null ? $skin_max : ''; ?>">
                    </div>
                </div>

                <div class="col-12 d-flex flex-wrap justify-content-end gap-2 pt-1">
                    <?php if ($ada_filter_aktif): ?>
                        <a href="mlbb.php" class="btn btn-outline-secondary fw-semibold">Reset Filter</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Card -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        if ($data && mysqli_num_rows($data) > 0) {
            while ($row = mysqli_fetch_assoc($data)) {
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
                            <span class="badge bg-secondary">Skin: <?= (int) $row['jumlah_skin']; ?></span>
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
            echo "<div class='col-12'><div class='text-center text-muted py-5'>Tidak ada akun MLBB yang sesuai dengan filter.</div></div>";
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>