<?php
/**
 * Tambah pasien baru dari hasil cek peserta BPJS (modal "Pasien baru" di module/admisi/pendaftaran).
 * POST noKartu (nomor yang dicari: NIK 16 / BPJS 13), nama, tgl_lahir, jenis_kelamin, no_bpjs, nik (opsional dari BPJS)
 * -> {success, message, id_patient, nomor_rm}
 *
 * Perbaikan:
 * - Nomor yang dicari selalu disimpan sebagai NIK (padahal bisa no. BPJS 13 digit) -> sekarang NIK hanya bila 16 digit.
 * - "SELECT ... FOR UPDATE" tanpa transaksi = tanpa kunci -> dua pendaftaran bersamaan bisa mendapat nomor RM sama.
 *   Sekarang dalam transaksi.
 * - patient_number dibuat tetapi tidak pernah disimpan.
 * - Jenis kelamin "Laki - Laki" & tanggal lahir dd-mm-yyyy dari BPJS dinormalkan ("Laki-laki", Y-m-d).
 * - id_patient dikembalikan agar halaman bisa langsung memilih pasien baru (dulu reload halaman).
 */
require_once __DIR__ . '/view.php';
header('Content-Type: application/json; charset=utf-8');
session_write_close();

function apJawab(array $d): void
{
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}

$idc = (int) $idcustomer;
$dicari = preg_replace('/\D/', '', (string) ($_POST['noKartu'] ?? ''));
$nikInput = preg_replace('/\D/', '', (string) ($_POST['nik'] ?? ''));
$nik = strlen($nikInput) === 16 ? $nikInput : (strlen($dicari) === 16 ? $dicari : '');
$noKartu = preg_replace('/\D/', '', (string) ($_POST['no_bpjs'] ?? ''));
if (strlen($noKartu) !== 13) $noKartu = strlen($dicari) === 13 ? $dicari : '';
$nama = trim((string) ($_POST['nama'] ?? ''));
$jkIn = strtoupper(substr(trim((string) ($_POST['jenis_kelamin'] ?? '')), 0, 1));
$jenisKelamin = $jkIn === 'L' ? 'Laki-laki' : ($jkIn === 'P' ? 'Perempuan' : '');
$tgl = trim((string) ($_POST['tgl_lahir'] ?? ''));
if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tgl, $m)) $tgl = "$m[3]-$m[2]-$m[1]";
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) $tgl = '';

if ($idc <= 0) apJawab(['success' => false, 'message' => 'Session tidak valid']);
if ($nama === '' || ($nik === '' && $noKartu === '')) apJawab(['success' => false, 'message' => 'Data peserta tidak lengkap (nama & NIK / no. BPJS).']);

// sudah terdaftar?
$w = [];
$t = 'i';
$p = [$idc];
if ($noKartu !== '') { $w[] = 'patient_bpjs = ?'; $t .= 's'; $p[] = $noKartu; }
if ($nik !== '') { $w[] = 'patient_nik = ?'; $t .= 's'; $p[] = $nik; }
$st = $koneksi->prepare("SELECT id_patient, nomor_rm, patient_name FROM ms_patient WHERE id_customer = ? AND (" . implode(' OR ', $w) . ") LIMIT 1");
$st->bind_param($t, ...$p);
$st->execute();
$ada = $st->get_result()->fetch_assoc();
$st->close();
if ($ada) {
    apJawab(['success' => false, 'sudah_ada' => true, 'id_patient' => (int) $ada['id_patient'], 'nomor_rm' => $ada['nomor_rm'],
        'message' => "Pasien sudah terdaftar: {$ada['patient_name']} (RM {$ada['nomor_rm']})."]);
}

$koneksi->begin_transaction();
try {
    $st = $koneksi->prepare("SELECT nomor_rm_end FROM setting_clinic WHERE id_customer = ? FOR UPDATE");
    $st->bind_param("i", $idc);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();
    if ($row) {
        $lastRM = (int) $row['nomor_rm_end'];
    } else {
        $lastRM = 0;
        $ins = $koneksi->prepare("INSERT INTO setting_clinic (id_customer, nomor_rm_end) VALUES (?, 0)");
        $ins->bind_param("i", $idc);
        $ins->execute();
        $ins->close();
    }
    $newRM = $lastRM + 1;
    $nomorRM = str_pad((string) $newRM, 6, "0", STR_PAD_LEFT);
    do {
        $patientNumber = "PCT-" . strtoupper(bin2hex(random_bytes(4)));
        $cek = $koneksi->prepare("SELECT COUNT(*) FROM ms_patient WHERE patient_number = ?");
        $cek->bind_param("s", $patientNumber);
        $cek->execute();
        $cek->bind_result($count);
        $cek->fetch();
        $cek->close();
    } while ($count > 0);

    $up = $koneksi->prepare("UPDATE setting_clinic SET nomor_rm_end = ? WHERE id_customer = ?");
    $up->bind_param("ii", $newRM, $idc);
    $up->execute();
    $up->close();

    $nikDb = $nik !== '' ? $nik : null;
    $bpjsDb = $noKartu !== '' ? $noKartu : null;
    $st = $koneksi->prepare("INSERT INTO ms_patient (patient_bpjs, patient_nik, patient_name, patient_gender, patient_datebirth, id_customer, nomor_rm, patient_number)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $st->bind_param('sssssiss', $bpjsDb, $nikDb, $nama, $jenisKelamin, $tgl, $idc, $nomorRM, $patientNumber);
    if (!$st->execute()) throw new RuntimeException($st->error);
    $idBaru = (int) $st->insert_id;
    $st->close();
    $koneksi->commit();
    require_once __DIR__ . '/../../../utility/activityLog.php';
    logAktivitas($koneksi, 'pasien', 'tambah', ['tipe' => 'pasien', 'id' => $idBaru, 'label' => $nomorRM . ' · ' . $nama], 'Pasien baru dari pendaftaran admisi (data peserta BPJS)',
        null, ['patient_bpjs' => $bpjsDb, 'patient_nik' => $nikDb, 'patient_name' => $nama, 'patient_gender' => $jenisKelamin, 'patient_datebirth' => $tgl, 'nomor_rm' => $nomorRM]);
} catch (Throwable $e) {
    $koneksi->rollback();
    error_log('[addDataPatient] ' . $e->getMessage());
    apJawab(['success' => false, 'message' => 'Gagal mendaftarkan pasien.']);
}

apJawab(['success' => true, 'message' => "Pasien ditambahkan dengan RM $nomorRM.", 'id_patient' => $idBaru, 'nomor_rm' => $nomorRM]);
