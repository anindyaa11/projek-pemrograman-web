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
            // Mengamankan password dengan hash
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
<body>

    <h1>Daftar Akun JogjaYuk!</h1>

    <?php if (!empty($pesan)): ?>
        <p><?php echo htmlspecialchars($pesan); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nama</label>
        <input type="text" name="nama" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Daftar</button>
    </form>

    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>

</body>
</html>
