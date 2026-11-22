<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrfValid()) {
    setFlash('gagal', 'Permintaan tidak valid.');
    header("Location: riwayatBooking.php");
    exit;
}

$tabel = ['homestay' => 'booking', 'paket' => 'booking_paket', 'rental' => 'booking_rental'];
$jenis = $_POST['jenis'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if (!isset($tabel[$jenis]) || $id < 1) {
    setFlash('gagal', 'Data pemesanan tidak valid.');
} else {
    try {
        $t = $tabel[$jenis];
        $n = qExec($koneksi,
            "UPDATE $t SET status = 'dibatalkan' WHERE id = ? AND user_id = ? AND status = 'menunggu'",
            'ii', [$id, $_SESSION['user_id']]);
        if ($n > 0) {
            setFlash('sukses', 'Pemesanan berhasil dibatalkan.');
        } else {
            setFlash('gagal', 'Pemesanan tidak dapat dibatalkan (sudah dibatalkan/dikonfirmasi atau bukan milikmu).');
        }
    } catch (Exception $ex) {
        setFlash('gagal', 'Terjadi kesalahan saat membatalkan pemesanan.');
    }
}

header("Location: riwayatBooking.php");
exit;
