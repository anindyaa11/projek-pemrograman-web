
<?php
include "../config/koneksi.php";
$query = "SELECT * FROM homestay WHERE status = 'tersedia' ORDER BY id ASC";
$result = mysqli_query($koneksi, $query);
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
    <header class="navbar">
        <a href="../index.html" class="logo">JogjaYuk!</a>

        <nav class="menu">
            <a href="../index.html">Beranda</a>
            <a href="homestay.php" class="aktif">Homestay</a>
            <a href="paketWisata.html">Paket Wisata</a>
            <a href="rental.html">Rental</a>
        </nav>

        <a href="#" class="tombol-login">Login</a>
    </header>

    <main class="halaman-katalog">

        <div class="judul-katalog">
            <p class="slogan">Temukan Penginapan Nyaman</p>

            <h1>Pilihan Homestay di Jogja</h1>

            <p>
                Temukan tempat menginap yang nyaman
                untuk menemani perjalananmu di Yogyakarta.
            </p>
        </div>

        <section class="grid-produk">

            <?php if ($result && mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <article class="kartu-produk">

                        <?php
                        $namaGambar = $row['gambar'];
                        $lokasiGambar = "../assets/images/" . $namaGambar;
                        $pathGambar = __DIR__ . "/../assets/images/" . $namaGambar;
                        ?>

                        <?php if (!empty($namaGambar) && file_exists($pathGambar)): ?>

                            <img
                                src="<?php echo htmlspecialchars($lokasiGambar); ?>"
                                alt="<?php echo htmlspecialchars($row['nama_homestay']); ?>"
                                class="gambar-produk"
                            >

                        <?php else: ?>

                            <div class="gambar-produk gambar-homestay">
                                🏡
                            </div>

                        <?php endif; ?>

                        <div class="isi-produk">

                            <h2>
                                <?php echo htmlspecialchars($row['nama_homestay']); ?>
                            </h2>

                            <p>
                                📍 <?php echo htmlspecialchars($row['lokasi']); ?>
                            </p>

                            <p>
                                <?php echo htmlspecialchars($row['deskripsi']); ?>
                            </p>

                            <h3>
                                Rp <?php echo number_format($row['harga_per_malam'], 0, ',', '.'); ?>/malam
                            </h3>

                           <a href="detailHomestay.php?id=<?php echo $row['id']; ?>" class="tombol-detail">
                                Lihat Detail
                           </a>

                        </div>
                    </article>

                <?php endwhile; ?>

            <?php else: ?>

                <p>Belum ada homestay yang tersedia.</p>

            <?php endif; ?>

        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">

        <div class="footer-container">

            <div class="footer-identitas">
                <h2>JogjaYuk!</h2>

                <p>Liburan ke Jogja, Tinggal Klik Aja.</p>

                <p>
                    Temukan pengalaman terbaik untuk perjalananmu
                    di Yogyakarta.
                </p>
            </div>

            <div class="footer-navigasi">
                <h3>Navigasi</h3>

                <a href="../index.html">Beranda</a>
                <a href="homestay.php">Homestay</a>
                <a href="paketWisata.html">Paket Wisata</a>
                <a href="rental.html">Rental Kendaraan</a>
            </div>

        </div>

        <div class="footer-bawah">
            <p>&copy; 2026 JogjaYuk! Semua hak dilindungi.</p>
        </div>

    </footer>

</body>
</html>
