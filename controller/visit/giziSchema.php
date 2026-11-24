<?php
/**
 * Definisi formulir Asesmen Gizi & Nutrisi — dipakai bersama oleh halaman (module/admin/form_gizi),
 * controller (controller/visit/giziController.php) dan cetakan (module/admin/print/formulir_gizi.php).
 * Kunci field sama dengan form lama agar tidak membingungkan.
 *
 * Tipe: date, text, textarea, number{min,max,step,unit}, select{opsi}, radio{opsi}, checks{opsi},
 *       calc (dihitung otomatis, tidak diisi pengguna), lab (baris laboratorium), tabel{kolom}.
 */

function giziYaTidak(): array
{
   return ['Tidak', 'Ya'];
}

function giziLab(): array
{
   return [
      'hb' => ['Hemoglobin', 'g/dL', 0, 30],
      'gula' => ['Gula Darah', 'mg/dL', 0, 1500],
      'hba1c' => ['HbA1c', '%', 0, 25],
      'kolesterol' => ['Kolesterol Total', 'mg/dL', 0, 1000],
      'ldl' => ['LDL', 'mg/dL', 0, 800],
      'hdl' => ['HDL', 'mg/dL', 0, 300],
      'trigliserida' => ['Trigliserida', 'mg/dL', 0, 5000],
      'albumin' => ['Albumin', 'g/dL', 0, 10],
      'ureum' => ['Ureum', 'mg/dL', 0, 500],
      'kreatinin' => ['Kreatinin', 'mg/dL', 0, 30],
   ];
}

