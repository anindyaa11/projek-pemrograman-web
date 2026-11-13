<?php
session_start();
include "../config/koneksi.php";

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($koneksi, $query);

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($hasil);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["nama"] = $user["nama"];
        $_SESSION["role"] = $user["role"];

        header("Location: ../index.php");
        exit;
    } else {
        $pesan = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JogjaYuk!</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="halaman-login">

    <div class="login-container">

        <div class="login-header">
            <h1>JogjaYuk!</h1>
            <p>Liburan ke Jogja, Tinggal Klik Aja.</p>
        </div>

        <div class="login-card">

            <h2>Selamat Datang!</h2>
            <p class="login-deskripsi">
                Masuk ke akunmu untuk melanjutkan perjalanan.
            </p>

            <?php if (!empty($pesan)): ?>
                <p class="pesan-error">
                    <?php echo htmlspecialchars($pesan); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-login">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        placeholder="Masukkan email kamu"
                        required
                    >
                </div>

                <div class="form-login">
                    <label>Password</label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                </div>

                <button type="submit" class="tombol-login">
                    Login
                </button>

            </form>

            <p class="daftar-login">
                Belum punya akun?
                <a href="register.php">Daftar di sini</a>
            </p>

            <a href="../index.php" class="kembali-beranda">
                ← Kembali ke Beranda
            </a>

        </div>
    </div>

</body>
</html>
