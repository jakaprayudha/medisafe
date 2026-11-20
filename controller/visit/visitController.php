<?php
include '../../database/connect.php';
while (ob_get_level()) ob_end_clean();

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
   createVisit();
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Method tidak diizinkan'
   ]);
   exit;
}

// ================= CREATE VISIT =================
// Dipakai: registrasi poliklinik (modal "Tanpa identitas"), registrasi UGD, jadwal dokter.
// pasien_baru=1 -> buat pasien anonim (nama sementara, JK, perkiraan umur, ciri-ciri) + kunjungan dalam satu transaksi.
// Perbaikan: jam praktik dulu diambil dari teks dropdown "dr X (07:00-23:00)(Sudah Tutup)" sehingga nama & jam salah;
// pasien tidak dicek milik faskes; code_doctor tidak disimpan; tanpa cegah daftar dobel; tanpa transaksi.
function createVisit()
{
   global $koneksi;
   date_default_timezone_set('Asia/Jakarta');
   $id_customer = (int) ($_SESSION['id_customer'] ?? 0);
   session_write_close();
   $jawab = function (array $d) { echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; };
   if (!$id_customer) $jawab(['status' => 'error', 'message' => 'Session habis']);

   $created_user = "Tanpa Identitas";
   $baru = ($_POST['pasien_baru'] ?? '') === '1';
   $id_patient = (int) ($_POST['id_patient'] ?? 0);
   $visit_date = (string) ($_POST['visit_date'] ?? '');
   $visit_time = (string) ($_POST['visit_time'] ?? '');
   $id_doctor = trim((string) ($_POST['id_doctor'] ?? ''));
   $id_provider = (int) ($_POST['id_provider'] ?? 0);
   $kdPoli = trim((string) ($_POST['kdPoli'] ?? ''));
   $poli_name = trim((string) ($_POST['poli_name'] ?? ($_POST['id_poli'] ?? '')));
   $source_hub = !empty($_POST['source_hub']) ? (string) $_POST['source_hub'] : 'Poliklinik';
   $keluhan = trim((string) ($_POST['keluhan'] ?? ''));

   // nama & jam praktik dokter: field eksplisit, atau urai teks lama "Nama (jam)(Sudah Tutup)"
   $doctor_name = trim((string) ($_POST['nama_dokter'] ?? ''));
   $jampraktek = trim((string) ($_POST['jampraktek'] ?? ''));
   if ($doctor_name === '') {
      $teks = trim(preg_replace('/\(\s*Sudah Tutup\s*\)/i', '', (string) ($_POST['doctor_name'] ?? '')));
      if (preg_match('/^(.*?)\s*\(([^()]*)\)\s*$/', $teks, $m)) {
         $doctor_name = trim($m[1]);
         if ($jampraktek === '') $jampraktek = trim($m[2]);
      } else {
         $doctor_name = $teks;
      }
   }

   if ((!$baru && !$id_patient) || !$visit_date || !$visit_time || !$id_doctor) $jawab(['status' => 'error', 'message' => 'Data tidak lengkap']);
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visit_date) || $visit_date < date('Y-m-d', strtotime('-7 days')) || $visit_date > date('Y-m-d', strtotime('+90 days'))) {
      $jawab(['status' => 'error', 'message' => 'Tanggal kunjungan tidak valid']);
   }
   if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $visit_time)) $jawab(['status' => 'error', 'message' => 'Jam kunjungan tidak valid']);

   $koneksi->begin_transaction();
   try {
      $nomor_rm = null;
      if ($baru) {
         // ---- pasien tanpa identitas ----
         $jk = strtoupper(substr(trim((string) ($_POST['jk'] ?? '')), 0, 1)) === 'P' ? 'Perempuan' : 'Laki-laki';
         $umur = trim((string) ($_POST['umur'] ?? ''));
         $umur = ($umur !== '' && ctype_digit($umur) && (int) $umur <= 120) ? (int) $umur : null;
         $nama = trim((string) ($_POST['nama'] ?? ''));
         if ($nama === '') $nama = ($umur !== null && $umur < 17 ? 'An. ' : ($jk === 'Perempuan' ? 'Ny. ' : 'Tn. ')) . 'X (Tanpa Identitas)';
         $nama = mb_substr($nama, 0, 150);
         $ciri = mb_substr(trim((string) ($_POST['ciri'] ?? '')), 0, 1000);
         $lahir = $umur !== null ? (date('Y') - $umur) . '-01-01' : null;

         $st = $koneksi->prepare("SELECT nomor_rm_end FROM setting_clinic WHERE id_customer = ? FOR UPDATE");
         $st->bind_param('i', $id_customer);
         $st->execute();
         $row = $st->get_result()->fetch_assoc();
         $st->close();
         if (!$row) throw new Exception('Pengaturan nomor RM klinik belum ada');
         $newRM = (int) $row['nomor_rm_end'] + 1;
         $nomor_rm = str_pad((string) $newRM, 6, '0', STR_PAD_LEFT);
         $up = $koneksi->prepare("UPDATE setting_clinic SET nomor_rm_end = ? WHERE id_customer = ?");
         $up->bind_param('ii', $newRM, $id_customer);
         $up->execute();
         $up->close();
         $pct = 'PCT-' . strtoupper(bin2hex(random_bytes(4)));
         $tag = 'TANPA IDENTITAS';
         $desk = 'Pasien tanpa identitas' . ($ciri !== '' ? ': ' . $ciri : '');
         $reg = date('Y-m-d');
         $st = $koneksi->prepare("INSERT INTO ms_patient (patient_name, patient_gender, patient_datebirth, id_customer, nomor_rm, patient_number, tag, description, registration_date, patient_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
         $st->bind_param('sssisssss', $nama, $jk, $lahir, $id_customer, $nomor_rm, $pct, $tag, $desk, $reg);
         if (!$st->execute()) throw new Exception('Gagal membuat data pasien: ' . $st->error);
         $id_patient = (int) $st->insert_id;
         $st->close();
         $patient_name_pcare = $nama;
      } else {
         // ---- pasien terdaftar: wajib milik faskes ----
         $st = $koneksi->prepare("SELECT patient_name, nomor_rm FROM ms_patient WHERE id_patient = ? AND id_customer = ? LIMIT 1");
         $st->bind_param('ii', $id_patient, $id_customer);
         $st->execute();
         $pas = $st->get_result()->fetch_assoc();
         $st->close();
         if (!$pas) throw new Exception('Pasien tidak ditemukan di faskes ini');
         $patient_name_pcare = trim((string) ($_POST['patient_name_pcare'] ?? '')) ?: $pas['patient_name'];
         $nomor_rm = $pas['nomor_rm'];
         if (($_POST['paksa'] ?? '') !== '1' && $poli_name !== '') {
            $idStr = (string) $id_patient;
            $st = $koneksi->prepare("SELECT visit_antrian FROM pasien_visit WHERE id_patient = ? AND id_customer = ? AND visit_date = ? AND id_poli = ? AND visit_status <> 99 LIMIT 1");
            $st->bind_param('siss', $idStr, $id_customer, $visit_date, $poli_name);
            $st->execute();
            $dob = $st->get_result()->fetch_assoc();
            $st->close();
            if ($dob) {
               $koneksi->rollback();
               $jawab(['status' => 'error', 'dobel' => true, 'message' => "Pasien sudah terdaftar di $poli_name pada tanggal ini (antrian " . ($dob['visit_antrian'] ?: '-') . ")."]);
            }
         }
      }

      $visit_ID = generateVisitID($koneksi, $id_customer);
      $resultAntrian = createAntrian($koneksi, $kdPoli, $id_customer, $visit_ID, $id_doctor, $visit_date, $jampraktek);
      $nomorantrean = $resultAntrian['display'];
      $visit_status = 0;
      $status_antrian = 0;
      $idPasienStr = (string) $id_patient;
      $keluhanDb = $keluhan !== '' ? $keluhan : null;
      $stmt = $koneksi->prepare("INSERT INTO pasien_visit (id_patient, visit_ID, visit_date, visit_time, id_doctor, code_doctor, id_poli, source_hub, visit_status, created_user, visit_antrian, status_antrian, id_customer, patient_name_pcare, id_provider, anamnesa)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("ssssssssissiisis", $idPasienStr, $visit_ID, $visit_date, $visit_time, $doctor_name, $id_doctor, $poli_name, $source_hub,
         $visit_status, $created_user, $nomorantrean, $status_antrian, $id_customer, $patient_name_pcare, $id_provider, $keluhanDb);
      if (!$stmt->execute()) throw new Exception($stmt->error);
      $stmt->close();
      $koneksi->commit();
      $jawab(['status' => 'success', 'message' => 'Berhasil daftar', 'data' => [
         'visit_ID' => $visit_ID, 'antrian' => $nomorantrean, 'id_patient' => $id_patient, 'nomor_rm' => $nomor_rm, 'nama' => $patient_name_pcare,
      ]]);
   } catch (Throwable $e) {
      $koneksi->rollback();
      error_log('[visitController] ' . $e->getMessage());
      $jawab(['status' => 'error', 'message' => $e->getMessage()]);
   }
}