/** Bagian-bagian formulir: kode => [judul, ikon, [field => definisi]] */
function giziSkema(): array
{
   $n = fn($label, $unit, $min, $max, $step = 'any', $extra = []) => ['tipe' => 'number', 'label' => $label, 'unit' => $unit, 'min' => $min, 'max' => $max, 'step' => $step] + $extra;
   $ta = fn($label, $ph = '', $extra = []) => ['tipe' => 'textarea', 'label' => $label, 'ph' => $ph] + $extra;
   $sel = fn($label, $opsi, $extra = []) => ['tipe' => 'select', 'label' => $label, 'opsi' => $opsi] + $extra;
   return [
      'id' => ['Identitas Asesmen', 'ti-id', [
         'tanggal_asesmen' => ['tipe' => 'date', 'label' => 'Tanggal asesmen', 'wajib' => true, 'lebar' => 4],
         'nutrisionis' => ['tipe' => 'text', 'label' => 'Nutrisionis / Dietisien', 'wajib' => true, 'max' => 150, 'lebar' => 8],
      ]],
      'A' => ['Skrining Risiko Gizi', 'ti-filter', [
         'penurunan_bb' => ['tipe' => 'radio', 'label' => 'Penurunan berat badan tidak direncanakan?', 'opsi' => giziYaTidak(), 'lebar' => 4],
         'penurunan_asupan' => ['tipe' => 'radio', 'label' => 'Penurunan asupan makan?', 'opsi' => giziYaTidak(), 'lebar' => 4],
         'kebutuhan_gizi' => ['tipe' => 'radio', 'label' => 'Kondisi yang meningkatkan kebutuhan gizi?', 'opsi' => giziYaTidak(), 'lebar' => 4],
         'risiko_gizi' => $sel('Risiko gizi', ['Rendah', 'Sedang', 'Tinggi'], ['lebar' => 4, 'wajib_selesai' => true]),
         'catatan_skrining' => $ta('Catatan skrining', '', ['lebar' => 8, 'baris' => 2]),
      ]],
      'B' => ['Antropometri', 'ti-ruler-measure', [
         'bb' => $n('Berat badan', 'kg', 0.5, 350, 'any', ['lebar' => 3]),
         'tb' => $n('Tinggi badan', 'cm', 30, 250, 'any', ['lebar' => 3]),
         'imt' => ['tipe' => 'calc', 'label' => 'IMT', 'unit' => 'kg/m²', 'lebar' => 3],
         'kategori_imt' => ['tipe' => 'calc', 'label' => 'Kategori IMT', 'lebar' => 3],
         'bb_ideal' => ['tipe' => 'calc', 'label' => 'BB ideal (Broca)', 'unit' => 'kg', 'lebar' => 3],
         'bb_biasa' => $n('BB biasanya', 'kg', 0.5, 350, 'any', ['lebar' => 3]),
         'perubahan_bb' => ['tipe' => 'calc', 'label' => 'Perubahan BB', 'unit' => 'kg', 'lebar' => 3],
         'persen_perubahan_bb' => ['tipe' => 'calc', 'label' => 'Perubahan BB', 'unit' => '%', 'lebar' => 3],
         'periode_perubahan_bb' => $n('Periode perubahan BB', 'bulan', 0, 120, 'any', ['lebar' => 3]),
         'llla' => $n('Lingkar lengan atas', 'cm', 5, 80, 'any', ['lebar' => 3]),
         'lingkar_pinggang' => $n('Lingkar pinggang', 'cm', 20, 250, 'any', ['lebar' => 3]),
         'lingkar_pinggul' => $n('Lingkar pinggul', 'cm', 20, 250, 'any', ['lebar' => 3]),
      ]],
      'C' => ['Biokimia / Laboratorium', 'ti-flask', [
         'lab' => ['tipe' => 'lab', 'label' => 'Hasil laboratorium'],
      ]],
      'D' => ['Pemeriksaan Klinis / Fisik', 'ti-stethoscope', [
         'nafsu_makan' => $sel('Nafsu makan', ['Baik', 'Menurun', 'Meningkat'], ['lebar' => 4]),
         'kesadaran_gizi' => $sel('Kesadaran', ['Compos Mentis', 'Somnolen', 'Sopor', 'Koma'], ['lebar' => 4]),
         'kondisi_umum' => $sel('Kondisi umum', ['Baik', 'Sedang', 'Lemah'], ['lebar' => 4]),
         'keluhan_gizi' => ['tipe' => 'checks', 'label' => 'Keluhan / gejala terkait gizi', 'opsi' => ['Mual', 'Muntah', 'Diare', 'Konstipasi', 'Sulit Menelan', 'Gangguan Mengunyah', 'Kembung', 'Nyeri Abdomen']],
         'kondisi_fisik' => ['tipe' => 'checks', 'label' => 'Kondisi fisik khusus', 'opsi' => ['Edema', 'Asites', 'Dehidrasi', 'Kehilangan Massa Otot']],
         'catatan_klinis' => $ta('Catatan klinis', '', ['baris' => 2]),
      ]],
      'E' => ['Riwayat Gizi (Dietary History)', 'ti-tools-kitchen-2', [
         'frekuensi_makan' => $n('Makan utama', 'kali/hari', 0, 10, '1', ['lebar' => 4]),
         'frekuensi_snack' => $n('Snack', 'kali/hari', 0, 10, '1', ['lebar' => 4]),
         'pola_makan' => $sel('Pola makan', ['Teratur', 'Tidak Teratur'], ['lebar' => 4]),
         'recall' => ['tipe' => 'tabel', 'label' => 'Recall 24 jam', 'kolom' => [
            'waktu' => ['Waktu', 'time'], 'makanan' => ['Makanan / minuman', 'text', 'mis. nasi, ayam, sayur'], 'jumlah' => ['Jumlah', 'text'], 'urt' => ['URT', 'text', 'piring / potong / gelas'],
         ]],
         'asupan_energi' => $n('Estimasi asupan energi', 'kkal', 0, 10000, 'any', ['lebar' => 4]),
         'asupan_protein' => $n('Estimasi asupan protein', 'g', 0, 500, 'any', ['lebar' => 4]),
         'asupan_cairan' => $n('Asupan cairan', 'mL/hari', 0, 10000, 'any', ['lebar' => 4]),
      ]],
      'F' => ['Riwayat Personal', 'ti-user', [
         'pekerjaan' => ['tipe' => 'text', 'label' => 'Pekerjaan', 'max' => 150, 'lebar' => 4],
         'aktivitas_fisik' => $sel('Aktivitas fisik', ['Sangat Ringan', 'Ringan', 'Sedang', 'Berat'], ['lebar' => 4]),
         'merokok' => $sel('Merokok', ['Tidak', 'Ya'], ['lebar' => 4]),
         'alergi_makanan' => $ta('Alergi makanan', '', ['lebar' => 6, 'baris' => 2]),
         'intoleransi_makanan' => $ta('Intoleransi makanan', '', ['lebar' => 6, 'baris' => 2]),
         'diet_sebelumnya' => $ta('Diet sebelumnya', '', ['lebar' => 6, 'baris' => 2]),
         'pantangan_makanan' => $ta('Pantangan / makanan yang dihindari', '', ['lebar' => 6, 'baris' => 2]),
      ]],
      'G' => ['Kesimpulan Asesmen', 'ti-file-description', [
         'kesimpulan_asesmen' => $ta('Kesimpulan asesmen gizi', 'Ringkasan hasil asesmen gizi…', ['baris' => 3]),
      ]],
      'H' => ['Diagnosis Gizi (PES)', 'ti-clipboard-heart', [
         'diagnosis_domain' => $sel('Domain', ['Intake', 'Clinical', 'Behavioral / Environmental'], ['lebar' => 4]),
         'diagnosis_problem' => ['tipe' => 'text', 'label' => 'Problem (P)', 'ph' => 'mis. Asupan energi tidak adekuat', 'max' => 255, 'lebar' => 8, 'wajib_selesai' => true],
         'diagnosis_etiologi' => $ta('Etiologi (E)', 'berkaitan dengan…', ['lebar' => 6, 'baris' => 2]),
         'diagnosis_tanda' => $ta('Signs / Symptoms (S)', 'dibuktikan dengan…', ['lebar' => 6, 'baris' => 2]),
         'diagnosis_pes' => ['tipe' => 'calc', 'label' => 'Diagnosis gizi (PES) — tersusun otomatis', 'panjang' => true],
      ]],
      'I' => ['Intervensi & Preskripsi Diet', 'ti-bowl', [
         'tujuan_intervensi' => ['tipe' => 'checks', 'label' => 'Tujuan intervensi', 'opsi' => ['Meningkatkan Asupan Energi', 'Meningkatkan Berat Badan', 'Menurunkan Berat Badan', 'Kontrol Gula Darah', 'Kontrol Tekanan Darah', 'Perbaikan Status Gizi']],
         'jenis_diet' => $sel('Jenis diet', ['Diet Biasa', 'Diet Lunak', 'Diet Saring', 'Diet Cair', 'Diet DM', 'Diet Rendah Garam', 'Diet Rendah Lemak', 'Diet Rendah Protein', 'Diet Tinggi Protein', 'Diet Ginjal', 'Diet Hati', 'Diet Jantung', 'Diet Tinggi Kalori Tinggi Protein', 'Diet Lainnya'], ['lebar' => 6, 'wajib_selesai' => true]),
         'bentuk_makanan' => $sel('Bentuk makanan', ['Biasa', 'Lunak', 'Saring', 'Cair'], ['lebar' => 6]),
         'target_energi' => $n('Energi', 'kkal/hari', 0, 10000, 'any', ['lebar' => 4]),
         'target_protein' => $n('Protein', 'g/hari', 0, 500, 'any', ['lebar' => 4]),
         'target_lemak' => $n('Lemak', 'g/hari', 0, 500, 'any', ['lebar' => 4]),
         'target_karbohidrat' => $n('Karbohidrat', 'g/hari', 0, 1500, 'any', ['lebar' => 4]),
         'target_cairan' => $n('Cairan', 'mL/hari', 0, 10000, 'any', ['lebar' => 4]),
         'target_frekuensi_makan' => $n('Frekuensi makan utama', 'kali/hari', 0, 10, '1', ['lebar' => 2]),
         'target_frekuensi_snack' => $n('Snack', 'kali/hari', 0, 10, '1', ['lebar' => 2]),
         'modifikasi_diet' => $ta('Pembatasan / modifikasi diet', 'mis. pembatasan gula sederhana, natrium, lemak jenuh, cairan', ['lebar' => 6, 'baris' => 2]),
         'instruksi_diet' => $ta('Instruksi diet', 'Instruksi diet untuk pasien…', ['lebar' => 6, 'baris' => 2]),
      ]],
      'J' => ['Edukasi Gizi', 'ti-school', [
         'edukasi_gizi' => ['tipe' => 'checks', 'label' => 'Topik edukasi', 'opsi' => ['Pemilihan Makanan', 'Porsi Makan', 'Jadwal Makan', 'Diet Penyakit', 'Pembatasan Gula', 'Pembatasan Garam', 'Aktivitas Fisik', 'Label Pangan']],
         'materi_edukasi' => $ta('Materi / catatan edukasi', '', ['baris' => 2]),
      ]],
      'K' => ['Monitoring & Evaluasi', 'ti-chart-line', [
         'monitoring' => ['tipe' => 'tabel', 'label' => 'Monitoring', 'kolom' => [
            'parameter' => ['Parameter', 'text', 'mis. berat badan'], 'target' => ['Target', 'text'], 'hasil' => ['Hasil', 'text'], 'tanggal' => ['Tanggal', 'date'],
            'status' => ['Status', 'select', ['Membaik', 'Tetap', 'Memburuk']], 'keterangan' => ['Keterangan', 'text'],
         ]],
         'evaluasi_gizi' => $ta('Evaluasi', '', ['lebar' => 6, 'baris' => 2]),
         'rencana_tindak_lanjut' => $ta('Rencana tindak lanjut', '', ['lebar' => 6, 'baris' => 2]),
         'tanggal_kontrol_gizi' => ['tipe' => 'date', 'label' => 'Rencana kontrol gizi', 'lebar' => 4],
      ]],
   ];
}

