<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();
$uid = (int) $_SESSION['user_id'];

$semua = [];

foreach (qAll($koneksi,
    "SELECT b.*, h.nama_homestay FROM booking b
     JOIN homestay h ON b.homestay_id = h.id WHERE b.user_id = ?", 'i', [$uid]) as $r) {
    $semua[] = ['jenis' => 'homestay', 'id' => $r['id'], 'kode' => 'HS-' . str_pad($r['id'], 4, '0', STR_PAD_LEFT),
        'judul' => $r['nama_homestay'], 'total' => $r['total_harga'], 'status' => $r['status'], 'created_at' => $r['created_at'],
        'info' => ['Nama Pemesan' => $r['nama_pemesan'], 'Check-in' => $r['tanggal_checkin'], 'Check-out' => $r['tanggal_checkout'],
                   'Jumlah Tamu' => $r['jumlah_tamu'], 'Jumlah Malam' => $r['jumlah_malam']]];
}
foreach (qAll($koneksi,
    "SELECT b.*, p.nama_paket FROM booking_paket b
     JOIN paket_wisata p ON b.paket_id = p.id WHERE b.user_id = ?", 'i', [$uid]) as $r) {
    $semua[] = ['jenis' => 'paket', 'id' => $r['id'], 'kode' => 'PW-' . str_pad($r['id'], 4, '0', STR_PAD_LEFT),
        'judul' => $r['nama_paket'], 'total' => $r['total_harga'], 'status' => $r['status'], 'created_at' => $r['created_at'],
        'info' => ['Nama Pemesan' => $r['nama_pemesan'], 'Tanggal Kunjungan' => $r['tanggal_kunjungan'], 'Jumlah Orang' => $r['jumlah_orang']]];
}
foreach (qAll($koneksi,
    "SELECT b.*, r.nama_kendaraan FROM booking_rental b
     JOIN rental r ON b.rental_id = r.id WHERE b.user_id = ?", 'i', [$uid]) as $r) {
    $semua[] = ['jenis' => 'rental', 'id' => $r['id'], 'kode' => 'RK-' . str_pad($r['id'], 4, '0', STR_PAD_LEFT),
        'judul' => $r['nama_kendaraan'], 'total' => $r['total_harga'], 'status' => $r['status'], 'created_at' => $r['created_at'],
        'info' => ['Nama Pemesan' => $r['nama_pemesan'], 'Mulai Sewa' => $r['tanggal_mulai'], 'Selesai Sewa' => $r['tanggal_selesai'],
                   'Jumlah Hari' => $r['jumlah_hari']]];
}

usort($semua, function ($a, $b) { return strcmp($b['created_at'], $a['created_at']); });

$fJenis = $_GET['jenis'] ?? '';
$fStatus = $_GET['status'] ?? '';
$semua = array_values(array_filter($semua, function ($x) use ($fJenis, $fStatus) {
    return ($fJenis === '' || $x['jenis'] === $fJenis) && ($fStatus === '' || $x['status'] === $fStatus);
}));

$label = ['homestay' => 'Homestay', 'paket' => 'Paket Wisata', 'rental' => 'Rental Kendaraan'];
$sel = function ($k, $v) { return (($_GET[$k] ?? '') === $v) ? ' selected' : ''; };
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

<?php navbarHalaman('riwayat'); ?>

<main class="halaman-katalog">

    <div class="judul-katalog">
        <p class="slogan">Perjalananmu Bersama JogjaYuk!</p>
        <h1>Riwayat Booking</h1>
        <p>Daftar pemesanan homestay, paket wisata, dan rental yang telah dilakukan.</p>
    </div>

    <?php tampilFlash(); ?>

    <section>
        <article class="kartu-produk" style="margin-bottom:24px">
            <div class="isi-produk">
                <form method="GET">
                    <label>Jenis layanan</label>
                    <select name="jenis">
                        <option value="">Semua</option>
                        <?php foreach ($label as $k => $v): ?>
                            <option value="<?php echo $k; ?>"<?php echo $sel('jenis', $k); ?>><?php echo $v; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Status</label>
                    <select name="status">
                        <option value="">Semua</option>
                        <option value="menunggu"<?php echo $sel('status', 'menunggu'); ?>>Menunggu</option>
                        <option value="dikonfirmasi"<?php echo $sel('status', 'dikonfirmasi'); ?>>Dikonfirmasi</option>
                        <option value="dibatalkan"<?php echo $sel('status', 'dibatalkan'); ?>>Dibatalkan</option>
                    </select>

                    <div>
                        <button type="submit" class="tombol-detail" style="border:none">Terapkan</button>
                        <a href="riwayatBooking.php" class="tombol-detail">Reset</a>
                    </div>
                </form>
            </div>
        </article>
    </section>

    <section class="grid-produk">

        <?php if ($semua): ?>

            <?php foreach ($semua as $row): ?>

                <article class="kartu-produk">
                    <div class="isi-produk">

                        <h2><?php echo e($row['judul']); ?></h2>

                        <p>Kode Booking: <?php echo e($row['kode']); ?> (<?php echo e($label[$row['jenis']]); ?>)</p>

                        <?php foreach ($row['info'] as $k => $v): ?>
                            <p><?php echo e($k); ?>: <?php echo e($v); ?></p>
                        <?php endforeach; ?>

                        <h3>Total Harga: Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></h3>

                        <p>Status: <?php echo e($row['status']); ?></p>

                        <?php if ($row['status'] === 'menunggu'): ?>
                            <form method="POST" action="batalBooking.php"
                                  onsubmit="return confirm('Yakin ingin membatalkan pemesanan ini?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="jenis" value="<?php echo e($row['jenis']); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                                <button type="submit" class="tombol-detail" style="border:none">Batalkan Pesanan</button>
                            </form>
                        <?php endif; ?>

                    </div>
                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <p>Belum ada riwayat booking.</p>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
