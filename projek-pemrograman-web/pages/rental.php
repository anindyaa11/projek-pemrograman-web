<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();

list($w, $t, $p) = bangunFilter(['nama_kendaraan', 'jenis_kendaraan', 'lokasi', 'deskripsi'], 'harga_per_hari', 'jenis_kendaraan');
$sql = "SELECT * FROM rental" . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY ' . urutan('harga_per_hari');
$daftar = qAll($koneksi, $sql, $t, $p);
$lokasiList = array_column(qAll($koneksi, "SELECT DISTINCT lokasi FROM rental ORDER BY lokasi"), 'lokasi');
$kategoriList = qAll($koneksi, "SELECT jenis_kendaraan AS nama, COUNT(*) AS n FROM rental WHERE status = 'tersedia' GROUP BY jenis_kendaraan ORDER BY jenis_kendaraan");
$fav = ambilWishlist($koneksi, 'rental');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Kendaraan - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php navbarHalaman('rental'); ?>

    <main class="halaman-katalog">
        <div class="judul-katalog">
            <p class="slogan">Jelajahi Jogja dengan mudah</p>
            <h1>Pilihan Rental Kendaraan</h1>
            <p>Temukan kendaraan yang sesuai untuk menemani perjalananmu menjelajahi Yogyakarta.</p>
        </div>

        <?php tampilFlash(); ?>
        <?php barKategori($kategoriList); ?>
        <?php formFilter($lokasiList); ?>

        <section class="grid-produk">
            <?php if ($daftar): ?>
                <?php foreach ($daftar as $row): kartuProduk('rental', $row, $fav); endforeach; ?>
            <?php else: ?>
                <p>Tidak ada kendaraan yang sesuai dengan pencarianmu.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php footerHalaman(); ?>
</body>
</html>