/** Kategori IMT dewasa (Kemenkes RI). Anak < 18 th memakai IMT/U sehingga tidak dikategorikan di sini. */
function giziKategoriImt(?float $imt, ?int $umur): string
{
   if ($imt === null) return '';
   if ($umur !== null && $umur < 18) return 'Anak: gunakan IMT/U';
   if ($imt < 17) return 'Kurus berat';
   if ($imt < 18.5) return 'Kurus ringan';
   if ($imt <= 25) return 'Normal';
   if ($imt <= 27) return 'Gemuk ringan';
   return 'Obesitas';
}

/**
 * Hitungan otomatis (sama dengan versi JavaScript di halaman):
 * IMT, kategori IMT, BB ideal Broca (90% × (TB−100); pria TB<160 / wanita TB<150 tanpa pengurangan 10%),
 * perubahan BB (kg, bertanda) & persen, dan kalimat PES.
 */
function giziHitung(array $d, string $jk, ?int $umur): array
{
   $f = fn($k) => isset($d[$k]) && $d[$k] !== '' && is_numeric($d[$k]) ? (float) $d[$k] : null;
   $bb = $f('bb');
   $tb = $f('tb');
   $biasa = $f('bb_biasa');
   $imt = ($bb && $tb) ? round($bb / (($tb / 100) ** 2), 1) : null;
   $d['imt'] = $imt === null ? '' : (string) $imt;
   $d['kategori_imt'] = giziKategoriImt($imt, $umur);
   if ($tb && $tb > 100) {
      $wanita = stripos($jk, 'p') === 0;
      $tanpaPotong = ($wanita && $tb < 150) || (!$wanita && $tb < 160);
      $d['bb_ideal'] = (string) round(($tb - 100) * ($tanpaPotong ? 1 : 0.9), 1);
   } else {
      $d['bb_ideal'] = '';
   }
   if ($bb && $biasa) {
      $selisih = round($bb - $biasa, 1);
      $d['perubahan_bb'] = (string) $selisih;
      $d['persen_perubahan_bb'] = (string) round($selisih / $biasa * 100, 1);
   } else {
      $d['perubahan_bb'] = $d['persen_perubahan_bb'] = '';
   }
   $p = trim((string) ($d['diagnosis_problem'] ?? ''));
   $e = trim((string) ($d['diagnosis_etiologi'] ?? ''));
   $s = trim((string) ($d['diagnosis_tanda'] ?? ''));
   $d['diagnosis_pes'] = $p === '' ? '' : $p . ($e !== '' ? ' berkaitan dengan ' . $e : '') . ($s !== '' ? ' dibuktikan dengan ' . $s : '');
   return $d;
}
