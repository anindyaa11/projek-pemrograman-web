<?php
$host = "localhost";
$username = "root";
$password = "";
$db = "jogjayuk";

$koneksi = mysqli_connect($host, $username, $password, $db);
if ($koneksi -> connect_error) {
    die("Koneksi database gagal: " . $koneksi -> connect_error);
}

?>