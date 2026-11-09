<?php
include "../config/koneksi.php";

$query = "SELECT booking.*, homestay.nama_homestay
          FROM booking
          JOIN homestay ON booking.homestay_id = homestay.id
          ORDER BY booking.created_at DESC";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Booking - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="navbar">
    <a href="../index.html" class="logo">JogjaYuk!</a>

    <nav class="menu">
        <a href="../index.html">Beranda</a>
        <a href="homestay.php">Homestay</a>
        <a href="paketWisata.html">Paket Wisata</a>
        <a href="rental.html">Rental</a>
        <a href="riwayatBooking.php" class="aktif">Riwayat Booking</a>
    </nav>
</header>

<main class="halaman-katalog">

    <div class="judul-katalog">
        <p class="slogan">Perjalananmu Bersama JogjaYuk!</p>
        <h1>Riwayat Booking</h1>
        <p>Daftar pemesanan homestay yang telah dilakukan.</p>
    </div>

    <section class="grid-produk">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <article class="kartu-produk">
                    <div class="isi-produk">

                        <h2>
                            <?php echo htmlspecialchars($row['nama_homestay']); ?>
                        </h2>

                        <p>
                            Nama Pemesan:
                            <?php echo htmlspecialchars($row['nama_pemesan']); ?>
                        </p>

                        <p>
                            Check-in:
                            <?php echo htmlspecialchars($row['tanggal_checkin']); ?>
                        </p>

                        <p>
                            Check-out:
                            <?php echo htmlspecialchars($row['tanggal_checkout']); ?>
                        </p>

                        <p>
                            Jumlah Tamu:
                            <?php echo htmlspecialchars($row['jumlah_tamu']); ?>
                        </p>

                        <p>
                            Jumlah Malam:
                            <?php echo htmlspecialchars($row['jumlah_malam']); ?>
                        </p>

                        <h3>
                            Total Harga:
                            Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?>
                        </h3>

                        <p>
                            Status:
                            <?php echo htmlspecialchars($row['status']); ?>
                        </p>

                    </div>
                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <p>Belum ada riwayat booking.</p>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
