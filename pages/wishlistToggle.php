<?php
include "../config/koneksi.php";
include "../config/helper.php";

$back = urlAman($_POST['back'] ?? '', 'wishlist.php');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: wishlist.php");
    exit;
}

if (!sudahLogin()) {
    header("Location: login.php?redirect=" . urlencode($back));
    exit;
}

$tipe = $_POST['tipe'] ?? '';
$itemId = (int) ($_POST['item_id'] ?? 0);
$tabel = ['homestay' => 'homestay', 'paket' => 'paket_wisata', 'rental' => 'rental'];

if (!csrfValid() || !isset($tabel[$tipe]) || $itemId < 1) {
    setFlash('gagal', 'Permintaan tidak valid.');
    header("Location: $back");
    exit;
}

try {
    $ada = qOne($koneksi, "SELECT id FROM " . $tabel[$tipe] . " WHERE id = ?", 'i', [$itemId]);
    if (!$ada) {
        setFlash('gagal', 'Item tidak ditemukan.');
    } else {
        $uid = (int) $_SESSION['user_id'];
        $n = qExec($koneksi, "DELETE FROM wishlist WHERE user_id = ? AND tipe = ? AND item_id = ?", 'isi', [$uid, $tipe, $itemId]);
        if ($n > 0) {
            setFlash('sukses', 'Dihapus dari favorit.');
        } else {
            qExec($koneksi, "INSERT INTO wishlist (user_id, tipe, item_id) VALUES (?, ?, ?)", 'isi', [$uid, $tipe, $itemId]);
            setFlash('sukses', 'Ditambahkan ke favorit.');
        }
    }
} catch (Exception $ex) {
    setFlash('gagal', 'Gagal memperbarui favorit.');
}

header("Location: $back");
exit;
