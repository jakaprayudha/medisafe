<?php
// Background import job processor - CLI only
if (php_sapi_name() !== 'cli') {
   http_response_code(403);
   exit('Access denied');
}

$job_id = $argv[1] ?? null;
if (!$job_id) {
   exit("Usage: php processImportJob.php <job_id>\n");
}

require dirname(__FILE__) . '/../../database/connect.php';

// Helper: trim + collapse whitespace
function removeWhitespaces($value)
{
   if (is_string($value)) {
      return preg_replace('/\s+/', ' ', trim($value));
   }
   return $value;
}

// Helper: update progress in DB
function updateProgress($koneksi, $job_id, $processed)
{
   $processed = (int) $processed;
   $stmt = $koneksi->prepare("UPDATE import_jobs SET processed_rows = ? WHERE job_id = ?");
   $stmt->bind_param("is", $processed, $job_id);
   $stmt->execute();
   $stmt->close();
}

// Helper: mark job failed
function markFailed($koneksi, $job_id, $message)
{
   $stmt = $koneksi->prepare("UPDATE import_jobs SET status = 'failed', result = ? WHERE job_id = ?");
   $stmt->bind_param("ss", $message, $job_id);
   $stmt->execute();
   $stmt->close();
}

try {
   // Mark as processing
   $stmtProc = $koneksi->prepare("UPDATE import_jobs SET status = 'processing' WHERE job_id = ?");
   $stmtProc->bind_param("s", $job_id);
   $stmtProc->execute();
   $stmtProc->close();

   // Fetch job record
   $stmtJob = $koneksi->prepare("SELECT * FROM import_jobs WHERE job_id = ?");
   $stmtJob->bind_param("s", $job_id);
   $stmtJob->execute();
   $job = $stmtJob->get_result()->fetch_assoc();
   $stmtJob->close();

   if (!$job) {
      exit("Job not found: $job_id\n");
   }

   // Read payload from temp file
   $rawData = json_decode(file_get_contents($job['data_file']), true);
   if (!$rawData) {
      markFailed($koneksi, $job_id, 'Gagal membaca data file');
      exit("Failed to read data file\n");
   }

   $type      = $rawData['type'];
   $id_faskes = $rawData['id_faskes'];
   $rows      = $rawData['data'];
   $totalRows = count($rows);

   // Update DB every ~2% of progress
   $updateInterval = max(1, intval($totalRows / 50));

   $successCount  = 0;
   $duplicateData = [];
   $errorData     = [];

   switch ($type) {

      // ── FASKES ──────────────────────────────────────────────
      case 'faskes':
         foreach ($rows as $rowIndex => $r) {
            try {
               $faskes_name = removeWhitespaces($r['faskes_name'] ?? '');
               $faskes_code = removeWhitespaces($r['faskes_code'] ?? '');

               if (empty($faskes_name)) throw new Exception('Nama Faskes harus diisi');
               if (empty($faskes_code)) throw new Exception('Kode Faskes harus diisi');

               $chk = $koneksi->prepare("SELECT id_faskes FROM ms_faskes WHERE faskes_code = ?");
               $chk->bind_param("s", $faskes_code);
               $chk->execute();
               $res = $chk->get_result();
               if ($res->num_rows > 0) throw new Exception('Kode Faskes sudah terdaftar');
               $chk->close();

               $stmt = $koneksi->prepare("INSERT INTO ms_faskes (faskes_name, faskes_code) VALUES (?, ?)");
               if (!$stmt) throw new Exception('Prepare gagal: ' . $koneksi->error);
               $stmt->bind_param("ss", $faskes_name, $faskes_code);
               if (!$stmt->execute()) throw new Exception('Execute gagal: ' . $stmt->error);
               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = ['row' => $rowIndex + 1, 'code' => $faskes_code ?? '', 'nama' => $faskes_name ?? '', 'reason' => $e->getMessage()];
               } else {
                  $errorData[] = ['row' => $rowIndex + 1, 'code' => $faskes_code ?? '', 'nama' => $faskes_name ?? '', 'error' => $e->getMessage()];
               }
            }

            if ($rowIndex % $updateInterval === 0) updateProgress($koneksi, $job_id, $rowIndex + 1);
         }
         break;

      // ── PASIEN ──────────────────────────────────────────────
      case 'pasien':
         foreach ($rows as $rowIndex => $r) {
            try {
               $nomor_ktp        = removeWhitespaces($r['Nomor KTP'] ?? '');
               $nomor_rm         = removeWhitespaces($r['nomor RM'] ?? '');
               $patient_name     = removeWhitespaces($r['Nama Pasien'] ?? '');
               $patient_datebirth = removeWhitespaces($r['Tanggal Lahir'] ?? '');
               $patient_gender   = removeWhitespaces($r['Jenis Kelamin'] ?? '');
               $patient_religion = removeWhitespaces($r['Agama'] ?? '');
               $patient_status   = removeWhitespaces($r['Status'] ?? '');
               $patient_blood    = removeWhitespaces($r['Golongan darah'] ?? '');
               $patient_education = removeWhitespaces($r['Pendidikan terakhir'] ?? '');
               $patient_occupation = removeWhitespaces($r['Pekerjaan'] ?? '');
               $patient_phone    = removeWhitespaces($r['No. HP'] ?? '');
               $patient_mail     = removeWhitespaces($r['Email'] ?? '');
               $patient_address  = removeWhitespaces($r['Alamat'] ?? '');
               $total_visit      = removeWhitespaces($r['Jumlah Kunjungan'] ?? '0');
               $registration_date = removeWhitespaces($r['Tanggal Terdaftar'] ?? '');
               $tag              = removeWhitespaces($r['Tag'] ?? '');
               $description      = removeWhitespaces($r['Deskripsi'] ?? '');

               if (empty($nomor_ktp) && empty($nomor_rm)) throw new Exception('Nomor KTP atau nomor RM harus diisi');
               if (empty($patient_name)) throw new Exception('Nama Pasien harus diisi');

               if (!empty($nomor_ktp)) {
                  $chk = $koneksi->prepare("SELECT id_patient FROM ms_patient WHERE patient_nik = ? AND id_customer = ?");
                  $chk->bind_param("ss", $nomor_ktp, $id_faskes);
                  $chk->execute();
                  if ($chk->get_result()->num_rows > 0) throw new Exception('Nomor KTP sudah terdaftar');
                  $chk->close();
               }

               if (!empty($nomor_rm)) {
                  $chk = $koneksi->prepare("SELECT id_patient FROM ms_patient WHERE nomor_rm = ? AND id_customer = ?");
                  $chk->bind_param("ss", $nomor_rm, $id_faskes);
                  $chk->execute();
                  if ($chk->get_result()->num_rows > 0) throw new Exception('Nomor RM sudah terdaftar');
                  $chk->close();
               }

               $stmt = $koneksi->prepare("
                  INSERT INTO ms_patient
                     (id_customer, patient_name, patient_nik, nomor_rm, patient_datebirth, patient_gender,
                      patient_religion, patient_status_lainnya, patient_blood, patient_education,
                      patient_occupation, patient_phone, patient_mail, patient_address, total_visit,
                      registration_date, tag, description)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
               ");
               if (!$stmt) throw new Exception('Prepare gagal: ' . $koneksi->error);
               $stmt->bind_param(
                  "ssssssssssssssssss",
                  $id_faskes, $patient_name, $nomor_ktp, $nomor_rm, $patient_datebirth, $patient_gender,
                  $patient_religion, $patient_status, $patient_blood, $patient_education,
                  $patient_occupation, $patient_phone, $patient_mail, $patient_address, $total_visit,
                  $registration_date, $tag, $description
               );
               if (!$stmt->execute()) throw new Exception('Execute gagal: ' . $stmt->error);
               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = ['row' => $rowIndex + 1, 'ktp' => $nomor_ktp ?? '', 'rm' => $nomor_rm ?? '', 'nama' => $patient_name ?? '', 'reason' => $e->getMessage()];
               } else {
                  $errorData[] = ['row' => $rowIndex + 1, 'ktp' => $nomor_ktp ?? '', 'rm' => $nomor_rm ?? '', 'nama' => $patient_name ?? '', 'error' => $e->getMessage()];
               }
            }

            if ($rowIndex % $updateInterval === 0) updateProgress($koneksi, $job_id, $rowIndex + 1);
         }
         break;

      // ── DOKTER ──────────────────────────────────────────────
      case 'dokter':
         foreach ($rows as $rowIndex => $r) {
            try {
               $nama_dokter = removeWhitespaces($r['nama_dokter'] ?? '');
               $spesialis   = removeWhitespaces($r['spesialis'] ?? '');

               if (empty($nama_dokter)) throw new Exception('Nama Dokter harus diisi');
               if (empty($spesialis))   throw new Exception('Spesialis harus diisi');

               $chk = $koneksi->prepare("SELECT id_dokter FROM dokter WHERE id_faskes = ? AND nama_dokter = ? AND spesialis = ?");
               $chk->bind_param("iss", $id_faskes, $nama_dokter, $spesialis);
               $chk->execute();
               if ($chk->get_result()->num_rows > 0) throw new Exception('Dokter dengan nama dan spesialis yang sama sudah terdaftar');
               $chk->close();

               $stmt = $koneksi->prepare("INSERT INTO dokter (id_faskes, nama_dokter, spesialis) VALUES (?, ?, ?)");
               if (!$stmt) throw new Exception('Prepare gagal: ' . $koneksi->error);
               $stmt->bind_param("iss", $id_faskes, $nama_dokter, $spesialis);
               if (!$stmt->execute()) throw new Exception('Execute gagal: ' . $stmt->error);
               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = ['row' => $rowIndex + 1, 'nama' => $nama_dokter ?? '', 'spesialis' => $spesialis ?? '', 'reason' => $e->getMessage()];
               } else {
                  $errorData[] = ['row' => $rowIndex + 1, 'nama' => $nama_dokter ?? '', 'spesialis' => $spesialis ?? '', 'error' => $e->getMessage()];
               }
            }

            if ($rowIndex % $updateInterval === 0) updateProgress($koneksi, $job_id, $rowIndex + 1);
         }
         break;

      // ── FARMASI ─────────────────────────────────────────────
      case 'farmasi':
         foreach ($rows as $rowIndex => $r) {
            try {
               $pharmacy_code          = removeWhitespaces($r['Kode'] ?? '');
               $pharmacy_name          = removeWhitespaces($r['Nama Obat'] ?? '');
               $pharmacy_jenis         = removeWhitespaces($r['Jenis'] ?? '');
               $pharmacy_category      = removeWhitespaces($r['Kategori'] ?? '');
               $pharmacy_stock         = removeWhitespaces($r['Stok'] ?? '0');
               $pharmacy_unit          = removeWhitespaces($r['Satuan'] ?? '');
               $pharmacy_price_general = removeWhitespaces($r['Harga Umum'] ?? '0');
               $pharmacy_price_item    = removeWhitespaces($r['Harga Barang'] ?? '0');
               $pharmacy_price_buy     = removeWhitespaces($r['Harga Beli (Setelah Pajak)'] ?? '0');
               $pharmacy_price_otc     = removeWhitespaces($r['Harga OTC'] ?? '0');
               $pharmacy_price_bpjs    = removeWhitespaces($r['Harga Jual BPJS'] ?? '0');
               $pharmacy_margin        = removeWhitespaces($r['Margin Profit'] ?? '0');

               if (empty($pharmacy_code)) throw new Exception('Kode Obat harus diisi');
               if (empty($pharmacy_name)) throw new Exception('Nama Obat harus diisi');

               $chk = $koneksi->prepare("SELECT id_pharmacy FROM ms_pharmacy WHERE id_customer = ? AND pharmacy_code = ?");
               $chk->bind_param("ss", $id_faskes, $pharmacy_code);
               $chk->execute();
               if ($chk->get_result()->num_rows > 0) throw new Exception('Kode Obat sudah terdaftar');
               $chk->close();

               $stmt = $koneksi->prepare("
                  INSERT INTO ms_pharmacy
                     (id_customer, pharmacy_code, pharmacy_name_trade, pharmcy_jenis_drugs, pharmacy_category,
                      pharmacy_stock, pharmacy_unit, pharmacy_price_general, pharmacy_price_item,
                      pharmacy_price_buy, pharmacy_price_otc, pharmacy_price_bpjs, pharmacy_margin_profit)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
               ");
               if (!$stmt) throw new Exception('Prepare gagal: ' . $koneksi->error);
               $stmt->bind_param(
                  "issssssssssss",
                  $id_faskes, $pharmacy_code, $pharmacy_name, $pharmacy_jenis, $pharmacy_category,
                  $pharmacy_stock, $pharmacy_unit, $pharmacy_price_general, $pharmacy_price_item,
                  $pharmacy_price_buy, $pharmacy_price_otc, $pharmacy_price_bpjs, $pharmacy_margin
               );
               if (!$stmt->execute()) throw new Exception('Execute gagal: ' . $stmt->error);
               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = ['row' => $rowIndex + 1, 'code' => $pharmacy_code ?? '', 'nama' => $pharmacy_name ?? '', 'reason' => $e->getMessage()];
               } else {
                  $errorData[] = ['row' => $rowIndex + 1, 'code' => $pharmacy_code ?? '', 'nama' => $pharmacy_name ?? '', 'error' => $e->getMessage()];
               }
            }

            if ($rowIndex % $updateInterval === 0) updateProgress($koneksi, $job_id, $rowIndex + 1);
         }
         break;

      // ── VISIT ───────────────────────────────────────────────
      case 'visit':
         foreach ($rows as $rowIndex => $r) {
            try {
               $nomor_rm = removeWhitespaces($r['nomor_rm'] ?? '');
               $tanggal  = removeWhitespaces($r['tanggal'] ?? '');

               if (empty($nomor_rm)) throw new Exception('Nomor RM harus diisi');
               if (empty($tanggal))  throw new Exception('Tanggal harus diisi');

               $chk = $koneksi->prepare("SELECT id_visit FROM visit WHERE id_faskes = ? AND nomor_rm = ? AND tanggal = ?");
               $chk->bind_param("iss", $id_faskes, $nomor_rm, $tanggal);
               $chk->execute();
               if ($chk->get_result()->num_rows > 0) throw new Exception('Visit dengan nomor RM dan tanggal yang sama sudah terdaftar');
               $chk->close();

               $stmt = $koneksi->prepare("INSERT INTO visit (id_faskes, nomor_rm, tanggal) VALUES (?, ?, ?)");
               if (!$stmt) throw new Exception('Prepare gagal: ' . $koneksi->error);
               $stmt->bind_param("iss", $id_faskes, $nomor_rm, $tanggal);
               if (!$stmt->execute()) throw new Exception('Execute gagal: ' . $stmt->error);
               $stmt->close();
               $successCount++;

            } catch (Exception $e) {
               if (strpos($e->getMessage(), 'sudah terdaftar') !== false) {
                  $duplicateData[] = ['row' => $rowIndex + 1, 'rm' => $nomor_rm ?? '', 'tanggal' => $tanggal ?? '', 'reason' => $e->getMessage()];
               } else {
                  $errorData[] = ['row' => $rowIndex + 1, 'rm' => $nomor_rm ?? '', 'tanggal' => $tanggal ?? '', 'error' => $e->getMessage()];
               }
            }

            if ($rowIndex % $updateInterval === 0) updateProgress($koneksi, $job_id, $rowIndex + 1);
         }
         break;

      default:
         markFailed($koneksi, $job_id, "Type tidak dikenal: $type");
         exit;
   }

   // Save final result to DB
   $dupCount = count($duplicateData);
   $errCount = count($errorData);
   $result   = json_encode([
      'summary' => [
         'total_rows' => $totalRows,
         'success'    => $successCount,
         'duplicates' => $dupCount,
         'errors'     => $errCount,
      ],
      'duplicates' => $duplicateData,
      'errors'     => $errorData,
   ]);

   $stmtDone = $koneksi->prepare("
      UPDATE import_jobs
      SET status = 'done',
          processed_rows  = ?,
          success_count   = ?,
          duplicate_count = ?,
          error_count     = ?,
          result          = ?
      WHERE job_id = ?
   ");
   $stmtDone->bind_param("iiiiss", $totalRows, $successCount, $dupCount, $errCount, $result, $job_id);
   $stmtDone->execute();
   $stmtDone->close();

   // Clean up temp data file
   if (file_exists($job['data_file'])) {
      unlink($job['data_file']);
   }

} catch (Exception $e) {
   // Mark job as failed on unexpected error
   markFailed($koneksi, $job_id, 'Fatal error: ' . $e->getMessage());
   exit(1);
}
