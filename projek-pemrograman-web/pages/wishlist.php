<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();
$uid = (int) $_SESSION['user_id'];

$homestay = qAll($koneksi,
    "SELECT h.* FROM wishlist w JOIN homestay h ON w.item_id = h.id
     WHERE w.user_id = ? AND w.tipe = 'homestay' ORDER BY w.created_at DESC", 'i', [$uid]);
$paket = qAll($koneksi,
    "SELECT p.* FROM wishlist w JOIN paket_wisata p ON w.item_id = p.id
     WHERE w.user_id = ? AND w.tipe = 'paket' ORDER BY w.created_at DESC", 'i', [$uid]);
$rental = qAll($koneksi,
    "SELECT r.* FROM wishlist w JOIN rental r ON w.item_id = r.id
     WHERE w.user_id = ? AND w.tipe = 'rental' ORDER BY w.created_at DESC", 'i', [$uid]);

$kosong = !$homestay && !$paket && !$rental;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorit Saya - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php navbarHalaman(); ?>

    <main class="halaman-katalog">
        <div class="judul-katalog">
            <p class="slogan">Simpanan Perjalananmu</p>
            <h1>Favorit Saya</h1>
            <p>Homestay, paket wisata, dan kendaraan yang kamu simpan.</p>
        </div>

        <?php tampilFlash(); ?>

        <?php if ($kosong): ?>
            <p>Belum ada item favorit. Tekan tombol "🤍 Simpan" pada layanan yang kamu suka.</p>
        <?php endif; ?>

        <?php if ($homestay): ?>
            <h2>Homestay</h2>
            <section class="grid-produk">
                <?php foreach ($homestay as $r) kartuProduk('homestay', $r, array_map('intval', array_column($homestay, 'id'))); ?>
            </section>
        <?php endif; ?>

        <?php if ($paket): ?>
            <h2>Paket Wisata</h2>
            <section class="grid-produk">
                <?php foreach ($paket as $r) kartuProduk('paket', $r, array_map('intval', array_column($paket, 'id'))); ?>
            </section>
        <?php endif; ?>

        <?php if ($rental): ?>
            <h2>Rental Kendaraan</h2>
            <section class="grid-produk">
                <?php foreach ($rental as $r) kartuProduk('rental', $r, array_map('intval', array_column($rental, 'id'))); ?>
            </section>
        <?php endif; ?>
    </main>

    <?php footerHalaman(); ?>
</body>
</html>
