<?php
session_start();
require_once "../../bin/database.php";

$username = $_SESSION['username'] ?? null;

// Deteksi role dari session (mendukung 'role', 'level', atau 'status_user')
$raw_role = $_SESSION['role'] ?? ($_SESSION['level'] ?? ($_SESSION['status_user'] ?? ''));
$role     = strtolower(trim((string) $raw_role));

// Tentukan tujuan halaman Home berdasarkan role
if ($role === 'admin') {
    $home_url = "../../admin/index.php";
} elseif ($role === 'pelanggan') {
    $home_url = "../../pelanggan/index.php";
} else {
    $home_url = "../../index.php";
}

if (isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ../../index.php");
    exit;
}

if (isset($_POST['home'])) {
    header("Location: " . $home_url);
    exit;
}

$id_akun = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$akun = null;
if ($id_akun > 0) {
    $query_detail = mysqli_query($koneksi, "SELECT * FROM ml WHERE id_ml = $id_akun LIMIT 1");
    if ($query_detail && mysqli_num_rows($query_detail) > 0) {
        $akun = mysqli_fetch_assoc($query_detail);
    }
}

// Proses konfirmasi pembelian sederhana
$pesan_sukses = null;
$pesan_error  = null;

if (isset($_POST['proses_beli']) && $akun) {
    $nama_pembeli = trim($_POST['nama_pembeli'] ?? '');
    $kontak_wa    = trim($_POST['kontak_wa'] ?? '');
    $metode_bayar = trim($_POST['metode_bayar'] ?? '');

    if ($nama_pembeli === '' || $kontak_wa === '' || $metode_bayar === '') {
        $pesan_error = "Silakan lengkapi nama, nomor WhatsApp, dan metode pembayaran terlebih dahulu.";
    } else {
        $pesan_sukses = [
            'kode_order' => 'AMP-ML-' . $akun['id_ml'] . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 5)),
            'nama'       => $nama_pembeli,
            'wa'         => preg_replace('/[^0-9]/', '', $kontak_wa),
            'metode'     => $metode_bayar,
            'judul'      => $akun['judul'],
            'harga'      => (float) $akun['harga'],
        ];
    }
}

