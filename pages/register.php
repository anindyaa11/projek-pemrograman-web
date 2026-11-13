<?php
include "../config/koneksi.php";

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST["nama"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($nama) || empty($email) || empty($password)) {
        $pesan = "Semua kolom wajib diisi!";
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
                $pesan = "Pendaftaran berhasil! Silakan login.";
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
                    <input type="text" name="nama"
                        placeholder="Masukkan nama kamu" required>
                </div>

                <div class="form-login">
                    <label>Email</label>
                    <input type="email" name="email"
                        placeholder="Masukkan email kamu" required>
                </div>

                <div class="form-login">
                    <label>Password</label>
                    <input type="password" name="password"
                        placeholder="Buat password" required>
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
