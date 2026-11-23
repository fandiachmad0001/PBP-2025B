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

$folder_upload = "../../bin/img_stok/mlbb/";
if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0777, true);
}

// Proses Tambah Stok Akun (Multi-gambar)
if (isset($_POST["tambah_stok"])) {
    $judul       = mysqli_real_escape_string($koneksi, trim($_POST['judul']));
    $rank        = mysqli_real_escape_string($koneksi, trim($_POST['rank']));
    $jumlah_skin = (int) $_POST['jumlah_skin'];
    $harga       = (int) $_POST['harga'];
    $email       = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $password    = mysqli_real_escape_string($koneksi, $_POST['password']);
    $deskripsi   = mysqli_real_escape_string($koneksi, trim($_POST['deskripsi']));

    $allowed_ext    = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $uploaded_files = [];
    $uploaded_paths = [];

    if (!empty($_FILES['gambar']['name'][0])) {
        $total_files = count($_FILES['gambar']['name']);

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gambar']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $nama_asli = basename($_FILES['gambar']['name'][$i]);
            $tmp_file  = $_FILES['gambar']['tmp_name'][$i];
            $ekstensi  = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));

            if (!in_array($ekstensi, $allowed_ext, true)) {
                foreach ($uploaded_paths as $path_batal) {
                    if (file_exists($path_batal)) {
                        unlink($path_batal);
                    }
                }
                echo "<script>alert('Semua file harus berupa gambar (JPG, JPEG, PNG, WEBP, GIF)!'); window.location='mlbb.php';</script>";
                exit;
            }

            $nama_file_baru = time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $nama_asli);
            $path_tujuan    = $folder_upload . $nama_file_baru;

            if (move_uploaded_file($tmp_file, $path_tujuan)) {
                $uploaded_files[] = $nama_file_baru;
                $uploaded_paths[] = $path_tujuan;
            }
        }
    }

    if (!empty($uploaded_files)) {
        $gambar_json = mysqli_real_escape_string($koneksi, json_encode($uploaded_files));

        $query = "INSERT INTO ml (judul, rank, jumlah_skin, harga, email, password, deskripsi, gambar) 
                  VALUES ('$judul', '$rank', '$jumlah_skin', '$harga', '$email', '$password', '$deskripsi', '$gambar_json')";

        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Stok akun dan " . count($uploaded_files) . " gambar berhasil ditambahkan!'); window.location='mlbb.php';</script>";
            exit;
        } else {
            foreach ($uploaded_paths as $path_batal) {
                if (file_exists($path_batal)) {
                    unlink($path_batal);
                }
            }
            echo "<script>alert('Gagal menyimpan data: " . addslashes(mysqli_error($koneksi)) . "'); window.location='mlbb.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Gagal mengupload gambar! Silakan pilih minimal 1 gambar.'); window.location='mlbb.php';</script>";
        exit;
    }
}

