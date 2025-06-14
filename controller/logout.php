<?php
session_start();

// Hapus semua session
$_SESSION = [];

// Hancurkan session di server
session_destroy();

// Arahkan kembali ke halaman login
header("Location: ../index");
exit;
