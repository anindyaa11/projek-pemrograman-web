<?php
session_start();

// Menghapus data session
$_SESSION = array();

// Menghapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Mengakhiri session
session_destroy();

// Kembali ke halaman utama
header("Location: ../index.php");
exit;
?>