// Proses Hapus Stok (Hapus semua gambar terkait)
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];

    if ($id_hapus > 0) {
        $cek = mysqli_query($koneksi, "SELECT gambar FROM ml WHERE id_ml = $id_hapus LIMIT 1");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $row_gambar = mysqli_fetch_assoc($cek);
            $raw_gambar = $row_gambar['gambar'] ?? '';

            $daftar_file = json_decode($raw_gambar, true);
            if (!is_array($daftar_file)) {
                $daftar_file = !empty($raw_gambar) ? [$raw_gambar] : [];
            }

            foreach ($daftar_file as $nama_file) {
                $file_lama = $folder_upload . basename((string) $nama_file);
                if (!empty($nama_file) && file_exists($file_lama)) {
                    unlink($file_lama);
                }
            }
        }

        if (mysqli_query($koneksi, "DELETE FROM ml WHERE id_ml = $id_hapus")) {
            echo "<script>alert('Stok berhasil dihapus!'); window.location='mlbb.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal menghapus stok: " . addslashes(mysqli_error($koneksi)) . "'); window.location='mlbb.php';</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/Amp-Store/bin/logo/logo.png">
    <title>AMP STORE - Admin MLBB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../style.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-amp sticky-top" id="navbar">
    <div class="container-fluid px-4">

        <!-- LOGO DI KIRI -->
        <a href="../index.php" class="navbar-brand logo-link">
            <img src="/Amp-Store/bin/logo/logo.png" alt="AMP STORE" class="logo-amp">
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

<!-- KONTEN TAMBAH & DAFTAR STOK -->
<div class="container my-5">
    <div class="row g-4">
        <!-- Form Tambah Stok -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-dark text-white fw-bold py-3 rounded-top-4">
                    + Tambah Stok Akun
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul Akun</label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Akun Sultan Ex Mythic" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rank</label>
                            <select name="rank" class="form-select" required>
                                <option value="">-- Pilih Rank --</option>
                                <option value="Epic">Epic</option>
                                <option value="Legend">Legend</option>
                                <option value="Mythic">Mythic</option>
                                <option value="Mythical Honor">Mythical Honor</option>
                                <option value="Mythical Glory">Mythical Glory</option>
                                <option value="Mythical Immortal">Mythical Immortal</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jumlah Skin</label>
                            <input type="number" name="jumlah_skin" class="form-control" min="0" placeholder="Contoh: 120" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Harga (Rp)</label>
                            <input type="number" name="harga" class="form-control" min="0" placeholder="Contoh: 200000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Akun</label>
                            <input type="email" name="email" class="form-control" placeholder="Contoh: akunml@gmail.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Akun</label>
                            <input type="text" name="password" class="form-control" placeholder="Masukkan password akun" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Gambar Akun (Bisa pilih lebih dari 1)</label>
                            <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Detail akun..." required></textarea>
                        </div>
                        <button type="submit" name="tambah_stok" class="btn btn-primary w-100 fw-bold">Simpan Stok</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Stok -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-dark text-white fw-bold py-3 rounded-top-4">
                    Daftar Stok Akun
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Gambar</th>
                                    <th>Detail Akun</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $data = mysqli_query($koneksi, "SELECT * FROM ml ORDER BY id_ml DESC");
                                if ($data && mysqli_num_rows($data) > 0) {
                                    while ($row = mysqli_fetch_assoc($data)) {
                                        $id_row     = (int) $row['id_ml'];
                                        $raw_gambar = $row['gambar'] ?? '';

                                        $daftar_file = json_decode($raw_gambar, true);
                                        if (!is_array($daftar_file)) {
                                            $daftar_file = !empty($raw_gambar) ? [$raw_gambar] : [];
                                        }

                                        $foto_pertama = $daftar_file[0] ?? '';
                                        $gambar_aman  = htmlspecialchars(basename((string) $foto_pertama), ENT_QUOTES, 'UTF-8');
                                        $jumlah_foto  = count($daftar_file);
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <?php if ($gambar_aman !== ''): ?>
                                            <img src="../../bin/img_stok/mlbb/<?= $gambar_aman; ?>" alt="Akun" width="80" class="rounded">
                                            <?php if ($jumlah_foto > 1): ?>
                                                <div class="badge bg-dark mt-1">+<?= $jumlah_foto - 1; ?> foto</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">Tidak ada foto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['judul'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                        <span class="badge bg-info text-dark">Rank: <?= htmlspecialchars($row['rank'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="badge bg-secondary">Skin: <?= (int) $row['jumlah_skin']; ?></span>
                                        <div class="small mt-1">
                                            <div><strong>Email:</strong> <?= htmlspecialchars($row['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div><strong>Password:</strong> <?= htmlspecialchars($row['password'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                        <p class="small text-muted mb-0 mt-1"><?= nl2br(htmlspecialchars($row['deskripsi'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    </td>
                                    <td class="fw-bold text-success">
                                        Rp <?= number_format((float) $row['harga'], 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <a href="?hapus=<?= $id_row; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus stok ini?')">Hapus</a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center text-muted py-4'>Stok kosong</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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