<?php
/**
 * Data laporan pelayanan resep farmasi, dipakai bersama oleh:
 *   - controller/report/resepController.php (halaman module/admin/rpt_resep)
 *   - module/report/export_resep.php        (Excel)
 *   - module/report/print_resep.php         (Print)
 *
 * Sumber: permintaan_pharmacy (tiket resep) + permintaan_pharmacy_details (item), periode = tanggal resep dibuat.
 * Status tiket: 0 draf dokter · 1 menunggu farmasi · 2 disiapkan · 3 selesai (obat diserahkan).
 * Nilai obat = qty × harga (harga jual saat resep). "Diserahkan" = tiket status 3.
 */

const RS_MAKS_HARI = 92; // ±3 bulan: faskes besar ±7 rb resep/bulan
const RS_STATUS = [0 => 'Draf dokter', 1 => 'Menunggu', 2 => 'Disiapkan', 3 => 'Selesai'];

function rsParams(array $g): array
{
   $hariIni = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
   $tgl = function ($v) {
      $v = (string) $v;
      $d = DateTime::createFromFormat('!Y-m-d', $v);
      return ($d && $d->format('Y-m-d') === $v) ? $v : null;
   };
   $from = $tgl($g['from'] ?? '') ?? $hariIni;
   $to = $tgl($g['to'] ?? '') ?? $from;
   if ($to < $from) [$from, $to] = [$to, $from];
   if ((new DateTime($from))->diff(new DateTime($to))->days + 1 > RS_MAKS_HARI) {
      throw new InvalidArgumentException('Rentang tanggal maksimal ' . RS_MAKS_HARI . ' hari.');
   }
   $status = (string) ($g['status'] ?? '');
   return [
      'from' => $from, 'to' => $to,
      'status' => in_array($status, ['0', '1', '2', '3', 'proses'], true) ? $status : '',
      'jenis' => mb_substr(trim((string) ($g['jenis'] ?? '')), 0, 30),
      'penjamin' => preg_match('/^\d{1,5}$/', (string) ($g['penjamin'] ?? '')) ? (string) $g['penjamin'] : '',
      'dokter' => mb_substr(trim((string) ($g['dokter'] ?? '')), 0, 150),
   ];
}

