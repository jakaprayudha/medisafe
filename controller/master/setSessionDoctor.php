<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_doctor'])) {
   $_SESSION['id_doctor'] = intval($_POST['id_doctor']);
   echo json_encode(['status' => 'success']);
} else {
   echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
}