$daftar_file = [];
if ($akun) {
    $raw_gambar = $akun['gambar'] ?? '';
    $decoded    = json_decode($raw_gambar, true);
    if (is_array($decoded)) {
        $daftar_file = $decoded;
    } elseif (!empty($raw_gambar)) {
        $daftar_file = [$raw_gambar];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/Amp-Store/bin/logo/logo.png">
    <title><?= $akun ? htmlspecialchars($akun['judul'], ENT_QUOTES, 'UTF-8') . ' - ' : ''; ?>AMP STORE MLBB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../style.css" rel="stylesheet">
    <style>
        .foto-utama-detail {
            width: 100%;
            height: 360px;
            object-fit: cover;
        }
        .thumb-galeri {
            width: 100%;
            height: 82px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }
        .thumb-galeri:hover {
            transform: scale(1.03);
            opacity: 0.9;
        }
        .poster-katalog {
            height: 185px;
            width: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-amp sticky-top" id="navbar">
    <div class="container-fluid px-4">
        <a href="<?= htmlspecialchars($home_url, ENT_QUOTES, 'UTF-8'); ?>" class="navbar-brand logo-link">
            <img src="/Amp-Store/bin/logo/logo.png" alt="AMP STORE" class="logo-amp">
        </a>
        <h1 id="namanav">AMP STORE</h1>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

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
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <?php if ($akun): ?>
        <?php
        $foto_pertama = htmlspecialchars(basename((string) ($daftar_file[0] ?? '')), ENT_QUOTES, 'UTF-8');
        $src_utama    = $foto_pertama !== ''
            ? "../../bin/img_stok/mlbb/" . $foto_pertama
            : "/Amp-Store/bin/logo/logo.png";

        $teks_wa = urlencode(
            "Halo AMP STORE, saya ingin membeli akun MLBB:\n" .
            "• Judul: " . $akun['judul'] . "\n" .
            "• Rank: " . $akun['rank'] . "\n" .
            "• Harga: Rp " . number_format((float) $akun['harga'], 0, ',', '.')
        );
        $link_wa = "https://wa.me/6281234567890?text=" . $teks_wa;
        ?>

        <div class="mb-3">
            <a href="mlbb.php" class="btn btn-outline-secondary btn-sm">&larr; Lihat Semua Akun MLBB</a>
        </div>

        <?php if ($pesan_error): ?>
            <div class="alert alert-danger rounded-3"><?= htmlspecialchars($pesan_error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success rounded-4 p-4 mb-4 shadow-sm">
                <h5 class="fw-bold mb-2">Pesanan Berhasil Dibuat!</h5>
                <p class="mb-1"><strong>Kode Pesanan:</strong> <?= htmlspecialchars($pesan_sukses['kode_order'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-1"><strong>Akun:</strong> <?= htmlspecialchars($pesan_sukses['judul'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-1"><strong>Pembeli:</strong> <?= htmlspecialchars($pesan_sukses['nama'], ENT_QUOTES, 'UTF-8'); ?> (<?= htmlspecialchars($pesan_sukses['wa'], ENT_QUOTES, 'UTF-8'); ?>)</p>
                <p class="mb-1"><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($pesan_sukses['metode'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-3"><strong>Total Bayar:</strong> Rp <?= number_format($pesan_sukses['harga'], 0, ',', '.'); ?></p>
                <a href="<?= $link_wa; ?>" target="_blank" rel="noopener" class="btn btn-success fw-bold">
                    Konfirmasi Pembayaran via WhatsApp
                </a>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Kolom Kiri: Galeri & Detail Item -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="p-4 bg-light border-bottom">
                        <img id="gambarPreview" src="<?= $src_utama; ?>" alt="<?= htmlspecialchars($akun['judul'], ENT_QUOTES, 'UTF-8'); ?>" class="foto-utama-detail rounded-3 border">

                        <?php if (count($daftar_file) > 1): ?>
                            <div class="row row-cols-4 row-cols-sm-5 g-2 mt-3">
                                <?php foreach ($daftar_file as $file):
                                    $file_aman = htmlspecialchars(basename((string) $file), ENT_QUOTES, 'UTF-8');
                                    if ($file_aman === '') continue;
                                    $url_file = "../../bin/img_stok/mlbb/" . $file_aman;
                                ?>
                                <div class="col">
                                    <img src="<?= $url_file; ?>" alt="Foto Akun" class="thumb-galeri rounded-2 border" onclick="document.getElementById('gambarPreview').src='<?= $url_file; ?>'">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-4">
                        <div class="mb-2">
                            <span class="badge bg-info text-dark">Rank: <?= htmlspecialchars($akun['rank'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge bg-secondary">Jumlah Skin: <?= (int) $akun['jumlah_skin']; ?></span>
                        </div>

                        <h3 class="fw-bold mb-2"><?= htmlspecialchars($akun['judul'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="fs-4 fw-bold text-success mb-4">
                            Rp <?= number_format((float) $akun['harga'], 0, ',', '.'); ?>
                        </div>

                        <h6 class="fw-bold">Deskripsi & Spesifikasi Akun</h6>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($akun['deskripsi'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Panel Pembelian -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-dark text-white fw-bold py-3 rounded-top-4">
                        Pembelian Akun
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                            <span class="text-muted">Harga Akun</span>
                            <span class="fs-5 fw-bold text-success">Rp <?= number_format((float) $akun['harga'], 0, ',', '.'); ?></span>
                        </div>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Lengkap / Nickname</label>
                                <input type="text" name="nama_pembeli" class="form-control" placeholder="Masukkan nama kamu" value="<?= htmlspecialchars($_POST['nama_pembeli'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nomor WhatsApp</label>
                                <input type="text" name="kontak_wa" class="form-control" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($_POST['kontak_wa'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Metode Pembayaran</label>
                                <select name="metode_bayar" class="form-select" required>
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    <option value="QRIS" <?= (($_POST['metode_bayar'] ?? '') === 'QRIS') ? 'selected' : ''; ?>>QRIS</option>
                                    <option value="Transfer Bank" <?= (($_POST['metode_bayar'] ?? '') === 'Transfer Bank') ? 'selected' : ''; ?>>Transfer Bank</option>
                                    <option value="E-Wallet (DANA / OVO / GoPay)" <?= (($_POST['metode_bayar'] ?? '') === 'E-Wallet (DANA / OVO / GoPay)') ? 'selected' : ''; ?>>E-Wallet (DANA / OVO / GoPay)</option>
                                </select>
                            </div>

                            <button type="submit" name="proses_beli" class="btn btn-primary w-100 fw-bold py-2 mb-2">
                                Beli Sekarang
                            </button>
                            <a href="<?= $link_wa; ?>" target="_blank" rel="noopener" class="btn btn-outline-success w-100 fw-semibold py-2">
                                Chat Admin via WhatsApp
                            </a>
                        </form>

                        <p class="small text-muted mt-3 mb-0">
                            Data login akun (email &amp; password) akan diberikan oleh admin setelah pembayaran terkonfirmasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Tampilan jika ?id= tidak disertakan atau tidak ditemukan -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Pilih Akun MLBB</h4>
            <?php if ($id_akun > 0): ?>
                <span class="badge bg-warning text-dark">ID akun tidak ditemukan</span>
            <?php endif; ?>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php
            $list_akun = mysqli_query($koneksi, "SELECT * FROM ml ORDER BY id_ml DESC");
            if ($list_akun && mysqli_num_rows($list_akun) > 0) {
                while ($item = mysqli_fetch_assoc($list_akun)) {
                    $id_item   = (int) $item['id_ml'];
                    $raw_img   = $item['gambar'] ?? '';
                    $arr_img   = json_decode($raw_img, true);
                    if (!is_array($arr_img)) {
                        $arr_img = !empty($raw_img) ? [$raw_img] : [];
                    }
                    $img_first = htmlspecialchars(basename((string) ($arr_img[0] ?? '')), ENT_QUOTES, 'UTF-8');
                    $src_item  = $img_first !== ''
                        ? "../../bin/img_stok/mlbb/" . $img_first
                        : "/Amp-Store/bin/logo/logo.png";
            ?>
            <div class="col">
                <a href="mlbb.php?id=<?= $id_item; ?>" class="text-decoration-none text-dark d-block h-100">
                    <div class="card h-100 border rounded-4 overflow-hidden shadow-sm">
                        <img src="<?= $src_item; ?>" class="card-img-top poster-katalog" alt="<?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-info text-dark align-self-start mb-2">Rank: <?= htmlspecialchars($item['rank'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h6 class="card-title fw-bold mb-2"><?= htmlspecialchars($item['judul'], ENT_QUOTES, 'UTF-8'); ?></h6>
                            <div class="fw-bold text-success mt-auto">Rp <?= number_format((float) $item['harga'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </a>
            </div>
            <?php
                }
            } else {
                echo "<div class='col-12'><div class='text-center text-muted py-5'>Belum ada stok akun MLBB tersedia.</div></div>";
            }
            ?>
        </div>
    <?php endif; ?>
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