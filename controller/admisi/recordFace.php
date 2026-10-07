<?php
include '../../database/connect.php';
include __DIR__ . '/saveFaceImage.php';

saveFaceImage($koneksi, 'faces', 'ms_patient', 'face_image', 'id_patient');
