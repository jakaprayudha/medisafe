<?php
require_once __DIR__ . '/../../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idcustomer = $_SESSION['id_customer'];
// $idcustomer = '19';
$sql = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `setting_pcare` WHERE id_customer = '$idcustomer'"));
$base_url = $sql['base_url'];
$service = $sql['service_name'];

$kodeppk = $sql['KodePPK'];
$tanggal = date('Y-m-d');

if (!function_exists('getNamaBulan')) {
    function getNamaBulan($bulan)
    {
        $daftarBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return $daftarBulan[$bulan - 1];
    }
}

$tglbulan = date('d') . ' ' . getNamaBulan(date('n')) . ' ' . date('Y');
$waktusekarang = date('Y-m-d H:i:s');
$secretKey = $sql['secret_key'];
$userkey = $sql['user_key'];
$username = $sql['username'];
$password = $sql['password'];
$kdAplikasi = '095';
$const_id = $sql['cons_id'];

date_default_timezone_set('UTC');
$tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
$signature = hash_hmac('sha256', $const_id . "&" . $tStamp, $secretKey, true);
$encodedSignature = base64_encode($signature);
$encodedAuthorization = base64_encode($username . ":" . $password . ":" . $kdAplikasi);

// Bungkus fungsi lainnya juga untuk keamanan
if (!function_exists('tampildata')) {
    function tampildata($query)
    {
        global $koneksi;
        $result = mysqli_query($koneksi, $query);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('generateUniqueNRM')) {
    function generateUniqueNRM()
    {
        global $koneksi;
        $sql = "SELECT * FROM pasien order by nomor_rm DESC";

        $result = mysqli_query($koneksi, $sql);
        $row = mysqli_fetch_array($result);

        if ($row) {
            $rm = intval($row['nomor_rm']) + 1;
            return sprintf("%06d", $rm);
        }
        return sprintf("%06d", 1);
    }
}