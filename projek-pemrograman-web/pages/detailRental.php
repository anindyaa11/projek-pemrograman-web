<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$rental = qOne($koneksi, "SELECT * FROM rental WHERE id = ?", 'i', [$id]);

$pesan = "";
$mulai = "";
$selesai = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $rental) {
    wajibLogin();

    $nama = trim($_POST['nama_pemesan'] ?? '');
    $mulai = $_POST['tanggal_mulai'] ?? '';
    $selesai = $_POST['tanggal_selesai'] ?? '';

    if (!csrfValid()) {
        $pesan = "Sesi formulir tidak valid. Silakan muat ulang halaman.";
    } elseif ($rental['status'] !== 'tersedia') {
        $pesan = "Kendaraan ini sedang tidak tersedia.";
    } elseif ($nama === "" || mb_strlen($nama) > 100 || !tanggalValid($mulai) || !tanggalValid($selesai)) {
        $pesan = "Semua data pemesanan wajib diisi dengan benar.";
    } elseif ($mulai < date('Y-m-d') || $selesai < $mulai) {
        $pesan = "Tanggal sewa tidak valid.";
    } else {
        $hari = (new DateTime($mulai))->diff(new DateTime($selesai))->days + 1;
        $total = $rental['harga_per_hari'] * $hari;

        $bentrok = qOne($koneksi,
            "SELECT COUNT(*) AS n FROM booking_rental
             WHERE rental_id = ? AND status <> 'dibatalkan'
             AND tanggal_mulai <= ? AND tanggal_selesai >= ?",
            'iss', [$id, $selesai, $mulai]);

        if ($bentrok['n'] > 0) {
            $pesan = "Maaf, kendaraan sudah disewa pada tanggal tersebut. Silakan pilih tanggal lain.";
        } else {
            try {
                qExec($koneksi,
                    "INSERT INTO booking_rental (user_id, rental_id, nama_pemesan, tanggal_mulai, tanggal_selesai, jumlah_hari, total_harga)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    'iisssid', [$_SESSION['user_id'], $id, $nama, $mulai, $selesai, $hari, $total]);
                setFlash('sukses', "Pemesanan rental berhasil! Total " . rupiah($total) . " untuk $hari hari. Status: menunggu konfirmasi.");
                header("Location: riwayatBooking.php");
                exit;
            } catch (Exception $ex) {
                $pesan = "Pemesanan gagal disimpan. Silakan coba lagi.";
            }
        }
    }
}

$terpakai = $rental ? qAll($koneksi,
    "SELECT tanggal_mulai, tanggal_selesai FROM booking_rental
     WHERE rental_id = ? AND status <> 'dibatalkan' AND tanggal_selesai >= CURDATE()
     ORDER BY tanggal_mulai", 'i', [$id]) : [];
$fav = $rental ? in_array($id, ambilWishlist($koneksi, 'rental'), true) : false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rental - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php navbarHalaman('rental'); ?>

<main class="halaman-katalog">

<?php if ($rental): ?>

    <div class="judul-katalog">
        <p class="slogan">Informasi Kendaraan</p>
        <h1><?php echo e($rental['nama_kendaraan']); ?></h1>
        <p><?php echo e($rental['lokasi']); ?></p>
    </div>

    <article class="kartu-produk">
        <?php
        $g = basename((string) $rental['gambar']);
        if ($g !== '' && file_exists(__DIR__ . '/../assets/images/' . $g)): ?>
            <img src="../assets/images/<?php echo e($g); ?>" alt="Foto Kendaraan" class="foto-detail-homestay">
        <?php else: ?>
            <div class="gambar-produk"><?php echo emojiRental($rental['jenis_kendaraan']); ?></div>
        <?php endif; ?>

        <div class="isi-produk">
            <h2>Jenis Kendaraan</h2>
            <p><?php echo e($rental['jenis_kendaraan']); ?></p>

            <h2>Deskripsi</h2>
            <p><?php echo e($rental['deskripsi']); ?></p>

            <h2>Harga</h2>
            <h3>Rp <?php echo number_format($rental['harga_per_hari'], 0, ',', '.'); ?>/hari</h3>

            <?php echo tombolWishlist('rental', $id, $fav); ?>

            <?php if ($terpakai): ?>
                <h2>Tanggal Sudah Disewa</h2>
                <p>
                    <?php foreach ($terpakai as $b): ?>
                        • <?php echo e($b['tanggal_mulai']); ?> s/d <?php echo e($b['tanggal_selesai']); ?><br>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>

            <hr>
            <h2>Form Pemesanan Rental</h2>

            <?php tampilPesan($pesan); ?>

            <?php if ($rental['status'] !== 'tersedia'): ?>
                <p>Kendaraan ini sedang tidak tersedia.</p>

            <?php elseif (!sudahLogin()): ?>
                <p>Silakan login terlebih dahulu untuk memesan kendaraan.</p>
                <a href="login.php?redirect=<?php echo urlencode(halamanSekarang()); ?>" class="tombol-detail">Login untuk Memesan</a>

            <?php else: ?>
                <form method="POST">
                    <?php echo csrfField(); ?>

                    <label>Nama Pemesan</label>
                    <input type="text" name="nama_pemesan" maxlength="100"
                           value="<?php echo e($_POST['nama_pemesan'] ?? $_SESSION['nama']); ?>" required>
                    <br><br>

                    <label>Tanggal Mulai Sewa</label>
                    <input type="date" name="tanggal_mulai" id="mulai"
                           min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($mulai); ?>" required>
                    <br><br>

                    <label>Tanggal Selesai Sewa</label>
                    <input type="date" name="tanggal_selesai" id="selesai"
                           min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($selesai); ?>" required>
                    <br><br>

                    <div class="total-harga">
                        <p>Estimasi total biaya:</p>
                        <h3 id="totalHarga">Pilih tanggal sewa</h3>
                    </div>

                    <button type="submit" class="tombol-detail">Pesan Sekarang</button>
                </form>

                <script>
                (function () {
                    var harga = <?php echo (float) $rental['harga_per_hari']; ?>;
                    var a = document.getElementById('mulai');
                    var b = document.getElementById('selesai');
                    var out = document.getElementById('totalHarga');
                    function hitung() {
                        if (a.value) { b.min = a.value; }
                        if (a.value && b.value) {
                            var h = Math.round((new Date(b.value) - new Date(a.value)) / 86400000) + 1;
                            out.textContent = h > 0
                                ? h + ' hari × Rp ' + harga.toLocaleString('id-ID') + ' = Rp ' + (h * harga).toLocaleString('id-ID')
                                : 'Tanggal selesai tidak boleh sebelum tanggal mulai';
                        }
                    }
                    a.addEventListener('change', hitung);
                    b.addEventListener('change', hitung);
                    hitung();
                })();
                </script>
            <?php endif; ?>
        </div>
    </article>

<?php else: ?>
    <h2>Kendaraan tidak ditemukan.</h2>
    <a href="rental.php">Kembali ke katalog</a>
<?php endif; ?>

</main>
</body>
</html>
