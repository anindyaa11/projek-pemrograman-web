<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

$nama = htmlspecialchars($_SESSION["nama"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="logo">JogjaYuk! Admin</a>

        <div class="menu-akun">
            Halo, <?php echo $nama; ?>
            <a href="../pages/logout.php" class="tombol-login">
                Logout
            </a>
        </div>
    </nav>

    <main class="halaman-katalog">

        <div class="judul-katalog">
            <h1>Dashboard Admin</h1>
            <p>Selamat datang di halaman pengelolaan JogjaYuk!</p>
        </div>

        <div class="grid-produk">

            <a href="kelolaHomestay.php" class="kartu-produk isi-produk">
                <h2>Kelola Homestay</h2>
                <p>Kelola data penginapan Jogja.</p>
            </a>

            <a href="kelolaPaket.php" class="kartu-produk isi-produk">
                <h2>Kelola Paket Wisata</h2>
                <p>Kelola data paket wisata.</p>
            </a>
            
            <a href="kelolaRental.php" class="kartu-produk isi-produk">
                <h2>Kelola Rental</h2>
                <p>Kelola data rental kendaraan.</p>
            </a>

           <a href="kelolaBooking.php" class="kartu-produk isi-produk">
                <h2>Kelola Booking</h2>
                <p>Lihat dan kelola pesanan pengguna.</p>
            </a>

            <a href="kelolaPengguna.php" class="kartu-produk isi-produk">
                <h2>Kelola Pengguna</h2>
                <p>Lihat data akun pengguna.</p>
            </a>

        </div>

    </main>

</body>
</html>
