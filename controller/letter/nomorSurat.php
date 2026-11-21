<?php
/**
 * Penomoran surat bersama untuk semua controller surat (sakit, sehat, berobat, rawat_inap, kematian, mata).
 *
 * Pakai DI DALAM transaksi, sebelum INSERT surat:
 *   $koneksi->begin_transaction();
 *   $n = nsAlokasi($koneksi, $idCustomer, 'sakit', $nomorManual, fn($no) => cekNomorDipakai($no));
 *   if ($n['error']) { rollback; balas error }
 *   INSERT ... nomor_surat = $n['nomor'] ...
 *   nsSimpan($koneksi, $n);   // AUTO: setting_surat.nomor_<jenis> = nomor yang benar-benar dipakai
 *   $koneksi->commit();
 *
 * Perbaikan dibanding generator lama di tiap controller:
 *  - baris setting dikunci (SELECT ... FOR UPDATE) -> dua simpan bersamaan tidak mendapat nomor yang sama;
 *  - bila nomor berikutnya sudah terpakai, nomor dilewati DAN nomor terakhir di setting ikut disetel ke nomor yang
 *    dipakai (dulu hanya +1, sehingga setiap surat berikutnya mengulang pencarian duplikat dari awal).
 * Padding & penanda sama persis dengan generator lama.
 */

const NS_JENIS = [
   // jenis => [kolom nomor, kolom format, format bawaan, padding bawaan {NO} (0 = tanpa padding)]
   'sakit'      => ['nomor_sakit', 'format_sakit', 'SKS/{NO}/{MM}/{YYYY}', 3],
   'sehat'      => ['nomor_sehat', 'format_sehat', 'SKH/{NO}/{MM}/{YYYY}', 4],
   'berobat'    => ['nomor_berobat', 'format_berobat', 'SKB/{NO}/{MM}/{YYYY}', 4],
   'rawat_inap' => ['nomor_rawat_inap', 'format_rawat_inap', 'SRI/{NO}/{MM}/{YYYY}', 3],
   'kematian'   => ['nomor_kematian', 'format_kematian', 'SKM/{NO}/{MM}/{YYYY}', 0],
   'mata'       => ['nomor_mata', 'format_mata', 'SPM/{NO}/{MM}/{YYYY}', 3],
];

/** Susun nomor dari format. */
function nsFormat(string $format, int $no, int $padBawaan, ?DateTime $tgl = null): string
{
   $tgl ??= new DateTime('now', new DateTimeZone('Asia/Jakarta'));
   $pad = $padBawaan;
   if (preg_match('/\{NO:(\d+)\}/i', $format, $m)) $pad = (int) $m[1];
   $angka = $pad > 0 ? str_pad((string) $no, $pad, '0', STR_PAD_LEFT) : (string) $no;
   $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][(int) $tgl->format('n')];
   return preg_replace(
      ['/\{NO:\d+\}/i', '/\{NO\}/i', '/\{YYYY\}/i', '/\{YY\}/i', '/\{MM\}/i', '/\{DD\}/i', '/\{ROMAN\}/i'],
      [$angka, $angka, $tgl->format('Y'), $tgl->format('y'), $tgl->format('m'), $tgl->format('d'), $romawi],
      $format
   );
}

