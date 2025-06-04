<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "toko_kelontong";

$conn = mysqli_connect($host, $user, $pass, $db_name);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
