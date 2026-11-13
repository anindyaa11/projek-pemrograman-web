<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JogjaYuk!- Liburan ke Jogja, Tinggal Klik Aja</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    
<header class="navbar">
    <a href="index.php" class="logo">JogjaYuk!</a>

    <nav class="menu">
        <a href="index.php" class="aktif">Beranda</a>
        <a href="pages/homestay.php">Homestay</a>
        <a href="pages/paketWisata.html">Paket Wisata</a>
        <a href="pages/rental.html">Rental</a>
    </nav>

    <?php if (isset($_SESSION["user_id"])): ?>
        <div class="menu-akun">
            <span>Halo, <?php echo htmlspecialchars($_SESSION["nama"]); ?>!</span>
            <a href="pages/logout.php" class="tombol-login">Logout</a>
        </div>
    <?php else: ?>
        <a href="pages/login.php" class="tombol-login">Login</a>
    <?php endif; ?>
</header>

    <main class="hero">
    <p class="slogan">Liburan ke Jogja, Tinggal Klik Aja</p>
    <h1>Temukan Pengalaman Terbaik di Yogyakarta</h1>
    <p class="deskripsi">Cari homestay nyaman, paket wisata seru, dan rental kendaraan untuk menemani perjalananmu menjelajahi Jogja.</p>
    <a href="#layanan" class="tombol">Jelajahi Sekarang</a>
    </main>

    <section id="layanan">
        <h2>Jelajahi Layanan Kami</h2>
        <p class="intro-layanan">Pilih layanan yang kamu butuhkan untuk hiburan di Jogja.</p>

        <div class="container-layanan">
            <div class="kartu-layanan">
                <div class="ikon-layanan">
                    <i class="bi bi-house-door"></i>
                </div>
                <h3>Homestay</h3>
                <p>Temukan penginapan nyaman untuk menemani liburanmu.</p>
                <a href="pages/homestay.php" class="link-layanan">Lihat Homestay
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            
            <div class="kartu-layanan">
                <div class="ikon-layanan">
                    <i class="bi bi-map"></i>
                </div>
                <h3>Paket Wisata</h3>
                <p>Jelajahi destinasi menarik dan pengalaman seru di Jogja.</p>
                <a href="pages/paketWisata.html" class="link-layanan">Lihat Paket Wisata
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="kartu-layanan">
                <div class="ikon-layanan">
                    <i class="bi bi-car-front"></i>
                </div>
                <h3>Rental Kendaraan</h3>
                <p>Pilih kendaraan untuk perjalananmu menjelajahi Jogja.</p>
                <a href="pages/rental.html" class="link-layanan">Lihat Kendaraan
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

        </div>
    
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-identitas">
                <h2>JogjaYuk!</h2>
                <p>Liburan ke Jogja, Tinggal Klik Aja.</p>
                <p>Temukan pengalaman terbaik untuk perjalananmu di Yogyakarta.</p>
            </div>
            <div class="footer-navigasi">
                <h3>Navigasi</h3>
                <a href="index.php">Beranda</a>
                <a href="pages/homestay.html">Homestay</a>
                <a href="pages/paketWisata.html">Paket Wisata</a>
                <a href="pages/rental.html">Rental Kendaraan</a>
            </div>
        </div>

        <div class="footer-bawah">
            <p>&copy; 2026 JogjaYuk! Semua hak dilindungi.</p>
        </div>
        
    </footer>

</body>
</html>