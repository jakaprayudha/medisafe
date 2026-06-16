<?php
session_start();
header('Content-Type: application/json');

$id_room = $_POST['id_room'] ?? null;

if ($id_room) {
   $_SESSION['selected_room_id'] = $id_room;
   echo json_encode(['status' => 'success']);
} else {
   echo json_encode(['status' => 'error', 'message' => 'ID room tidak dikirim']);
}
