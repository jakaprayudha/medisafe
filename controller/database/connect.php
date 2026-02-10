<?php
$host = "localhost";
$uname = "root";
$password = "";
// $database = "db_enterprise";
$database = "db_medisafe";

// Membuat koneksi
$koneksi = new mysqli($host, $uname, $password, $database);

// Memeriksa koneksi
if (!$koneksi) {
    echo "Gagal: " . mysqli_connect_error();
}
