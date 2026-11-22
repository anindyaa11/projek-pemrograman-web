<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$paket = qOne($koneksi, "SELECT * FROM paket_wisata WHERE id = ?", 'i', [$id]);

$pesan = "";
$tgl = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $paket) {
    wajibLogin();

    $nama = trim($_POST['nama_pemesan'] ?? '');
    $tgl = $_POST['tanggal_kunjungan'] ?? '';
    $orang = (int) ($_POST['jumlah_orang'] ?? 0);

    if (!csrfValid()) {
        $pesan = "Sesi formulir tidak valid. Silakan muat ulang halaman.";
    } elseif ($paket['status'] !== 'tersedia') {
        $pesan = "Paket wisata ini sedang tidak tersedia.";
    } elseif ($nama === "" || mb_strlen($nama) > 100 || !tanggalValid($tgl) || $orang < 1 || $orang > 100) {
        $pesan = "Semua data pemesanan wajib diisi dengan benar.";
    } elseif ($tgl < date('Y-m-d')) {
        $pesan = "Tanggal kunjungan tidak valid.";
    } else {
        $total = $paket['harga_per_orang'] * $orang;
        try {
            qExec($koneksi,
                "INSERT INTO booking_paket (user_id, paket_id, nama_pemesan, tanggal_kunjungan, jumlah_orang, total_harga)
                 VALUES (?, ?, ?, ?, ?, ?)",
                'iissid', [$_SESSION['user_id'], $id, $nama, $tgl, $orang, $total]);
            setFlash('sukses', "Pemesanan paket wisata berhasil! Total " . rupiah($total) . ". Status: menunggu konfirmasi.");
            header("Location: riwayatBooking.php");
            exit;
        } catch (Exception $ex) {
            $pesan = "Pemesanan gagal disimpan. Silakan coba lagi.";
        }
    }
}

$fav = $paket ? in_array($id, ambilWishlist($koneksi, 'paket'), true) : false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Paket Wisata - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php navbarHalaman('paket'); ?>

<main class="halaman-katalog">

<?php if ($paket): ?>

    <div class="judul-katalog">
        <p class="slogan">Informasi Paket Wisata</p>
        <h1><?php echo e($paket['nama_paket']); ?></h1>
        <p><?php echo e($paket['lokasi']); ?></p>
    </div>

    <article class="kartu-produk">
        <?php
        $g = basename((string) $paket['gambar']);
        if ($g !== '' && file_exists(__DIR__ . '/../assets/images/' . $g)): ?>
            <img src="../assets/images/<?php echo e($g); ?>" alt="Foto Paket" class="foto-detail-homestay">
        <?php else: ?>
            <div class="gambar-produk"><?php echo emojiPaket($paket['nama_paket']); ?></div>
        <?php endif; ?>

        <div class="isi-produk">
            <h2>Deskripsi Paket</h2>
            <p><?php echo e($paket['deskripsi']); ?></p>

            <?php if (!empty($paket['durasi'])): ?>
                <h2>Durasi</h2>
                <p><?php echo e($paket['durasi']); ?></p>
            <?php endif; ?>

            <?php if (!empty($paket['fasilitas'])): ?>
                <h2>Fasilitas</h2>
                <p><?php echo e($paket['fasilitas']); ?></p>
            <?php endif; ?>

            <h2>Harga</h2>
            <h3>Rp <?php echo number_format($paket['harga_per_orang'], 0, ',', '.'); ?>/orang</h3>

            <?php echo tombolWishlist('paket', $id, $fav); ?>

            <hr>
            <h2>Form Pemesanan Paket Wisata</h2>

            <?php tampilPesan($pesan); ?>

            <?php if ($paket['status'] !== 'tersedia'): ?>
                <p>Paket wisata ini sedang tidak tersedia.</p>

            <?php elseif (!sudahLogin()): ?>
                <p>Silakan login terlebih dahulu untuk memesan paket wisata.</p>
                <a href="login.php?redirect=<?php echo urlencode(halamanSekarang()); ?>" class="tombol-detail">Login untuk Memesan</a>

            <?php else: ?>
                <form method="POST">
                    <?php echo csrfField(); ?>

                    <label>Nama Pemesan</label>
                    <input type="text" name="nama_pemesan" maxlength="100"
                           value="<?php echo e($_POST['nama_pemesan'] ?? $_SESSION['nama']); ?>" required>
                    <br><br>

                    <label>Tanggal Kunjungan</label>
                    <input type="date" name="tanggal_kunjungan" id="tgl"
                           min="<?php echo date('Y-m-d'); ?>" value="<?php echo e($tgl); ?>" required>
                    <br><br>

                    <label>Jumlah Orang</label>
                    <input type="number" name="jumlah_orang" id="orang" min="1" max="100"
                           value="<?php echo (int) ($_POST['jumlah_orang'] ?? 1); ?>" required>
                    <br><br>

                    <div class="total-harga">
                        <p>Estimasi total biaya:</p>
                        <h3 id="totalHarga"></h3>
                    </div>

                    <button type="submit" class="tombol-detail">Pesan Sekarang</button>
                </form>

                <script>
                (function () {
                    var harga = <?php echo (float) $paket['harga_per_orang']; ?>;
                    var o = document.getElementById('orang');
                    var out = document.getElementById('totalHarga');
                    function hitung() {
                        var n = parseInt(o.value, 10);
                        out.textContent = n > 0
                            ? n + ' orang × Rp ' + harga.toLocaleString('id-ID') + ' = Rp ' + (n * harga).toLocaleString('id-ID')
                            : 'Masukkan jumlah orang';
                    }
                    o.addEventListener('input', hitung);
                    hitung();
                })();
                </script>
            <?php endif; ?>
        </div>
    </article>

<?php else: ?>
    <h2>Paket wisata tidak ditemukan.</h2>
    <a href="paketWisata.php">Kembali ke katalog</a>
<?php endif; ?>

</main>
</body>
</html>
