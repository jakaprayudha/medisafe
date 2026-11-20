<?php
/**
 * Proses gabung RM (dipanggil controller/admin/gabungRme.php). Dipisah agar bisa diuji tanpa HTTP.
 *
 * Semua dalam satu transaksi:
 *  1. Kunci baris pasien (utama + yang digabung), wajib satu faskes.
 *  2. Validasi: konfirmasi = nomor RM utama; semua RM terhubung lewat NIK 16 digit / no. BPJS 13 digit valid;
 *     bila tanggal lahir / jenis kelamin / nama berbeda jauh -> perlu konfirmasi ulang (paksa).
 *  3. Pindahkan semua data per id_patient (kunjungan, CPPT, permintaan ranap, surat-surat, ...).
 *  4. Perbarui nomor_rm di tabel RME yang menyimpan nomor RM, HANYA untuk kunjungan yang dipindah
 *     (nomor_rm tidak unik dalam satu faskes, jadi tidak boleh update berdasarkan nomor RM saja).
 *  5. Lengkapi kolom identitas RM utama yang kosong dari RM yang digabung; catat RM lama di nomor_rm_any.
 *  6. Simpan cadangan (baris pasien lengkap + daftar kunjungan) ke logs/merge_rme/, baru hapus pasien lama.
 */

/** Tabel yang menyimpan id_patient. */
const G_TABEL_ID = ['pasien_visit', 'visit_cppt', 'permintaan_ranap', 'surat_sakit', 'surat_berobat', 'surat_kematian',
   'surat_pemeriksaan_mata', 'surat_rawat_inap', 'surat_sehat', 'pasien_surat_ranap', 'pasien_ttd_pernyataan'];

/** Tabel yang menyimpan nomor_rm + kolom kunjungan (berisi visit_ID). */
const G_TABEL_RM = ['buku_kia' => 'nomor_visit', 'ekg_results' => 'visit_ID', 'ibu_hamil_header' => 'visit_ID', 'kb_card' => 'visit_ID',
   'lab_results' => 'visit_id', 'lbp_bpjs' => 'visit_ID', 'partograf_header' => 'visit_ID', 'pasien_dokumen' => 'visit_ID',
   'pasien_icd' => 'nomor_visit', 'pasien_persalinan_anc' => 'id_visit', 'pasien_triase' => 'visit_ID', 'pasien_ttd_pernyataan' => 'visit_ID',
   'pemeriksaan_trimester3' => 'visit_ID', 'persalinan_header' => 'visit_ID', 'resume_medis' => 'visit_ID', 'skl_kelahiran' => 'visit_id',
   'skrining_hipotiroid' => 'visit_id', 'sp_gigi' => 'visit_ID', 'usg_results' => 'visit_ID', 'visit_cppt_dokter' => 'visit_ID',
   'visit_kb_consent' => 'visit_ID', 'visit_kb_status' => 'visit_ID', 'visit_persalinan' => 'visit_ID', 'visit_ranap' => 'visit_ID'];

/** Kolom identitas yang diisi dari RM yang digabung bila di RM utama masih kosong. */
const G_KOLOM_LENGKAPI = ['patient_nik', 'idsh', 'patient_bpjs', 'patient_datebirth', 'patient_gender', 'patient_phone', 'patient_mail',
   'patient_address', 'patient_place', 'patient_religion', 'patient_blood', 'patient_provinsi', 'patient_kabupaten', 'patient_kecamatan',
   'patient_kelurahan', 'patient_desa', 'patient_marital_status', 'patient_nationality', 'patient_education', 'patient_occupation',
   'patient_emergency_contact_name', 'patient_emergency_contact_relation', 'patient_emergency_contact_phone', 'patient_allergy',
   'patient_disability', 'patient_kk', 'patient_ktp'];

/** Kolom yang benar-benar ada (lingkungan lama mungkin belum punya semua tabel). */
function gKolomAda(mysqli $db, array $tabel): array
{
   $ada = [];
   foreach (gQ($db, "SELECT TABLE_NAME t, COLUMN_NAME c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (" . gIn(count($tabel)) . ")",
      str_repeat('s', count($tabel)), $tabel) as $r) $ada[$r['t']][strtolower($r['c'])] = $r['c'];
   return $ada;
}

