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

// Trim and remove extra whitespaces
function removeWhitespaces($value) {
   if (is_string($value)) {
      // Trim and replace multiple consecutive whitespaces with single space
      return preg_replace('/\s+/', ' ', trim($value));
   }
   return $value;
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
      $successCount = 0;
      $duplicateData = [];
      $errorData = [];

      try {
         foreach ($rows as $rowIndex => $r) {
            try {
               // Trim all data and remove extra whitespaces
               $nomor_ktp = removeWhitespaces($r['Nomor KTP'] ?? '');
               $nomor_rm = removeWhitespaces($r['nomor RM'] ?? '');
               $patient_name = removeWhitespaces($r['Nama Pasien'] ?? '');
               $patient_datebirth = removeWhitespaces($r['Tanggal Lahir'] ?? '');
               $patient_gender = removeWhitespaces($r['Jenis Kelamin'] ?? '');
               $patient_religion = removeWhitespaces($r['Agama'] ?? '');
               $patient_status = removeWhitespaces($r['Status'] ?? '');
               $patient_blood = removeWhitespaces($r['Golongan darah'] ?? '');
               $patient_education = removeWhitespaces($r['Pendidikan terakhir'] ?? '');
               $patient_occupation = removeWhitespaces($r['Pekerjaan'] ?? '');
               $patient_phone = removeWhitespaces($r['No. HP'] ?? '');
               $patient_mail = removeWhitespaces($r['Email'] ?? '');
               $patient_address = removeWhitespaces($r['Alamat'] ?? '');
               $total_visit = removeWhitespaces($r['Jumlah Kunjungan'] ?? '0');
               $registration_date = removeWhitespaces($r['Tanggal Terdaftar'] ?? '');
               $tag = removeWhitespaces($r['Tag'] ?? '');
               $description = removeWhitespaces($r['Deskripsi'] ?? '');

               // Validate required fields
               if (empty($nomor_ktp) && empty($nomor_rm)) {
                  throw new Exception('Nomor KTP atau nomor RM harus diisi');
               }

               if (empty($patient_name)) {
                  throw new Exception('Nama Pasien harus diisi');
               }

               // Check for duplicates based on nomor_ktp
               if (!empty($nomor_ktp)) {
                  $checkKtp = $koneksi->prepare("
                     SELECT id_patient FROM ms_patient 
                     WHERE patient_nik = ? AND id_customer = ?
                  ");
                  $checkKtp->bind_param("ss", $nomor_ktp, $id_faskes);
                  $checkKtp->execute();
                  $resultKtp = $checkKtp->get_result();

                  if ($resultKtp->num_rows > 0) {
                     throw new Exception('Nomor KTP sudah terdaftar');
                  }
                  $checkKtp->close();
               }

               // Check for duplicates based on nomor_rm
               if (!empty($nomor_rm)) {
                  $checkRm = $koneksi->prepare("
                     SELECT id_patient FROM ms_patient 
                     WHERE nomor_rm = ? AND id_customer = ?
                  ");
                  $checkRm->bind_param("ss", $nomor_rm, $id_faskes);
                  $checkRm->execute();
                  $resultRm = $checkRm->get_result();

                  if ($resultRm->num_rows > 0) {
                     throw new Exception('Nomor RM sudah terdaftar');
                  }
                  $checkRm->close();
               }

               // Insert data if no duplicates
               $stmt = $koneksi->prepare("
                  INSERT INTO ms_patient (id_customer, patient_name, patient_nik, nomor_rm, patient_datebirth, patient_gender, patient_religion, patient_status_lainnya, patient_blood, patient_education, patient_occupation, patient_phone, patient_mail, patient_address, total_visit, registration_date, tag, description)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
               ");

               if (!$stmt) {
                  throw new Exception('Error: ' . $koneksi->error);
               }

               $stmt->bind_param(
                  "ssssssssssssssssss",
                  $id_faskes,
                  $patient_name,
                  $nomor_ktp,
                  $nomor_rm,
                  $patient_datebirth,
                  $patient_gender,
                  $patient_religion,
                  $patient_status,
                  $patient_blood,
                  $patient_education,
                  $patient_occupation,
                  $patient_phone,
                  $patient_mail,
                  $patient_address,
                  $total_visit,
                  $registration_date,
                  $tag,
                  $description
               );

               if (!$stmt->execute()) {
                  throw new Exception('Execute gagal: ' . $stmt->error);
               }

               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               // Check if it's a duplicate error
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = [
                     'row' => $rowIndex + 1,
                     'ktp' => $nomor_ktp ?? '',
                     'rm' => $nomor_rm ?? '',
                     'nama' => $patient_name ?? '',
                     'reason' => $e->getMessage()
                  ];
               } else {
                  $errorData[] = [
                     'row' => $rowIndex + 1,
                     'ktp' => $nomor_ktp ?? '',
                     'rm' => $nomor_rm ?? '',
                     'nama' => $patient_name ?? '',
                     'error' => $e->getMessage()
                  ];
               }
            }
         }

         // Return comprehensive response
         $response = [
            'status' => 'success',
            'message' => "Import selesai: $successCount data berhasil diimport",
            'summary' => [
               'total_rows' => count($rows),
               'success' => $successCount,
               'duplicates' => count($duplicateData),
               'errors' => count($errorData)
            ]
         ];

         if (!empty($duplicateData)) {
            $response['duplicates'] = $duplicateData;
         }

         if (!empty($errorData)) {
            $response['errors'] = $errorData;
         }

         echo json_encode($response);

      } catch (Exception $e) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
         ]);
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
