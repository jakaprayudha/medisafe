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

   // MASTER FASKES
   case 'faskes':
      $successCount = 0;
      $duplicateData = [];
      $errorData = [];

      try {
         foreach ($rows as $rowIndex => $r) {
            try {
               // Trim and remove extra whitespaces
               $faskes_name = removeWhitespaces($r['faskes_name'] ?? '');
               $faskes_code = removeWhitespaces($r['faskes_code'] ?? '');

               // Validate required fields
               if (empty($faskes_name)) {
                  throw new Exception('Nama Faskes harus diisi');
               }

               if (empty($faskes_code)) {
                  throw new Exception('Kode Faskes harus diisi');
               }

               // Check for duplicate faskes_code
               $checkDuplicate = $koneksi->prepare("
                  SELECT id_faskes FROM ms_faskes WHERE faskes_code = ?
               ");
               $checkDuplicate->bind_param("s", $faskes_code);
               $checkDuplicate->execute();
               $resultDuplicate = $checkDuplicate->get_result();

               if ($resultDuplicate->num_rows > 0) {
                  throw new Exception('Kode Faskes sudah terdaftar');
               }
               $checkDuplicate->close();

               // Insert data
               $stmt = $koneksi->prepare("
                  INSERT INTO ms_faskes (faskes_name, faskes_code)
                  VALUES (?, ?)
               ");

               if (!$stmt) {
                  throw new Exception('Prepare statement gagal: ' . $koneksi->error);
               }

               $stmt->bind_param("ss", $faskes_name, $faskes_code);

               if (!$stmt->execute()) {
                  throw new Exception('Execute gagal: ' . $stmt->error);
               }

               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = [
                     'row' => $rowIndex + 1,
                     'code' => $faskes_code ?? '',
                     'nama' => $faskes_name ?? '',
                     'reason' => $e->getMessage()
                  ];
               } else {
                  $errorData[] = [
                     'row' => $rowIndex + 1,
                     'code' => $faskes_code ?? '',
                     'nama' => $faskes_name ?? '',
                     'error' => $e->getMessage()
                  ];
               }
            }
         }

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

   // PASIEN
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

   // DOKTER
   case 'dokter':
      $successCount = 0;
      $duplicateData = [];
      $errorData = [];

      try {
         foreach ($rows as $rowIndex => $r) {
            try {
               // Trim and remove extra whitespaces
               $nama_dokter = removeWhitespaces($r['nama_dokter'] ?? '');
               $spesialis = removeWhitespaces($r['spesialis'] ?? '');

               // Validate required fields
               if (empty($nama_dokter)) {
                  throw new Exception('Nama Dokter harus diisi');
               }

               if (empty($spesialis)) {
                  throw new Exception('Spesialis harus diisi');
               }

               // Check for duplicate dokter
               $checkDuplicate = $koneksi->prepare("
                  SELECT id_dokter FROM dokter WHERE id_faskes = ? AND nama_dokter = ? AND spesialis = ?
               ");
               $checkDuplicate->bind_param("iss", $id_faskes, $nama_dokter, $spesialis);
               $checkDuplicate->execute();
               $resultDuplicate = $checkDuplicate->get_result();

               if ($resultDuplicate->num_rows > 0) {
                  throw new Exception('Dokter dengan nama dan spesialis yang sama sudah terdaftar');
               }
               $checkDuplicate->close();

               // Insert data
               $stmt = $koneksi->prepare("
                  INSERT INTO dokter (id_faskes, nama_dokter, spesialis)
                  VALUES (?, ?, ?)
               ");

               if (!$stmt) {
                  throw new Exception('Prepare statement gagal: ' . $koneksi->error);
               }

               $stmt->bind_param("iss", $id_faskes, $nama_dokter, $spesialis);

               if (!$stmt->execute()) {
                  throw new Exception('Execute gagal: ' . $stmt->error);
               }

               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = [
                     'row' => $rowIndex + 1,
                     'nama' => $nama_dokter ?? '',
                     'spesialis' => $spesialis ?? '',
                     'reason' => $e->getMessage()
                  ];
               } else {
                  $errorData[] = [
                     'row' => $rowIndex + 1,
                     'nama' => $nama_dokter ?? '',
                     'spesialis' => $spesialis ?? '',
                     'error' => $e->getMessage()
                  ];
               }
            }
         }

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

   // FARMASI
   case 'farmasi':
      $successCount = 0;
      $duplicateData = [];
      $errorData = [];

      try {
         foreach ($rows as $rowIndex => $r) {
            try {
               // Trim and remove extra whitespaces
               $pharmacy_code = removeWhitespaces($r['Kode'] ?? '');
               $pharmacy_name = removeWhitespaces($r['Nama Obat'] ?? '');
               $pharmacy_jenis = removeWhitespaces($r['Jenis'] ?? '');
               $pharmacy_category = removeWhitespaces($r['Kategori'] ?? '');
               $pharmacy_stock = removeWhitespaces($r['Stok'] ?? '0');
               $pharmacy_unit = removeWhitespaces($r['Satuan'] ?? '');
               $pharmacy_price_general = removeWhitespaces($r['Harga Umum'] ?? '0');
               $pharmacy_price_item = removeWhitespaces($r['Harga Barang'] ?? '0');
               $pharmacy_price_buy = removeWhitespaces($r['Harga Beli (Setelah Pajak)'] ?? '0');
               $pharmacy_price_otc = removeWhitespaces($r['Harga OTC'] ?? '0');
               $pharmacy_price_bpjs = removeWhitespaces($r['Harga Jual BPJS'] ?? '0');
               $pharmacy_margin = removeWhitespaces($r['Margin Profit'] ?? '0');

               // Validate required fields
               if (empty($pharmacy_code)) {
                  throw new Exception('Kode Obat harus diisi');
               }

               if (empty($pharmacy_name)) {
                  throw new Exception('Nama Obat harus diisi');
               }

               // Check for duplicate pharmacy_code
               $checkDuplicate = $koneksi->prepare("
                  SELECT id_pharmacy FROM ms_pharmacy WHERE id_customer = ? AND pharmacy_code = ?
               ");
               $checkDuplicate->bind_param("ss", $id_faskes, $pharmacy_code);
               $checkDuplicate->execute();
               $resultDuplicate = $checkDuplicate->get_result();

               if ($resultDuplicate->num_rows > 0) {
                  throw new Exception('Kode Obat sudah terdaftar');
               }
               $checkDuplicate->close();

               // Insert data
               $stmt = $koneksi->prepare("
                  INSERT INTO ms_pharmacy (id_customer, pharmacy_code, pharmacy_name_trade, pharmcy_jenis_drugs, pharmacy_category, pharmacy_stock, pharmacy_unit, pharmacy_price_general, pharmacy_price_item, pharmacy_price_buy, pharmacy_price_otc, pharmacy_price_bpjs, pharmacy_margin_profit)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
               ");

               if (!$stmt) {
                  throw new Exception('Prepare statement gagal: ' . $koneksi->error);
               }

               $stmt->bind_param(
                  "issssssssssss",
                  $id_faskes,
                  $pharmacy_code,
                  $pharmacy_name,
                  $pharmacy_jenis,
                  $pharmacy_category,
                  $pharmacy_stock,
                  $pharmacy_unit,
                  $pharmacy_price_general,
                  $pharmacy_price_item,
                  $pharmacy_price_buy,
                  $pharmacy_price_otc,
                  $pharmacy_price_bpjs,
                  $pharmacy_margin
               );

               if (!$stmt->execute()) {
                  throw new Exception('Execute gagal: ' . $stmt->error);
               }

               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = [
                     'row' => $rowIndex + 1,
                     'code' => $pharmacy_code ?? '',
                     'nama' => $pharmacy_name ?? '',
                     'reason' => $e->getMessage()
                  ];
               } else {
                  $errorData[] = [
                     'row' => $rowIndex + 1,
                     'code' => $pharmacy_code ?? '',
                     'nama' => $pharmacy_name ?? '',
                     'error' => $e->getMessage()
                  ];
               }
            }
         }

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

   // VISIT
   case 'visit':
      $successCount = 0;
      $duplicateData = [];
      $errorData = [];

      try {
         foreach ($rows as $rowIndex => $r) {
            try {
               // Trim and remove extra whitespaces
               $nomor_rm = removeWhitespaces($r['nomor_rm'] ?? '');
               $tanggal = removeWhitespaces($r['tanggal'] ?? '');

               // Validate required fields
               if (empty($nomor_rm)) {
                  throw new Exception('Nomor RM harus diisi');
               }

               if (empty($tanggal)) {
                  throw new Exception('Tanggal harus diisi');
               }

               // Check for duplicate visit
               $checkDuplicate = $koneksi->prepare("
                  SELECT id_visit FROM visit WHERE id_faskes = ? AND nomor_rm = ? AND tanggal = ?
               ");
               $checkDuplicate->bind_param("iss", $id_faskes, $nomor_rm, $tanggal);
               $checkDuplicate->execute();
               $resultDuplicate = $checkDuplicate->get_result();

               if ($resultDuplicate->num_rows > 0) {
                  throw new Exception('Visit dengan nomor RM dan tanggal yang sama sudah terdaftar');
               }
               $checkDuplicate->close();

               // Insert data
               $stmt = $koneksi->prepare("
                  INSERT INTO visit (id_faskes, nomor_rm, tanggal)
                  VALUES (?, ?, ?)
               ");

               if (!$stmt) {
                  throw new Exception('Prepare statement gagal: ' . $koneksi->error);
               }

               $stmt->bind_param("iss", $id_faskes, $nomor_rm, $tanggal);

               if (!$stmt->execute()) {
                  throw new Exception('Execute gagal: ' . $stmt->error);
               }

               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = [
                     'row' => $rowIndex + 1,
                     'rm' => $nomor_rm ?? '',
                     'tanggal' => $tanggal ?? '',
                     'reason' => $e->getMessage()
                  ];
               } else {
                  $errorData[] = [
                     'row' => $rowIndex + 1,
                     'rm' => $nomor_rm ?? '',
                     'tanggal' => $tanggal ?? '',
                     'error' => $e->getMessage()
                  ];
               }
            }
         }

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

   default:
      echo json_encode(['status' => 'error', 'message' => 'Type tidak dikenal']);
      exit;
}

echo json_encode([
   'status' => 'success',
   'message' => 'Import berhasil'
]);
