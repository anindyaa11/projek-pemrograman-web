<?php
include "../config/koneksi.php";
include "../config/helper.php";

wajibLogin();
$uid = (int) $_SESSION['user_id'];
$pesan = "";
$sukses = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!csrfValid()) {
        $pesan = "Sesi formulir tidak valid. Silakan muat ulang halaman.";
    } elseif (($_POST['aksi'] ?? '') === 'profil') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nama === '' || mb_strlen($nama) > 100) {
            $pesan = "Nama wajib diisi (maksimal 100 karakter).";
        } elseif (($err = validasiEmailGmail($email)) !== "") {
            $pesan = $err;
        } elseif (qOne($koneksi, "SELECT id FROM users WHERE email = ? AND id <> ?", 'si', [$email, $uid])) {
            $pesan = "Email sudah digunakan akun lain.";
        } else {
            qExec($koneksi, "UPDATE users SET nama = ?, email = ? WHERE id = ?", 'ssi', [$nama, $email, $uid]);
            $_SESSION['nama'] = $nama;
            setFlash('sukses', 'Profil berhasil diperbarui.');
            header("Location: profil.php");
            exit;
        }
    } elseif (($_POST['aksi'] ?? '') === 'password') {
        $lama = $_POST['password_lama'] ?? '';
        $baru = $_POST['password_baru'] ?? '';
        $konf = $_POST['password_konfirmasi'] ?? '';
        $u = qOne($koneksi, "SELECT password FROM users WHERE id = ?", 'i', [$uid]);

        if (!$u || !password_verify($lama, $u['password'])) {
            $pesan = "Password lama salah.";
        } elseif (($err = validasiPassword($baru)) !== "") {
            $pesan = $err;
        } elseif ($baru !== $konf) {
            $pesan = "Konfirmasi password baru tidak cocok.";
        } else {
            qExec($koneksi, "UPDATE users SET password = ? WHERE id = ?", 'si', [password_hash($baru, PASSWORD_DEFAULT), $uid]);
            setFlash('sukses', 'Password berhasil diubah.');
            header("Location: profil.php");
            exit;
        }
    }
}

$user = qOne($koneksi, "SELECT nama, email, created_at FROM users WHERE id = ?", 'i', [$uid]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php navbarHalaman(); ?>

    <main class="halaman-katalog">
        <div class="judul-katalog">
            <p class="slogan">Akun Saya</p>
            <h1>Profil &amp; Pengaturan</h1>
            <p>
                <a href="riwayatBooking.php" class="tombol-detail">Riwayat Booking</a>
                <a href="wishlist.php" class="tombol-detail">Favorit Saya</a>
            </p>
        </div>

        <?php tampilFlash(); ?>
        <?php tampilPesan($pesan); ?>

        <div class="form-booking">
            <h2>Data Profil</h2>
            <p>Terdaftar sejak <?php echo e(date('d-m-Y', strtotime($user['created_at']))); ?></p>
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="aksi" value="profil">

                <label>Nama</label>
                <input type="text" name="nama" maxlength="100" value="<?php echo e($user['nama']); ?>" required>

                <label>Email</label>
                <input type="email" name="email" maxlength="100" value="<?php echo e($user['email']); ?>" required>

                <button type="submit" class="tombol-detail">Simpan Profil</button>
            </form>
        </div>

        <div class="form-booking">
            <h2>Ubah Password</h2>
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="aksi" value="password">

                <label>Password Lama</label>
                <input type="password" name="password_lama" required>

                <label>Password Baru (min. 8 karakter + 1 simbol)</label>
                <input type="password" name="password_baru" minlength="8" required>

                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_konfirmasi" minlength="8" required>

                <button type="submit" class="tombol-detail">Ubah Password</button>
            </form>
        </div>
    </main>

    <?php footerHalaman(); ?>
</body>
</html>
