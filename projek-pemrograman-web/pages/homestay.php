<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();

list($w, $t, $p) = bangunFilter(['nama_homestay', 'lokasi', 'deskripsi', 'fasilitas'], 'harga_per_malam', 'kategori');

$ci = $_GET['checkin'] ?? '';
$co = $_GET['checkout'] ?? '';
$qs = '';
if (tanggalValid($ci) && tanggalValid($co) && $co > $ci) {
    $w[] = "id NOT IN (SELECT homestay_id FROM booking
                       WHERE status <> 'dibatalkan' AND tanggal_checkin < ? AND tanggal_checkout > ?)";
    $t .= 'ss';
    $p[] = $co;
    $p[] = $ci;
    $qs = '&checkin=' . urlencode($ci) . '&checkout=' . urlencode($co);
}

$sql = "SELECT * FROM homestay" . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY ' . urutan('harga_per_malam');
$daftar = qAll($koneksi, $sql, $t, $p);
$lokasiList = array_column(qAll($koneksi, "SELECT DISTINCT lokasi FROM homestay ORDER BY lokasi"), 'lokasi');
$kategoriList = qAll($koneksi, "SELECT kategori AS nama, COUNT(*) AS n FROM homestay WHERE status = 'tersedia' AND kategori IS NOT NULL AND kategori <> '' GROUP BY kategori ORDER BY kategori");
$fav = ambilWishlist($koneksi, 'homestay');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homestay - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php navbarHalaman('homestay'); ?>

    <main class="halaman-katalog">

        <div class="judul-katalog">
            <p class="slogan">Temukan Penginapan Nyaman</p>
            <h1>Pilihan Homestay di Jogja</h1>
            <p>
                Temukan tempat menginap yang nyaman
                untuk menemani perjalananmu di Yogyakarta.
            </p>
        </div>

        <?php tampilFlash(); ?>
        <?php barKategori($kategoriList); ?>
        <?php formFilter($lokasiList, true); ?>

        <section class="grid-produk">
            <?php if ($daftar): ?>
                <?php foreach ($daftar as $row): kartuProduk('homestay', $row, $fav, $qs); endforeach; ?>
            <?php else: ?>
                <p>Tidak ada homestay yang sesuai dengan pencarianmu.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php footerHalaman(); ?>
</body>
</html>