function gNamaNormal(string $s): string
{
   $s = strtoupper($s);
   $s = preg_replace('/\b(TN|NY|NN|AN|BY|NONA|SDR|SDRI|DR|DRG|H|HJ|IR|ALM|ALMH)\b\.?/', ' ', $s);
   $s = preg_replace('/[^A-Z ]+/', ' ', $s);
   return trim(preg_replace('/\s+/', ' ', $s));
}

/** Kemiripan nama 0-100. */
function gMiripNama(string $a, string $b): int
{
   $a = gNamaNormal($a);
   $b = gNamaNormal($b);
   if ($a === '' || $b === '') return 100;
   if ($a === $b || str_contains($a, $b) || str_contains($b, $a)) return 100;
   similar_text($a, $b, $p);
   return (int) round($p);
}

function gTglValid($v): string
{
   $v = substr(trim((string) $v), 0, 10);
   return (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && $v > '1900-01-01') ? $v : '';
}

function gJk($v): string
{
   $v = strtoupper(trim((string) $v));
   if ($v === '') return '';
   return in_array($v[0], ['L', 'M', '1'], true) ? 'L' : (in_array($v[0], ['P', 'F', '2', 'W'], true) ? 'P' : '');
}

/** Perbedaan identitas yang perlu dipastikan petugas (bukan blokir permanen). */
function gPeringatan(array $utama, array $t): array
{
   $w = [];
   $la = gTglValid($utama['patient_datebirth'] ?? '');
   $lb = gTglValid($t['patient_datebirth'] ?? '');
   if ($la && $lb && $la !== $lb) $w[] = "tanggal lahir berbeda ($lb vs $la)";
   $ja = gJk($utama['patient_gender'] ?? '');
   $jb = gJk($t['patient_gender'] ?? '');
   if ($ja && $jb && $ja !== $jb) $w[] = 'jenis kelamin berbeda';
   $m = gMiripNama((string) $utama['patient_name'], (string) $t['patient_name']);
   if ($m < 60) $w[] = "nama berbeda (kemiripan $m%)";
   return $w;
}

