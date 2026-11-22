<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$homestay = qOne($koneksi, "SELECT * FROM homestay WHERE id = ?", 'i', [$id]);

$pesan = "";
$ci = tanggalValid($_GET['checkin'] ?? '') ? $_GET['checkin'] : '';
$co = tanggalValid($_GET['checkout'] ?? '') ? $_GET['checkout'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $homestay) {
    wajibLogin();

    $nama = trim($_POST['nama_pemesan'] ?? '');
    $ci = $_POST['tanggal_checkin'] ?? '';
    $co = $_POST['tanggal_checkout'] ?? '';
    $tamu = (int) ($_POST['jumlah_tamu'] ?? 0);

    if (!csrfValid()) {
        $pesan = "Sesi formulir tidak valid. Silakan muat ulang halaman.";
    } elseif ($homestay['status'] !== 'tersedia') {
        $pesan = "Homestay ini sedang tidak tersedia.";
    } elseif ($nama === "" || mb_strlen($nama) > 100 || !tanggalValid($ci) || !tanggalValid($co) || $tamu < 1 || $tamu > 50) {
        $pesan = "Semua data booking wajib diisi dengan benar.";
    } elseif ($ci < date('Y-m-d') || $co <= $ci) {
        $pesan = "Tanggal booking tidak valid.";
    } else {
        $jumlahMalam = (new DateTime($ci))->diff(new DateTime($co))->days;
        $totalHarga = $homestay['harga_per_malam'] * $jumlahMalam;

        $bentrok = qOne($koneksi,
            "SELECT COUNT(*) AS n FROM booking
             WHERE homestay_id = ? AND status <> 'dibatalkan'
             AND tanggal_checkin < ? AND tanggal_checkout > ?",
            'iss', [$id, $co, $ci]);

        if ($bentrok['n'] > 0) {
            $pesan = "Maaf, homestay sudah dibooking pada tanggal tersebut. Silakan pilih tanggal lain.";
        } else {
            try {
                qExec($koneksi,
                    "INSERT INTO booking (user_id, homestay_id, nama_pemesan, tanggal_checkin,
                     tanggal_checkout, jumlah_tamu, jumlah_malam, total_harga)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    'iisssiid',
                    [$_SESSION['user_id'], $id, $nama, $ci, $co, $tamu, $jumlahMalam, $totalHarga]);
                setFlash('sukses', "Booking berhasil! Total " . rupiah($totalHarga) . " untuk $jumlahMalam malam. Status: menunggu konfirmasi.");
                header("Location: riwayatBooking.php");
                exit;
            } catch (Exception $ex) {
                $pesan = "Booking gagal disimpan. Silakan coba lagi.";
            }
        }
    }
}

$terbooking = $homestay ? qAll($koneksi,
    "SELECT tanggal_checkin, tanggal_checkout FROM booking
     WHERE homestay_id = ? AND status <> 'dibatalkan' AND tanggal_checkout >= CURDATE()
     ORDER BY tanggal_checkin", 'i', [$id]) : [];
$fav = $homestay ? in_array($id, ambilWishlist($koneksi, 'homestay'), true) : false;
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

<?php navbarHalaman('homestay'); ?>

<main class="halaman-katalog">

    <?php if ($homestay): ?>

        <div class="judul-katalog">
            <p class="slogan">Informasi Penginapan</p>
            <h1><?php echo e($homestay['nama_homestay']); ?></h1>
            <p><?php echo e($homestay['lokasi']); ?></p>
        </div>

        <article class="kartu-produk">
            <?php if (!empty($homestay['gambar'])): ?>
                <img src="../assets/images/<?php echo e(basename($homestay['gambar'])); ?>" alt="Foto Homestay" class="foto-detail-homestay">
            <?php endif; ?>

            <div class="isi-produk">

                <h2>Deskripsi Homestay</h2>
                <p><?php echo e($homestay['deskripsi']); ?></p>

                <h2>Fasilitas</h2>
                <p><?php echo e($homestay['fasilitas'] ?? 'Belum ada informasi fasilitas.'); ?></p>

                <h2>Harga</h2>
                <h3>Rp <?php echo number_format($homestay['harga_per_malam'], 0, ',', '.'); ?>/malam</h3>

                <?php echo tombolWishlist('homestay', $id, $fav); ?>

                <?php if ($terbooking): ?>
                    <h2>Tanggal Sudah Terbooking</h2>
                    <p>
                        <?php foreach ($terbooking as $b): ?>
                            • <?php echo e($b['tanggal_checkin']); ?> s/d <?php echo e($b['tanggal_checkout']); ?><br>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <hr>

                <h2>Form Booking Homestay</h2>

                <?php tampilPesan($pesan); ?>

                <?php if ($homestay['status'] !== 'tersedia'): ?>

                    <p>Homestay ini sedang tidak tersedia.</p>

                <?php elseif (!sudahLogin()): ?>

                    <p>Silakan login terlebih dahulu untuk melakukan booking.</p>
                    <a href="login.php?redirect=<?php echo urlencode(halamanSekarang()); ?>" class="tombol-detail">Login untuk Booking</a>

                <?php else: ?>

                    <form method="POST">
                        <?php echo csrfField(); ?>

                        <label>Nama Pemesan</label>
                        <input type="text" name="nama_pemesan" maxlength="100"
                               value="<?php echo e($_POST['nama_pemesan'] ?? $_SESSION['nama']); ?>" required>
                        <br><br>

                        <label>Tanggal Check-in</label>
                        <input type="date" name="tanggal_checkin" id="checkin"
                               min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($ci); ?>" required>
                        <br><br>

                        <label>Tanggal Check-out</label>
                        <input type="date" name="tanggal_checkout" id="checkout"
                               min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($co); ?>" required>
                        <br><br>

                        <label>Jumlah Tamu</label>
                        <input type="number" name="jumlah_tamu" min="1" max="50"
                               value="<?php echo (int) ($_POST['jumlah_tamu'] ?? 1); ?>" required>
                        <br><br>

                        <div class="total-harga">
                            <p>Estimasi total biaya:</p>
                            <h3 id="totalHarga">Pilih tanggal check-in &amp; check-out</h3>
                        </div>

                        <button type="submit" class="tombol-detail">Booking Sekarang</button>
                    </form>

                    <script>
                    (function () {
                        var harga = <?php echo (float) $homestay['harga_per_malam']; ?>;
                        var ci = document.getElementById('checkin');
                        var co = document.getElementById('checkout');
                        var out = document.getElementById('totalHarga');
                        function hitung() {
                            if (ci.value) {
                                var min = new Date(ci.value);
                                min.setUTCDate(min.getUTCDate() + 1);
                                co.min = min.toISOString().slice(0, 10);
                            }
                            if (ci.value && co.value) {
                                var m = Math.round((new Date(co.value) - new Date(ci.value)) / 86400000);
                                out.textContent = m > 0
                                    ? m + ' malam × Rp ' + harga.toLocaleString('id-ID') + ' = Rp ' + (m * harga).toLocaleString('id-ID')
                                    : 'Tanggal check-out harus setelah check-in';
                            }
                        }
                        ci.addEventListener('change', hitung);
                        co.addEventListener('change', hitung);
                        hitung();
                    })();
                    </script>

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
