<?php
// Kirim ulang terapi obat & BMHP sebuah kunjungan ke PCare (PUT /kunjungan/v1).
// Dipakai oleh kirimObat.php (kirim tiket baru) dan sinkronObatPcare.php
// (obat ditambah/diubah pada tiket yang sudah terkirim).
//
// Prasyarat pemanggil: servicebpjs.php (bpjsPost) & rmeCycleService.php (rmeCycleTherapy) sudah di-include.

/**
 * Bangun terapiObat/bmhp dari seluruh tiket kunjungan yang sudah terkirim (status >= 1)
 * ditambah tiket $ticketIdsBaru, lalu kirim ke PCare dan simpan ke pcare_kunjungan.
 *
 * @return array ['ok' => bool, 'message' => string, 'result' => mixed]
 * @throws RuntimeException bila data lokal tidak lengkap (terapi kosong / kunjungan PCare belum ada)
 */
function pcareKirimTerapiVisit(mysqli $koneksi, string $id_customer, string $nomor_visit, array $ticketIdsBaru = []): array
{
   $ticketIdsBaru = array_values(array_map('strval', $ticketIdsBaru));
   $kondisiTiket = 'pp.status_permintaan >= 1';
   if ($ticketIdsBaru) {
      $placeholders = implode(',', array_fill(0, count($ticketIdsBaru), '?'));
      $kondisiTiket = "(pp.id_permintaan_farmasi IN ($placeholders) OR pp.status_permintaan >= 1)";
   }
   $vals = array_merge([$id_customer, $nomor_visit], $ticketIdsBaru);
   $types = str_repeat('s', count($vals));

   // header tiket (referensi rmeCycleTherapy)
   $stmt = $koneksi->prepare("
      SELECT pp.id_permintaan_farmasi, pp.id_visit, pp.status_permintaan, pp.rck_signa
      FROM permintaan_pharmacy AS pp
      WHERE pp.id_customer = ? AND pp.id_visit = ? AND $kondisiTiket
      ORDER BY pp.id_permintaan_farmasi ASC
   ");
   $stmt->bind_param($types, ...$vals);
   $stmt->execute();
   $allTickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   $stmt = $koneksi->prepare("
      SELECT pd.*, p.pharmacy_category, p.pharmacy_name_generic, pp.rck_signa
      FROM permintaan_pharmacy_details AS pd
      INNER JOIN permintaan_pharmacy AS pp ON pp.id_permintaan_farmasi = pd.id_permintaan_farmasi
      LEFT JOIN ms_pharmacy AS p ON p.id_pharmacy = pd.id_pharmacy
      WHERE pp.id_customer = ? AND pp.id_visit = ? AND $kondisiTiket
      ORDER BY pp.id_permintaan_farmasi ASC, pd.id_pharmacy_details ASC
   ");
   $stmt->bind_param($types, ...$vals);
   $stmt->execute();
   $detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   $hasilTerapi = rmeCycleTherapy($allTickets, $detail);
   $terapiObat = $hasilTerapi['terapiObat'];
   $bmhp = $hasilTerapi['bmhp'];

   if ($terapiObat === '' && $bmhp === '') {
      throw new RuntimeException('Data terapi obat dan BMHP kosong setelah diproses. Periksa kelengkapan item.');
   }

   // INNER JOIN id_patient agar tidak gagal jika format noKartu berbeda
   $stmt = $koneksi->prepare("
      SELECT pv.visit_ID, pv.id_patient, pk.*
      FROM pasien_visit AS pv
      INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan
      INNER JOIN ms_patient AS p ON p.id_patient = pv.id_patient
      WHERE pv.visit_ID = ? AND pv.id_customer = ?
   ");
   $stmt->bind_param('ss', $nomor_visit, $id_customer);
   $stmt->execute();
   $data = $stmt->get_result()->fetch_assoc();
   $stmt->close();

   $noKunjungan = $data['noKunjungan'] ?? null;
   if (!$noKunjungan) {
      throw new RuntimeException('Kunjungan BPJS (PCare) belum tersedia untuk pasien ini. Simpan pemeriksaan terlebih dahulu.');
   }

   $rujukLanjut = null;
   if (!empty($data['rujukLanjut'])) {
      $decodedRujuk = is_string($data['rujukLanjut']) ? json_decode($data['rujukLanjut'], true) : $data['rujukLanjut'];
      $rujukLanjut = is_array($decodedRujuk) ? $decodedRujuk : null;
   }

   $payload = [
      "noKunjungan" => $noKunjungan,
      "noKartu" => $data['noKartu'] ?? null,
      "tglDaftar" => !empty($data['tglDaftar']) ? date("d-m-Y", strtotime($data['tglDaftar'])) : null,
      "kdPoli" => $data['kdPoli'] ?? null,
      "keluhan" => $data['keluhan'] ?? null,
      "kdSadar" => $data['kdSadar'] ?? null,
      "sistole" => $data['sistole'] ?? null,
      "diastole" => $data['diastole'] ?? null,
      "beratBadan" => isset($data['beratBadan']) ? (float)$data['beratBadan'] : 0,
      "tinggiBadan" => isset($data['tinggiBadan']) ? (float)$data['tinggiBadan'] : 0,
      "respRate" => $data['respRate'] ?? null,
      "heartRate" => $data['heartRate'] ?? null,
      "lingkarPerut" => $data['lingkarPerut'] ?? null,
      "kdStatusPulang" => $data['kdStatusPulang'] ?? null,
      "tglPulang" => !empty($data['tglPulang']) ? date("d-m-Y", strtotime($data['tglPulang'])) : null,
      "kdDokter" => $data['kdDokter'] ?? null,
      "kdDiag1" => $data['kdDiag1'] ?? null,
      "kdDiag2" => $data['kdDiag2'] ?? null,
      "kdDiag3" => $data['kdDiag3'] ?? null,
      "kdPoliRujukInternal" => $data['kdPoliRujukInternal'] ?? null,
      "rujukLanjut" => $rujukLanjut,
      "kdTacc" => (string)($data['kdTacc'] ?? ''),
      "alasanTacc" => $data['alasanTacc'] ?? null,
      "anamnesa" => $data['anamnesa'] ?? null,
      "alergiMakan" => $data['alergiMakan'] ?? null,
      "alergiUdara" => $data['alergiUdara'] ?? null,
      "alergiObat" => $data['alergiObat'] ?? null,
      "kdPrognosa" => $data['kdPrognosa'] ?? null,
      "terapiObat" => $terapiObat !== '' ? $terapiObat : "Tidak Ada Obat",
      "terapiNonObat" => $data['terapiNonObat'] ?? null,
      "bmhp" => $bmhp !== '' ? $bmhp : "Tidak Ada Bmhp",
      "suhu" => $data['suhu'] ?? null
   ];

   $resultBpjs = bpjsPost("/kunjungan/v1", $payload, 'PUT');

   // validasi ketat response BPJS
   $bpjsCode = null;
   $bpjsMsg = null;
   if (is_array($resultBpjs)) {
      $bpjsCode = $resultBpjs['code'] ?? ($resultBpjs['metaData']['code'] ?? null);
      $bpjsMsg = $resultBpjs['message'] ?? ($resultBpjs['metaData']['message'] ?? null);
   }
   if (!(is_scalar($bpjsCode) && in_array((string)$bpjsCode, ['200', '201'], true))) {
      return [
         'ok' => false,
         'message' => $bpjsMsg ?: 'Layanan BPJS sedang tidak dapat diakses atau menolak data.',
         'result' => $resultBpjs,
      ];
   }

   $terapiObatDb = $terapiObat !== '' ? $terapiObat : 'Tidak Ada';
   $bmhpDb = $bmhp !== '' ? $bmhp : null;
   $stmt = $koneksi->prepare("UPDATE pcare_kunjungan SET terapiObat = ?, bmhp = ? WHERE noKunjungan = ?");
   $stmt->bind_param('sss', $terapiObatDb, $bmhpDb, $noKunjungan);
   $ok = $stmt->execute();
   $err = $stmt->error;
   $stmt->close();
   if (!$ok) {
      throw new RuntimeException($err ?: 'Gagal memperbarui data kunjungan lokal');
   }

   return ['ok' => true, 'message' => 'Terapi obat terkirim ke PCare.', 'result' => $resultBpjs];
}
