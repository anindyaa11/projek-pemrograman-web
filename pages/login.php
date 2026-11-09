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
<body>

    <h1>Login JogjaYuk!</h1>

    <?php if (!empty($pesan)): ?>
        <p><?php echo htmlspecialchars($pesan); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>

</body>
</html>
