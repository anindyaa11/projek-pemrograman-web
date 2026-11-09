<?php
include "../config/koneksi.php";

// Mengambil ID homestay dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Mengambil data homestay
$query = "SELECT * FROM homestay WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$homestay = mysqli_fetch_assoc($result);

$pesan = "";
$berhasil = false;

// Proses booking ketika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && $homestay) {

    $nama = trim($_POST['nama_pemesan']);
    $checkin = $_POST['tanggal_checkin'];
    $checkout = $_POST['tanggal_checkout'];
    $tamu = (int) $_POST['jumlah_tamu'];

    if ($nama == "" || $checkin == "" || $checkout == "" || $tamu < 1) {
        $pesan = "Semua data booking wajib diisi dengan benar.";
    } else {
        $tanggalMasuk = new DateTime($checkin);
        $tanggalKeluar = new DateTime($checkout);
        $hariIni = new DateTime(date('Y-m-d'));

        $jumlahMalam = $tanggalMasuk->diff($tanggalKeluar)->days;

        if ($tanggalMasuk < $hariIni || $tanggalKeluar <= $tanggalMasuk) {
            $pesan = "Tanggal booking tidak valid.";
        } else {
            $harga = $homestay['harga_per_malam'];
            $totalHarga = $harga * $jumlahMalam;

            $sql = "INSERT INTO booking
                    (homestay_id, nama_pemesan, tanggal_checkin,
                    tanggal_checkout, jumlah_tamu, jumlah_malam, total_harga)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmtBooking = mysqli_prepare($koneksi, $sql);

            mysqli_stmt_bind_param(
                $stmtBooking,
                "isssiid",
                $id,
                $nama,
                $checkin,
                $checkout,
                $tamu,
                $jumlahMalam,
                $totalHarga
            );

            if (mysqli_stmt_execute($stmtBooking)) {
                $pesan = "Booking berhasil disimpan!";
                $berhasil = true;
            } else {
                $pesan = "Booking gagal disimpan. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Homestay - JogjaYuk!</title>
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

    <?php if ($homestay): ?>

        <div class="judul-katalog">
            <p class="slogan">Informasi Penginapan</p>

            <h1>
                <?php echo htmlspecialchars($homestay['nama_homestay']); ?>
            </h1>

            <p>
                <?php echo htmlspecialchars($homestay['lokasi']); ?>
            </p>
        </div>

        <article class="kartu-produk">
            <div class="isi-produk">

                <h2>Deskripsi Homestay</h2>

                <p>
                    <?php echo htmlspecialchars($homestay['deskripsi']); ?>
                </p>

                <h2>Fasilitas</h2>

                <p>
                    <?php echo htmlspecialchars($homestay['fasilitas'] ?? 'Belum ada informasi fasilitas.'); ?>
                </p>

                <h2>Harga</h2>

                <h3>
                    Rp <?php echo number_format($homestay['harga_per_malam'], 0, ',', '.'); ?>/malam
                </h3>

                <hr>

                <h2>Form Booking Homestay</h2>

                <?php if ($pesan != ""): ?>
                    <p><?php echo htmlspecialchars($pesan); ?></p>
                <?php endif; ?>

                <?php if (!$berhasil && $homestay['status'] == 'tersedia'): ?>

                    <form method="POST">

                        <label>Nama Pemesan</label>
                        <input type="text" name="nama_pemesan" required>

                        <br><br>

                        <label>Tanggal Check-in</label>
                        <input type="date" name="tanggal_checkin"
                               min="<?php echo date('Y-m-d'); ?>" required>

                        <br><br>

                        <label>Tanggal Check-out</label>
                        <input type="date" name="tanggal_checkout"
                               min="<?php echo date('Y-m-d'); ?>" required>

                        <br><br>

                        <label>Jumlah Tamu</label>
                        <input type="number" name="jumlah_tamu"
                               min="1" value="1" required>

                        <br><br>

                        <button type="submit" class="tombol-detail">
                            Booking Sekarang
                        </button>

                    </form>

                <?php elseif ($berhasil): ?>

                    <a href="homestay.php" class="tombol-detail">
                        Kembali ke Homestay
                    </a>

                <?php else: ?>

                    <p>Homestay ini sedang tidak tersedia.</p>

                <?php endif; ?>

            </div>
        </article>

    <?php else: ?>

        <h2>Homestay tidak ditemukan.</h2>
        <a href="homestay.php">Kembali ke katalog</a>

    <?php endif; ?>

</main>

</body>
</html>
