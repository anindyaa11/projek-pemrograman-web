<?php
include "../config/koneksi.php";
include "../config/helper.php";

if (sudahLogin()) {
    header("Location: ../index.php");
    exit;
}

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST["nama"]);
    $email = strtolower(trim($_POST["email"]));
    $password = $_POST["password"];

    if (empty($nama) || empty($email) || empty($password)) {
        $pesan = "Semua kolom wajib diisi!";
    } elseif (mb_strlen($nama) > 100) {
        $pesan = "Nama maksimal 100 karakter.";
    } elseif (($err = validasiEmailGmail($email)) !== "") {
        $pesan = $err;
    } elseif (($err = validasiPassword($password)) !== "") {
        $pesan = $err;
    } else {

        $queryCek = "SELECT id FROM users WHERE email = ?";
        $stmtCek = mysqli_prepare($koneksi, $queryCek);
        mysqli_stmt_bind_param($stmtCek, "s", $email);
        mysqli_stmt_execute($stmtCek);
        $hasilCek = mysqli_stmt_get_result($stmtCek);

        if (mysqli_num_rows($hasilCek) > 0) {
            $pesan = "Email sudah terdaftar!";
        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (nama, email, password)
                      VALUES (?, ?, ?)";

            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $nama,
                $email,
                $passwordHash
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php?daftar=1");
                exit;
            } else {
                $pesan = "Pendaftaran gagal. Silakan coba lagi.";
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
    <title>Register - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>  
<body class="halaman-login">

    <div class="login-container">

        <div class="login-header">
            <h1>JogjaYuk!</h1>
            <p>Liburan ke Jogja, Tinggal Klik Aja.</p>
        </div>

        <div class="login-card">

            <h2>Daftar Akun</h2>
            <p class="login-deskripsi">
                Buat akun untuk memulai perjalananmu!
            </p>

            <?php if (!empty($pesan)): ?>
                <p class="pesan-error">
                    <?php echo htmlspecialchars($pesan); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-login">
                    <label>Nama</label>
                    <input type="text" name="nama" maxlength="100"
                        placeholder="Masukkan nama kamu" required>
                </div>

                <div class="form-login">
                    <label>Email</label>
                    <input type="email" name="email" maxlength="100"
                        pattern="[A-Za-z0-9._%+\-]+@[Gg][Mm][Aa][Ii][Ll]\.[Cc][Oo][Mm]"
                        title="Gunakan email @gmail.com, contoh: nama@gmail.com"
                        placeholder="contoh: nama@gmail.com" required>
                </div>

                <div class="form-login">
                    <label>Password</label>
                    <input type="password" name="password" minlength="8"
                        pattern="(?=.*[^A-Za-z0-9\s]).{8,}"
                        title="Minimal 8 karakter dan mengandung minimal 1 simbol (! @ # $ % &amp; *)"
                        placeholder="Min. 8 karakter + 1 simbol (contoh: Jogja@2026)" required>
                </div>

                <button type="submit" class="tombol-login">
                    Daftar
                </button>

            </form>

            <p class="daftar-login">
                Sudah punya akun?
                <a href="login.php">Login di sini</a>
            </p>

            <a href="../index.php" class="kembali-beranda">
                ← Kembali ke Beranda
            </a>

        </div>
    </div>

</body>
</html>