/** Tiket resep periode + data kunjungan. Mengembalikan [tiket[], obat[]] (obat = ringkasan per obat dari tiket yang lolos filter). */
function rsData(mysqli $k, string $idc, array $p): array
{
   $akhir = (new DateTime($p['to']))->modify('+1 day')->format('Y-m-d');
   $saring = '';
   $nilai = [];
   if ($p['status'] === 'proses') $saring .= ' AND p.status_permintaan IN (1, 2)';
   elseif ($p['status'] !== '') { $saring .= ' AND p.status_permintaan = ?'; $nilai[] = $p['status']; }
   if ($p['jenis'] !== '') { $saring .= ' AND p.tipe_obat = ?'; $nilai[] = $p['jenis']; }

   $st = $k->prepare("SELECT p.id_permintaan_farmasi, p.permintaan_number, p.id_visit, p.created_at, p.status_permintaan, p.tipe_obat, p.rck_nama
      FROM permintaan_pharmacy p WHERE p.id_customer = ? AND p.created_at >= ? AND p.created_at < ?$saring ORDER BY p.created_at, p.id_permintaan_farmasi");
   $st->bind_param(str_repeat('s', 3 + count($nilai)), $idc, $p['from'], $akhir, ...$nilai);
   $st->execute();
   $tiket = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   if (!$tiket) return [[], []];

   // kunjungan (pasien, dokter, poli, penjamin) — 1 query IN per potongan
   $visit = [];
   foreach (array_chunk(array_values(array_unique(array_column($tiket, 'id_visit'))), 5000) as $potong) {
      $st = $k->prepare("SELECT pv.visit_ID, pv.id_doctor, pv.id_poli, pv.id_provider, prv.provider_name, mp.nomor_rm,
            COALESCE(NULLIF(TRIM(pv.patient_name_pcare), ''), mp.patient_name) AS patient_name
         FROM pasien_visit pv LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
         WHERE pv.id_customer = ? AND pv.visit_ID IN (" . implode(',', array_fill(0, count($potong), '?')) . ")");
      $st->bind_param('s' . str_repeat('s', count($potong)), $idc, ...$potong);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $v) $visit[$v['visit_ID']] = $v;
      $st->close();
   }

   // item diringkas di database (dulu semua baris item diambil -> faskes besar kehabisan memori):
   //  - per tiket: jumlah item, qty, nilai     - per obat per status tiket: qty, nilai, jumlah resep
   // permintaan_pharmacy_details tanpa index id_permintaan_farmasi -> 1 query IN per potongan, bukan per tiket
   $perTiket = [];
   $statusTiket = [];
   foreach ($tiket as $t) $statusTiket[(int) $t['id_permintaan_farmasi']] = (int) $t['status_permintaan'];
   $perObat = [];
   foreach (array_chunk(array_keys($statusTiket), 20000) as $ids) {
      $tanda = implode(',', array_fill(0, count($ids), '?'));
      $st = $k->prepare("SELECT id_permintaan_farmasi, COUNT(*) n, SUM(qty) q, SUM(qty * harga) v FROM permintaan_pharmacy_details
         WHERE id_permintaan_farmasi IN ($tanda) GROUP BY id_permintaan_farmasi");
      $st->bind_param(str_repeat('i', count($ids)), ...$ids);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$id, $n, $q, $v]) $perTiket[(int) $id] = [(int) $n, (int) $q, (int) $v];
      $st->close();
      $st = $k->prepare("SELECT id_permintaan_farmasi, id_pharmacy, SUM(qty) q, SUM(qty * harga) v FROM permintaan_pharmacy_details
         WHERE id_permintaan_farmasi IN ($tanda) GROUP BY id_permintaan_farmasi, id_pharmacy");
      $st->bind_param(str_repeat('i', count($ids)), ...$ids);
      $st->execute();
      $hasilObat = $st->get_result();
      while ($o = $hasilObat->fetch_row()) $perObat[] = [(int) $o[0], (int) $o[1], (int) $o[2], (int) $o[3]];
      $hasilObat->free();
      $st->close();
   }

   $hasil = [];
   $lolos = [];
   foreach ($tiket as $t) {
      $v = $visit[$t['id_visit']] ?? [];
      if ($p['penjamin'] !== '' && (string) ($v['id_provider'] ?? '') !== $p['penjamin']) continue;
      $dokter = trim((string) ($v['id_doctor'] ?? ''));
      if ($p['dokter'] !== '' && $dokter !== $p['dokter']) continue;
      $id = (int) $t['id_permintaan_farmasi'];
      $lolos[$id] = true;
      [$n, $qty, $nilaiObat] = $perTiket[$id] ?? [0, 0, 0];
      $status = (int) $t['status_permintaan'];
      $hasil[] = [
         'id' => $id,
         'nomor' => $t['permintaan_number'] ?: ('#' . $id),
         'tanggal' => substr($t['created_at'], 0, 10),
         'jam' => substr($t['created_at'], 11, 5),
         'visit_ID' => $t['id_visit'],
         'pasien' => $v['patient_name'] ?? '-',
         'nomor_rm' => $v['nomor_rm'] ?? '-',
         'dokter' => $dokter ?: '-',
         'poli' => trim((string) ($v['id_poli'] ?? '')) ?: '-',
         'penjamin' => $v['provider_name'] ?? 'Lainnya',
         'jenis' => trim((string) $t['tipe_obat']) ?: 'Non Racikan',
         'racikan' => $t['rck_nama'],
         'status' => $status,
         'status_label' => RS_STATUS[$status] ?? ('Status ' . $status),
         'item' => $n,
         'qty' => $qty,
         'nilai' => $nilaiObat,
      ];
   }

   // ringkas per obat (hanya tiket yang lolos filter), nama obat diambil sekali
   $obat = [];
   foreach ($perObat as [$idt, $idObat, $q, $v]) {
      if (!isset($lolos[$idt])) continue;
      $obat[$idObat] = $obat[$idObat] ?? ['id' => $idObat, 'qty' => 0, 'nilai' => 0, 'resep' => 0];
      $obat[$idObat]['qty'] += $q;
      $obat[$idObat]['nilai'] += $v;
      $obat[$idObat]['resep']++;
   }
   unset($perObat);
   if ($obat) {
      foreach (array_chunk(array_keys($obat), 5000) as $ids) {
         $st = $k->prepare("SELECT id_pharmacy, pharmacy_name_generic, pharmacy_name_trade, pharmacy_unit FROM ms_pharmacy
            WHERE id_pharmacy IN (" . implode(',', array_fill(0, count($ids), '?')) . ")");
         $st->bind_param(str_repeat('i', count($ids)), ...$ids);
         $st->execute();
         foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$id, $g, $tr, $u]) {
            $obat[(int) $id]['nama'] = trim((string) $g) ?: (trim((string) $tr) ?: '');
            $obat[(int) $id]['satuan'] = preg_match('/\p{L}/u', (string) $u) ? trim((string) $u) : '';
         }
         $st->close();
      }
      foreach ($obat as &$o) {
         if (empty($o['nama'])) $o['nama'] = '(tidak ada di master obat)';
         $o['satuan'] = $o['satuan'] ?? '';
      }
      unset($o);
   }
   return [$hasil, array_values($obat)];
}