// ================= GENERATE VISIT ID =================
function generateVisitID($koneksi, $idcustomer)
{
   do {
      $date = date('ymd');
      $random = strtoupper(bin2hex(random_bytes(3)));
      $visitID = "VIS-" . $idcustomer . "-" . $date . "-" . $random;
      $count = '';
      $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_visit WHERE visit_ID=?");
      $check->bind_param("s", $visitID);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $visitID;
}
function createAntrian($koneksi, $kdPoli, $idcustomer, $visit_ID, $kdDokter, $tglDaftarDB, $jampraktek){
   $cekantrian = $koneksi->prepare("SELECT COALESCE(MAX(a.nomor), 0) AS last, (SELECT d.doctor_antrean FROM ms_doctor d WHERE d.doctor_code = ? AND d.id_customer = ? LIMIT 1) AS kode_antrian FROM antrian_poli a WHERE a.poli = ? AND a.tanggal = ? AND a.id_customer = ? AND a.kode_antri = (SELECT d.doctor_antrean FROM ms_doctor d WHERE d.doctor_code = ? AND d.id_customer = ? LIMIT 1) FOR UPDATE");
   $cekantrian->bind_param("sssssss", $kdDokter, $idcustomer, $kdPoli, $tglDaftarDB, $idcustomer, $kdDokter, $idcustomer);
   $cekantrian->execute();
   $rowantrian = $cekantrian->get_result()->fetch_assoc();
   $next = (int)$rowantrian['last'] + 1;
   $kode_antrian = $rowantrian['kode_antrian'];
   $createantrian = $koneksi->prepare("INSERT INTO antrian_poli (nomor, poli, tanggal, id_customer, nomor_visit,id_dokter, kode_antri, jampraktek)VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
   $createantrian->bind_param("isssssss", $next, $kdPoli, $tglDaftarDB, $idcustomer, $visit_ID, $kdDokter, $kode_antrian, $jampraktek);
   $createantrian->execute();
   return [
      'nomor' => $next,
      'kode' => $kode_antrian,
      'display' => $kode_antrian . $next
   ];
}
