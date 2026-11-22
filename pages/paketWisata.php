<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();

list($w, $t, $p) = bangunFilter(['nama_paket', 'lokasi', 'deskripsi', 'fasilitas'], 'harga_per_orang', 'kategori');
$sql = "SELECT * FROM paket_wisata" . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY ' . urutan('harga_per_orang');
$daftar = qAll($koneksi, $sql, $t, $p);
$lokasiList = array_column(qAll($koneksi, "SELECT DISTINCT lokasi FROM paket_wisata ORDER BY lokasi"), 'lokasi');
$kategoriList = qAll($koneksi, "SELECT kategori AS nama, COUNT(*) AS n FROM paket_wisata WHERE status = 'tersedia' AND kategori IS NOT NULL AND kategori <> '' GROUP BY kategori ORDER BY kategori");
$fav = ambilWishlist($koneksi, 'paket');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Wisata - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php navbarHalaman('paket', 'JogjaYuk'); ?>

    <main class="halaman-katalog">
        <div class="judul-katalog">
            <p class="slogan">Jelajahi Keindahan Jogja</p>
            <h1>Pilihan Paket Wisata Jogja</h1>
            <p>Temukan pengalaman liburan seru dan kunjungi berbagai destinasi menarik di Yogyakarta.</p>
        </div>

        <?php tampilFlash(); ?>
        <?php barKategori($kategoriList); ?>
        <?php formFilter($lokasiList); ?>

        <section class="grid-produk">
            <?php if ($daftar): ?>
                <?php foreach ($daftar as $row): kartuProduk('paket', $row, $fav); endforeach; ?>
            <?php else: ?>
                <p>Tidak ada paket wisata yang sesuai dengan pencarianmu.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php footerHalaman(); ?>
</body>
</html>