function gProsesGabung(mysqli $db, int $idc, int $utamaId, array $gabungIds, string $konfirmasi, bool $paksa, string $oleh): array
{
   $semua = array_merge([$utamaId], $gabungIds);
   $ada = gKolomAda($db, array_values(array_unique(array_merge(G_TABEL_ID, array_keys(G_TABEL_RM)))));

   if (!$db->begin_transaction()) throw new RuntimeException('Transaksi gagal dimulai.');
   try {
      // 1. kunci baris pasien
      $rows = gQ($db, "SELECT * FROM ms_patient WHERE id_customer = ? AND id_patient IN (" . gIn(count($semua)) . ") FOR UPDATE",
         'i' . str_repeat('i', count($semua)), array_merge([$idc], $semua));
      $p = [];
      foreach ($rows as $r) $p[(int) $r['id_patient']] = $r;
      foreach ($semua as $i) {
         if (!isset($p[$i])) {
            $db->rollback();
            return ['status' => 'error', 'message' => 'Salah satu RM tidak ditemukan di faskes ini (mungkin sudah digabung). Muat ulang halaman.', '_kode' => 404];
         }
      }
      $utama = $p[$utamaId];

      // 2a. konfirmasi = nomor RM utama
      if (strcasecmp($konfirmasi, trim((string) $utama['nomor_rm'])) !== 0) {
         $db->rollback();
         return ['status' => 'error', 'message' => 'Konfirmasi tidak sesuai. Ketik nomor RM utama: ' . $utama['nomor_rm'], '_kode' => 422];
      }

      // 2b. semua RM harus terhubung lewat NIK / no. BPJS valid
      $terhubung = [$utamaId => true];
      $kunci = array_flip(gKunci($utama));
      for ($ubah = true; $ubah;) {
         $ubah = false;
         foreach ($gabungIds as $i) {
            if (isset($terhubung[$i])) continue;
            $k = gKunci($p[$i]);
            if (array_intersect_key(array_flip($k), $kunci)) {
               $terhubung[$i] = true;
               $kunci += array_flip($k);
               $ubah = true;
            }
         }
      }
      $lepas = array_diff($gabungIds, array_keys($terhubung));
      if ($lepas) {
         $db->rollback();
         $rm = implode(', ', array_map(fn($i) => $p[$i]['nomor_rm'], $lepas));
         return ['status' => 'error', 'message' => "RM $rm tidak memiliki NIK (16 digit) atau no. kartu BPJS (13 digit) valid yang sama dengan RM utama.", '_kode' => 422];
      }

      // 2c. identitas berbeda -> minta konfirmasi ulang
      $peringatan = [];
      foreach ($gabungIds as $i) {
         if ($w = gPeringatan($utama, $p[$i])) $peringatan[] = ['rm' => $p[$i]['nomor_rm'], 'nama' => $p[$i]['patient_name'], 'alasan' => $w];
      }
      if ($peringatan && !$paksa) {
         $db->rollback();
         return ['status' => 'error', 'perlu_paksa' => true, 'peringatan' => $peringatan, '_kode' => 409,
            'message' => 'Identitas RM yang akan digabung berbeda dengan RM utama. Pastikan ini orang yang sama (bukan anggota keluarga yang memakai NIK/kartu yang sama).'];
      }

      $cadangan = ['waktu' => date('c'), 'oleh' => $oleh, 'id_customer' => $idc, 'utama' => $utama, 'digabung' => [], 'paksa' => $paksa, 'peringatan' => $peringatan];
      $ringkas = [];
      $utamaStr = (string) $utamaId;
      $rmUtama = (string) $utama['nomor_rm'];

      foreach ($gabungIds as $i) {
         $t = $p[$i];
         $iStr = (string) $i;
         // daftar kunjungan yang dipindah (untuk tabel bernomor RM & cadangan)
         $kunj = array_column(gQ($db, "SELECT visit_ID FROM pasien_visit WHERE id_patient = ? AND id_customer = ?", 'si', [$iStr, $idc]), 'visit_ID');
         $kunj = array_values(array_filter(array_map('strval', $kunj), 'strlen'));
         $hit = [];

         // 3. tabel ber-id_patient (id_patient unik global, jadi aman tanpa filter faskes; pasien_visit tetap dibatasi faskes)
         foreach (G_TABEL_ID as $tb) {
            if (!isset($ada[$tb]['id_patient'])) continue;
            $n = $tb === 'pasien_visit'
               ? gExec($db, "UPDATE pasien_visit SET id_patient = ? WHERE id_patient = ? AND id_customer = ?", 'ssi', [$utamaStr, $iStr, $idc])
               : gExec($db, "UPDATE `$tb` SET id_patient = ? WHERE id_patient = ?", 'ss', [$utamaStr, $iStr]);
            if ($n) $hit[$tb] = ($hit[$tb] ?? 0) + $n;
         }

         // 4. tabel ber-nomor_rm: hanya baris milik kunjungan yang dipindah
         $rmLama = (string) $t['nomor_rm'];
         if ($kunj && $rmLama !== $rmUtama) {
            foreach (G_TABEL_RM as $tb => $kol) {
               $kv = $ada[$tb][strtolower($kol)] ?? null;
               if (!$kv || !isset($ada[$tb]['nomor_rm'])) continue;
               foreach (array_chunk($kunj, 300) as $c) {
                  $n = gExec($db, "UPDATE `$tb` SET nomor_rm = ? WHERE nomor_rm = ? AND `$kv` IN (" . gIn(count($c)) . ")",
                     'ss' . str_repeat('s', count($c)), array_merge([$rmUtama, $rmLama], $c));
                  if ($n) $hit[$tb] = ($hit[$tb] ?? 0) + $n;
               }
            }
         }

         // sisa kunjungan di RM lama harus nol sebelum dihapus
         $sisa = (int) (gQ($db, "SELECT COUNT(*) n FROM pasien_visit WHERE id_patient = ?", 's', [$iStr])[0]['n'] ?? 0);
         if ($sisa > 0) throw new RuntimeException("Masih ada $sisa kunjungan di RM {$t['nomor_rm']} (faskes lain?). Penggabungan dibatalkan.");

         $cadangan['digabung'][] = ['pasien' => $t, 'kunjungan' => $kunj, 'dipindah' => $hit];
         $ringkas[] = ['rm' => $rmLama, 'nama' => $t['patient_name'], 'kunjungan' => count($kunj), 'tabel' => $hit];
      }

      // 5. lengkapi identitas RM utama + catat RM lama
      $set = [];
      $val = [];
      $diisi = [];
      foreach (G_KOLOM_LENGKAPI as $kol) {
         if (!array_key_exists($kol, $utama) || trim((string) $utama[$kol]) !== '') continue;
         foreach ($gabungIds as $i) {
            $v = trim((string) ($p[$i][$kol] ?? ''));
            if ($v === '') continue;
            if ($kol === 'patient_nik' && gNik($v) === '') continue;
            if ($kol === 'patient_bpjs' && gBpjs($v) === '') continue;
            if ($kol === 'patient_datebirth' && gTglValid($v) === '') continue;
            $set[] = "`$kol` = ?";
            $val[] = $v;
            $diisi[] = $kol;
            break;
         }
      }
      if (array_key_exists('nomor_rm_any', $utama)) {
         $lain = preg_split('/\s*[,;]\s*/', trim((string) $utama['nomor_rm_any']), -1, PREG_SPLIT_NO_EMPTY);
         foreach ($gabungIds as $i) {
            $lain[] = trim((string) $p[$i]['nomor_rm']);
            foreach (preg_split('/\s*[,;]\s*/', trim((string) ($p[$i]['nomor_rm_any'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) as $x) $lain[] = $x;
         }
         $lain = array_values(array_unique(array_filter($lain, fn($x) => $x !== '' && strcasecmp($x, $rmUtama) !== 0)));
         $set[] = '`nomor_rm_any` = ?';
         $val[] = mb_substr(implode(', ', $lain), 0, 255);
      }
      if (array_key_exists('total_visit', $utama)) {
         $tot = (int) (gQ($db, "SELECT COUNT(*) n FROM pasien_visit WHERE id_patient = ? AND id_customer = ? AND visit_status <> 99", 'si', [$utamaStr, $idc])[0]['n'] ?? 0);
         $set[] = '`total_visit` = ?';
         $val[] = (string) $tot;
      }
      if (array_key_exists('updated_at', $utama)) {
         $set[] = '`updated_at` = ?';
         $val[] = date('Y-m-d H:i:s');
      }
      if ($set) {
         gExec($db, "UPDATE ms_patient SET " . implode(', ', $set) . " WHERE id_patient = ? AND id_customer = ?",
            str_repeat('s', count($val)) . 'ii', array_merge($val, [$utamaId, $idc]));
      }

      // 6. cadangan dulu (wajib berhasil), baru hapus
      $dir = dirname(__DIR__, 2) . '/logs/merge_rme';
      if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) throw new RuntimeException('Folder cadangan logs/merge_rme tidak dapat dibuat.');
      if (!is_file("$dir/.htaccess")) @file_put_contents("$dir/.htaccess", "Require all denied\nDeny from all\n");
      $baris = json_encode($cadangan, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
      if (@file_put_contents("$dir/" . date('Y-m') . '.jsonl', $baris, FILE_APPEND | LOCK_EX) === false) {
         throw new RuntimeException('Cadangan gagal ditulis ke logs/merge_rme. Penggabungan dibatalkan.');
      }
      $hapus = gExec($db, "DELETE FROM ms_patient WHERE id_customer = ? AND id_patient IN (" . gIn(count($gabungIds)) . ")",
         'i' . str_repeat('i', count($gabungIds)), array_merge([$idc], $gabungIds));
      if ($hapus !== count($gabungIds)) throw new RuntimeException('Jumlah RM terhapus tidak sesuai. Penggabungan dibatalkan.');

      if (!$db->commit()) throw new RuntimeException('Commit gagal.');
   } catch (Throwable $e) {
      $db->rollback();
      throw $e;
   }

   error_log("[gabungRme] faskes $idc: RM " . implode(',', array_column($ringkas, 'rm')) . " digabung ke $rmUtama (#$utamaId) oleh $oleh");
   return ['status' => 'success', 'message' => count($gabungIds) . ' RM berhasil digabung ke RM ' . $rmUtama . '.',
      'utama' => ['id' => $utamaId, 'rm' => $rmUtama, 'nama' => $utama['patient_name']], 'digabung' => $ringkas, 'dilengkapi' => $diisi];
}