function rsRingkasan(array $tiket, array $item, array $p): array
{
   $r = ['resep' => count($tiket), 'kunjungan' => 0, 'pasien' => 0, 'selesai' => 0, 'proses' => 0, 'draf' => 0,
      'item' => 0, 'qty' => 0, 'nilai' => 0, 'nilai_diserahkan' => 0, 'racikan' => 0, 'rata_item' => 0, 'rata_nilai' => 0];
   $kunjungan = $pasien = [];
   $harian = [];
   for ($d = new DateTime($p['from']); $d->format('Y-m-d') <= $p['to']; $d->modify('+1 day')) {
      $harian[$d->format('Y-m-d')] = ['tanggal' => $d->format('Y-m-d'), 'resep' => 0, 'selesai' => 0, 'nilai' => 0];
   }
   $grup = ['jenis' => [], 'dokter' => [], 'poli' => [], 'penjamin' => [], 'status' => []];
   foreach ($tiket as $t) {
      $kunjungan[$t['visit_ID']] = true;
      if ($t['nomor_rm'] !== '-') $pasien[$t['nomor_rm']] = true;
      $r['item'] += $t['item'];
      $r['qty'] += $t['qty'];
      $r['nilai'] += $t['nilai'];
      if ($t['status'] === 3) { $r['selesai']++; $r['nilai_diserahkan'] += $t['nilai']; }
      elseif ($t['status'] === 0) $r['draf']++;
      else $r['proses']++;
      if (strcasecmp($t['jenis'], 'Racikan') === 0) $r['racikan']++;
      if (isset($harian[$t['tanggal']])) {
         $harian[$t['tanggal']]['resep']++;
         $harian[$t['tanggal']]['nilai'] += $t['nilai'];
         if ($t['status'] === 3) $harian[$t['tanggal']]['selesai']++;
      }
      foreach (['jenis' => $t['jenis'], 'dokter' => $t['dokter'], 'poli' => $t['poli'], 'penjamin' => $t['penjamin'], 'status' => $t['status_label']] as $g => $kunci) {
         $grup[$g][$kunci] = $grup[$g][$kunci] ?? ['nama' => $kunci, 'resep' => 0, 'nilai' => 0];
         $grup[$g][$kunci]['resep']++;
         $grup[$g][$kunci]['nilai'] += $t['nilai'];
      }
   }
   $r['kunjungan'] = count($kunjungan);
   $r['pasien'] = count($pasien);
   $r['rata_item'] = $r['resep'] ? round($r['item'] / $r['resep'], 1) : 0;
   $r['rata_nilai'] = $r['resep'] ? (int) round($r['nilai'] / $r['resep']) : 0;

   // obat terbanyak (qty) & nilai terbesar — $item sudah teringkas per obat oleh rsData()
   $obat = $item;

   $urut = function (array $a, string $kunci) { usort($a, fn($x, $y) => $y[$kunci] <=> $x[$kunci]); return $a; };
   foreach ($grup as $g => $isi) $grup[$g] = $urut(array_values($isi), 'resep');

   return ['ringkasan' => $r, 'harian' => array_values($harian), 'grup' => $grup,
      'obat_qty' => array_slice($urut($obat, 'qty'), 0, 15), 'obat_nilai' => array_slice($urut($obat, 'nilai'), 0, 15),
      'jumlah_obat' => count($obat), 'obat_semua' => $urut($obat, 'qty')];
}

function rsOpsi(array $tiket): array
{
   $dokter = array_values(array_unique(array_filter(array_column($tiket, 'dokter'), fn($d) => $d !== '-')));
   sort($dokter);
   return ['dokter' => $dokter];
}

function rsPenjamin(mysqli $k): array
{
   $o = [];
   foreach ($k->query("SELECT id_provider, provider_name FROM ms_provider ORDER BY provider_name")->fetch_all(MYSQLI_NUM) as [$id, $n]) $o[] = ['id' => (string) $id, 'nama' => $n];
   return $o;
}

function rsKlinik(mysqli $k, string $idc): array
{
   $st = $k->prepare("SELECT sc.clinic_name, sc.image_clinic, f.faskes_address, f.faskes_city, f.faskes_phone
      FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id_customer = ? LIMIT 1");
   $st->bind_param('s', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc() ?: [];
   $st->close();
   return ['nama' => $r['clinic_name'] ?? 'Klinik', 'logo' => $r['image_clinic'] ?? '', 'alamat' => trim(implode(', ', array_filter([$r['faskes_address'] ?? '', $r['faskes_city'] ?? '']))), 'telp' => $r['faskes_phone'] ?? ''];
}

function rsTglIndo(string $ymd): string
{
   $b = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
   [$y, $m, $d] = explode('-', $ymd);
   return (int) $d . ' ' . $b[(int) $m] . ' ' . $y;
}

function rsLabelFilter(array $p, array $penjamin): string
{
   $f = [];
   if ($p['status'] !== '') $f[] = 'status ' . ($p['status'] === 'proses' ? 'dalam proses' : RS_STATUS[(int) $p['status']]);
   if ($p['jenis'] !== '') $f[] = 'jenis ' . $p['jenis'];
   foreach ($penjamin as $o) if ($p['penjamin'] !== '' && $o['id'] === $p['penjamin']) $f[] = 'penjamin ' . $o['nama'];
   if ($p['dokter'] !== '') $f[] = 'dokter ' . $p['dokter'];
   return $f ? implode(' · ', $f) : 'semua resep';
}
