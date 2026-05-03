<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$id_customer = $_SESSION['id_customer'];
$checkfaseks = $koneksi->query("SELECT * FROM setting_clinic LEFT JOIN ms_faskes ON ms_faskes.id_clinic = setting_clinic.id  WHERE id_customer='$id_customer'");
$datafaskes = $checkfaseks->fetch_assoc();
