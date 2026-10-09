<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/saveFaceImage.php';

saveFaceImage($koneksi, 'faces_visit', 'pasien_visit', 'face_image_visit', 'id_visit');