/** Setting faskes (tanpa kunci) untuk ditampilkan di form. */
function nsSetting(mysqli $k, string $idc, string $jenis): array
{
   [$kolNo, $kolFmt, $fmtBawaan] = NS_JENIS[$jenis];
   $st = $k->prepare("SELECT id, mode_nomor, `$kolFmt` AS fmt, `$kolNo` AS no FROM setting_surat WHERE id_customer = ? LIMIT 1");
   $st->bind_param('s', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return [
      'ada' => (bool) $r,
      'mode' => $r ? (strtoupper((string) $r['mode_nomor']) === 'MANUAL' ? 'MANUAL' : 'AUTO') : null,
      'format' => $r && trim((string) $r['fmt']) !== '' ? (string) $r['fmt'] : $fmtBawaan,
      'terakhir' => $r ? (int) $r['no'] : 0,
   ];
}

/**
 * Alokasikan nomor surat. Wajib dalam transaksi.
 * @param callable $dipakai fn(string $nomor): bool  -> nomor sudah dipakai surat lain (jenis & faskes sama)?
 * @param ?string $tanggalSurat Y-m-d; penanda tanggal di format memakai tanggal ini (bawaan: hari ini)
 * @return array{error:?string, kode:?string, nomor:?string, mode:?string, setting_id:?int, kolom:string, angka:?int}
 */
function nsAlokasi(mysqli $k, string $idc, string $jenis, string $manual, callable $dipakai, ?string $tanggalSurat = null): array
{
   [$kolNo, $kolFmt, $fmtBawaan, $pad] = NS_JENIS[$jenis];
   $hasil = ['error' => null, 'kode' => null, 'nomor' => null, 'mode' => null, 'setting_id' => null, 'kolom' => $kolNo, 'angka' => null];
   $st = $k->prepare("SELECT id, mode_nomor, `$kolFmt` AS fmt, `$kolNo` AS no FROM setting_surat WHERE id_customer = ? LIMIT 1 FOR UPDATE");
   $st->bind_param('s', $idc);
   $st->execute();
   $s = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$s) return ['error' => 'Pengaturan nomor surat belum dibuat. Minta admin mengatur di menu Surat > Atur nomor surat.', 'kode' => 'setting_required'] + $hasil;

   $mode = strtoupper(trim((string) $s['mode_nomor']));
   if (!in_array($mode, ['AUTO', 'MANUAL'], true)) return ['error' => 'Mode nomor surat belum valid.'] + $hasil;
   $hasil['mode'] = $mode;
   $hasil['setting_id'] = (int) $s['id'];

   if ($mode === 'MANUAL') {
      $manual = trim($manual);
      if ($manual === '') return ['error' => 'Nomor surat wajib diisi (mode penomoran manual).'] + $hasil;
      if (mb_strlen($manual) > 100) return ['error' => 'Nomor surat maksimal 100 karakter.'] + $hasil;
      if ($dipakai($manual)) return ['error' => "Nomor surat $manual sudah dipakai surat lain."] + $hasil;
      $hasil['nomor'] = $manual;
      return $hasil;
   }

   $format = trim((string) $s['fmt']) !== '' ? (string) $s['fmt'] : $fmtBawaan;
   // {DD}/{MM}/{YYYY} mengikuti tanggal surat bila dikirim (generator lama surat sehat), selain itu hari ini
   $tgl = ($tanggalSurat && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalSurat)) ? new DateTime($tanggalSurat, new DateTimeZone('Asia/Jakarta')) : null;
   $no = (int) $s['no'] + 1;
   for ($i = 0; $i <= 1000; $i++, $no++) {
      $nomor = nsFormat($format, $no, $pad, $tgl);
      if (!$dipakai($nomor)) {
         $hasil['nomor'] = $nomor;
         $hasil['angka'] = $no;
         return $hasil;
      }
   }
   return ['error' => 'Gagal membuat nomor surat otomatis (terlalu banyak nomor yang sudah terpakai). Periksa nomor terakhir di pengaturan.'] + $hasil;
}

/** Setelah INSERT berhasil (mode AUTO): simpan nomor terakhir yang dipakai. */
function nsSimpan(mysqli $k, array $n): void
{
   if ($n['mode'] !== 'AUTO' || !$n['setting_id']) return;
   $kol = $n['kolom'];
   $st = $k->prepare("UPDATE setting_surat SET `$kol` = ?, updated_at = NOW() WHERE id = ?");
   $st->bind_param('ii', $n['angka'], $n['setting_id']);
   if (!$st->execute()) throw new RuntimeException($st->error);
   $st->close();
}
