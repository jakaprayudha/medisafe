<?php
include '../../database/connect.php'; // file koneksi
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/activityLog.php';

/** Daftar anamnesa / terapi terstruktur satu kunjungan (untuk log sebelum/sesudah). */
function spDaftar(mysqli $db, string $tabel, string $kolomId, string $no): array
{
   $st = $db->prepare("SELECT $kolomId AS id, detail FROM $tabel WHERE nomor_visit = ? ORDER BY $kolomId");
   $st->bind_param('s', $no);
   $st->execute();
   $out = [];
   foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $out[] = $r['id'] . ($r['detail'] !== null && $r['detail'] !== '' ? ': ' . $r['detail'] : '');
   $st->close();
   return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $nomor_rm = $_POST['nomor_rm'];
   $nomor_visit = $_POST['nomor_visit'];
   $kondisi_masuk = $_POST['kondisi_masuk'];
   $tekanan_darah = $_POST['tekanan_darah'];
   $suhu = $_POST['suhu'];
   $nadi = $_POST['nadi'];
   $respirasi = $_POST['respirasi'];
   $tinggi = $_POST['tinggi'];
   $berat = $_POST['berat'];
   $analyst = $_POST['analyst'];
   $riwayat_konsumsi = $_POST['riwayat_konsumsi'];
   $pemeriksaan_fisik = $_POST['pemeriksaan_fisik'];
   $bmi = $_POST['bmi'];
   $bmi_ket = $_POST['bmi_ket'];
   $diagnosa = $_POST['diagnosa'];
   $anamnesa_text = $_POST['anamnesa_text'];

   // hanya kunjungan faskes sesi (dulu kunjungan faskes mana pun bisa diubah lewat nomor_visit)
   visitGuard($koneksi, 'visit_ID', $nomor_visit);
   $spLama = logAmbilBaris($koneksi, 'pasien_visit', 'visit_ID', $nomor_visit);
   $spAnamLama = spDaftar($koneksi, 'visit_anamnesa', 'id_anamnesa_detail', (string) $nomor_visit);
   $spTerapiLama = spDaftar($koneksi, 'visit_terapi', 'id_terapi', (string) $nomor_visit);

   $koneksi->begin_transaction();
   try {
      // 1. Update pemeriksaan perawat/dokter
      // analyst & riwayat_konsumsi tidak punya kolom di pasien_visit (prod) -> tidak disimpan
      $stmt = $koneksi->prepare("UPDATE pasien_visit SET kondisi_masuk=?, tekanan_darah=?, suhu=?, nadi=?, respirasi=?, tinggi_badan=?, berat_badan=?, pemeriksaan_fisik=?, bmi=?, bmi_keterangan=?, diagnosa=?, anamnesa=? WHERE visit_ID=?");
      $stmt->bind_param("sssssssssssss", $kondisi_masuk, $tekanan_darah, $suhu, $nadi, $respirasi, $tinggi, $berat, $pemeriksaan_fisik, $bmi, $bmi_ket, $diagnosa, $anamnesa_text, $nomor_visit);

      $stmt->execute();
      $stmt->close();

      // 2. Simpan anamnesa
      // 2. Hapus anamnesa lama lalu simpan ulang
      $stmt = $koneksi->prepare("DELETE FROM visit_anamnesa WHERE nomor_visit=?");
      $stmt->bind_param("s", $nomor_visit);
      $stmt->execute();
      $stmt->close();

      if (isset($_POST['anamnesa'])) {
         foreach ($_POST['anamnesa'] as $id_anamnesa => $detail) {
            $stmt = $koneksi->prepare("INSERT INTO visit_anamnesa (nomor_visit, id_anamnesa_detail, detail) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nomor_visit, $id_anamnesa, $detail);
            $stmt->execute();
            $stmt->close();
         }
      }

      // 3. Simpan terapi
      // 3. Hapus terapi lama lalu simpan ulang
      $stmt = $koneksi->prepare("DELETE FROM visit_terapi WHERE nomor_visit=?");
      $stmt->bind_param("s", $nomor_visit);
      $stmt->execute();
      $stmt->close();

      if (isset($_POST['terapi'])) {
         foreach ($_POST['terapi'] as $id_terapi => $detail) {
            // cek kalau kosong, skip
            if (empty(trim($detail))) continue;

            $stmt = $koneksi->prepare("INSERT INTO visit_terapi (nomor_visit, id_terapi, detail) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nomor_visit, $id_terapi, $detail);
            $stmt->execute();
            $stmt->close();
         }
      }
      $koneksi->commit();
      // log setelah commit: kolom kunjungan yang berubah + anamnesa/terapi terstruktur
      $kol = ['kondisi_masuk', 'tekanan_darah', 'suhu', 'nadi', 'respirasi', 'tinggi_badan', 'berat_badan', 'pemeriksaan_fisik', 'bmi', 'bmi_keterangan', 'diagnosa', 'anamnesa'];
      $spBaru = logAmbilBaris($koneksi, 'pasien_visit', 'visit_ID', $nomor_visit) ?? [];
      [$l, $b] = logBeda(array_intersect_key($spLama ?? [], array_flip($kol)), array_intersect_key($spBaru, array_flip($kol)));
      $anamBaru = spDaftar($koneksi, 'visit_anamnesa', 'id_anamnesa_detail', (string) $nomor_visit);
      $terapiBaru = spDaftar($koneksi, 'visit_terapi', 'id_terapi', (string) $nomor_visit);
      if ($anamBaru !== $spAnamLama) { $l['anamnesa_terstruktur'] = $spAnamLama; $b['anamnesa_terstruktur'] = $anamBaru; }
      if ($terapiBaru !== $spTerapiLama) { $l['terapi'] = $spTerapiLama; $b['terapi'] = $terapiBaru; }
      if ($b) logAktivitas($koneksi, 'klinis', $spLama && trim((string) ($spLama['pemeriksaan_fisik'] ?? '') . ($spLama['diagnosa'] ?? '') . ($spLama['anamnesa'] ?? '')) !== '' ? 'ubah' : 'simpan',
         ['tipe' => 'pemeriksaan', 'id' => $nomor_visit, 'label' => 'Pemeriksaan · RM ' . $nomor_rm, 'visit_ID' => $nomor_visit],
         'Form pemeriksaan: ' . implode(', ', array_keys($b)), $l ?: null, $b);
      // Dinkes: data sudah tersimpan; kegagalan kirim tidak menghalangi (status ada di monitoring-dinkes)
      // auto-kirim dinonaktifkan; kirim manual lewat tombol / monitoring-dinkes
      // require_once __DIR__ . '/../dinkes/dinkesService.php';
      // dkKirimVisit($koneksi, (int) ($_SESSION['id_customer'] ?? 0), (string) $nomor_visit);
      echo json_encode(['status' => 'success', 'message' => 'Data pemeriksaan berhasil disimpan']);
   } catch (Exception $e) {
      $koneksi->rollback();
      echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
   }
}
