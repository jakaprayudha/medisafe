<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$type = $data['type'] ?? null;
$rows = $data['data'] ?? [];
$id_faskes = $data['id_faskes'] ?? null;

if (!$type || !$rows) {
   echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
   exit;
}

if (!$id_faskes) {
   echo json_encode(['status' => 'error', 'message' => 'Faskes wajib dipilih']);
   exit;
}

switch ($type) {

   // 🔹 MASTER FASKES (tidak perlu id_faskes)
   case 'faskes':
      foreach ($rows as $r) {
         $stmt = $koneksi->prepare("
            INSERT INTO ms_faskes (faskes_name, faskes_code)
            VALUES (?, ?)
         ");
         $stmt->bind_param(
            "ss",
            $r['faskes_name'],
            $r['faskes_code']
         );
         $stmt->execute();
      }
      break;

   // 🔹 PASIEN
   case 'pasien':
      foreach ($rows as $r) {

         $stmt = $koneksi->prepare("
            INSERT INTO ms_patient (id_customer, patient_name, patient_nik, nomor_rm, patient_datebirth, patient_gender, patient_religion, patient_status_lainnya, patient_blood, patient_education, patient_occupation, patient_phone, patient_mail, patient_address, total_visit, registration_date, tag, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ");

         $stmt->bind_param(
            "ssssssssssssssssss",
            $id_faskes,
            $r['Nama Pasien'],
            $r['Nomor KTP'],
            $r['nomor RM'],
            $r['Tanggal Lahir'],
            $r['Jenis Kelamin'],
            $r['Agama'],
            $r['Status'],
            $r['Golongan darah'],
            $r['Pendidikan terakhir'],
            $r['Pekerjaan'],
            $r['No. HP'],
            $r['Email'],
            $r['Alamat'],
            $r['Jumlah Kunjungan'],
            $r['Tanggal Terdaftar'],
            $r['Tag'],
            $r['Deskripsi']
         );

         $stmt->execute();
      }
      break;

   // 🔹 DOKTER
   case 'dokter':
      foreach ($rows as $r) {

         $stmt = $koneksi->prepare("
            INSERT INTO dokter (id_faskes, nama_dokter, spesialis)
            VALUES (?, ?, ?)
         ");

         $stmt->bind_param(
            "iss",
            $id_faskes,
            $r['nama_dokter'],
            $r['spesialis']
         );

         $stmt->execute();
      }
      break;

   // 🔹 FARMASI
   case 'farmasi':
      foreach ($rows as $r) {

         $stmt = $koneksi->prepare("
            INSERT INTO ms_pharmacy (id_customer, pharmacy_code, pharmacy_name_trade, pharmcy_jenis_drugs, pharmacy_category, pharmacy_stock, pharmacy_unit, pharmacy_price_general, pharmacy_price_item, pharmacy_price_buy, pharmacy_price_otc, pharmacy_price_bpjs, pharmacy_margin_profit)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ");

         $stmt->bind_param(
            "issssssssssss", // 🔥 13 karakter
            $id_faskes,
            $r['Kode'],
            $r['Nama Obat'],
            $r['Jenis'],
            $r['Kategori'],
            $r['Stok'],
            $r['Satuan'],
            $r['Harga Umum'],
            $r['Harga Barang'],
            $r['Harga Beli (Setelah Pajak)'],
            $r['Harga OTC'],
            $r['Harga Jual BPJS'],
            $r['Margin Profit']
         );

         $stmt->execute();
      }
      break;

   // 🔹 VISIT
   case 'visit':
      foreach ($rows as $r) {

         $stmt = $koneksi->prepare("
            INSERT INTO visit (id_faskes, nomor_rm, tanggal)
            VALUES (?, ?, ?)
         ");

         $stmt->bind_param(
            "iss",
            $id_faskes,
            $r['nomor_rm'],
            $r['tanggal']
         );

         $stmt->execute();
      }
      break;

   default:
      echo json_encode(['status' => 'error', 'message' => 'Type tidak dikenal']);
      exit;
}

echo json_encode([
   'status' => 'success',
   'message' => 'Import berhasil'
]);
