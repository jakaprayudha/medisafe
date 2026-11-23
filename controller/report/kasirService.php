<?php
/**
 * Data laporan kasir, dipakai bersama oleh:
 *   - controller/report/kasirController.php (halaman module/admin/rpt_kasir)
 *   - module/report/export_kasir.php        (Excel)
 *   - module/report/print_kasir.php         (Print)
 *
 * Sumber: pasien_visit yang sudah lunas (status_bayar = 1, diisi controller/visit/bayar).
 * Tanggal bayar = 8 digit awal nomor_transaksi (format YmdXXXX, semua transaksi mengikuti pola ini);
 * bila tidak cocok pola, dipakai tanggal kunjungan.
 * Komposisi obat / tindakan dihitung dari rincian tagihan saat ini (rumus = controller/visit/_tagihan.php).
 */

const KS_MAKS_HARI = 366;
const KS_JEDA_BAYAR = 200; // hari: pembayaran bisa dilakukan sesudah kunjungan (terlama di data ±174 hari)

function ksParams(array $g): array
{
   $tz = new DateTimeZone('Asia/Jakarta');
   $hariIni = (new DateTime('now', $tz))->format('Y-m-d');
   $tgl = function ($v) {
      $v = (string) $v;
      $d = DateTime::createFromFormat('!Y-m-d', $v);
      return ($d && $d->format('Y-m-d') === $v) ? $v : null;
   };
   $from = $tgl($g['from'] ?? '') ?? $hariIni;
   $to = $tgl($g['to'] ?? '') ?? $from;
   if ($to < $from) [$from, $to] = [$to, $from];
   if ((new DateTime($from))->diff(new DateTime($to))->days + 1 > KS_MAKS_HARI) {
      throw new InvalidArgumentException('Rentang tanggal maksimal ' . KS_MAKS_HARI . ' hari.');
   }
   $dasar = ($g['dasar'] ?? 'bayar') === 'kunjungan' ? 'kunjungan' : 'bayar';
   return [
      'from' => $from, 'to' => $to, 'dasar' => $dasar,
      'metode' => mb_substr(trim((string) ($g['metode'] ?? '')), 0, 50),
      'penjamin' => preg_match('/^\d{1,5}$/', (string) ($g['penjamin'] ?? '')) ? (string) $g['penjamin'] : '',
      'poli' => mb_substr(trim((string) ($g['poli'] ?? '')), 0, 100),
   ];
}

/** Transaksi lunas pada periode (+ komposisi obat / tindakan dari rincian). */
function ksRows(mysqli $k, string $idc, array $p): array
{
   $akhir = (new DateTime($p['to']))->modify('+1 day')->format('Y-m-d');
   if ($p['dasar'] === 'kunjungan') {
      $vDari = $p['from'];
      $saringTgl = '';
      $nilaiTgl = [];
   } else {
      // batas bawah tanggal kunjungan agar index idx_date_cust terpakai
      $vDari = (new DateTime($p['from']))->modify('-' . KS_JEDA_BAYAR . ' day')->format('Y-m-d');
      $saringTgl = " AND LEFT(pv.nomor_transaksi, 8) >= ? AND LEFT(pv.nomor_transaksi, 8) <= ?";
      $nilaiTgl = [str_replace('-', '', $p['from']), str_replace('-', '', $p['to'])];
   }
   $saring = '';
   $nilai = [];
   if ($p['metode'] !== '') {
      if ($p['metode'] === 'Tanpa tagihan') { $saring .= " AND pv.metode_bayar = 'AUTO'"; }
      else { $saring .= ' AND pv.metode_bayar = ?'; $nilai[] = $p['metode']; }
   }
   if ($p['penjamin'] !== '') { $saring .= ' AND pv.id_provider = ?'; $nilai[] = $p['penjamin']; }
   if ($p['poli'] !== '') { $saring .= ' AND pv.id_poli = ?'; $nilai[] = $p['poli']; }

   $st = $k->prepare("SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.nomor_transaksi, pv.metode_bayar,
         pv.amount_results, pv.amount_payment, pv.amount_changes, pv.id_poli, pv.id_doctor, pv.id_provider,
         COALESCE(NULLIF(TRIM(pv.patient_name_pcare), ''), mp.patient_name) AS patient_name, mp.nomor_rm, prv.provider_name
      FROM pasien_visit pv FORCE INDEX (idx_date_cust)
      LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
      WHERE pv.id_customer = ? AND pv.visit_date >= ? AND pv.visit_date < ? AND pv.status_bayar = 1$saringTgl$saring
      ORDER BY pv.nomor_transaksi, pv.id_visit");
   $st->bind_param(str_repeat('s', 3 + count($nilaiTgl) + count($nilai)), $idc, $vDari, $akhir, ...array_merge($nilaiTgl, $nilai));
   $st->execute();
   $hasil = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   // komposisi rincian: obat (resep) & tindakan (pasien_billing) per kunjungan, 2 langkah (tanpa subquery per baris)
   $obat = $jasa = [];
   $visit = array_values(array_unique(array_column($hasil, 'visit_ID')));
   foreach (array_chunk($visit, 5000) as $potong) {
      $tanda = implode(',', array_fill(0, count($potong), '?'));
      $st = $k->prepare("SELECT id_permintaan_farmasi, id_visit FROM permintaan_pharmacy WHERE id_customer = ? AND id_visit IN ($tanda)");
      $st->bind_param('s' . str_repeat('s', count($potong)), $idc, ...$potong);
      $st->execute();
      $tiket = [];
      foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$id, $v]) $tiket[(int) $id] = $v;
      $st->close();
      foreach (array_chunk(array_keys($tiket), 20000) as $ids) {
         $st = $k->prepare("SELECT id_permintaan_farmasi, SUM(qty * harga) FROM permintaan_pharmacy_details
            WHERE id_permintaan_farmasi IN (" . implode(',', array_fill(0, count($ids), '?')) . ") GROUP BY id_permintaan_farmasi");
         $st->bind_param(str_repeat('i', count($ids)), ...$ids);
         $st->execute();
         foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$id, $sum]) $obat[$tiket[(int) $id]] = ($obat[$tiket[(int) $id]] ?? 0) + (int) $sum;
         $st->close();
      }
      $st = $k->prepare("SELECT id_visit, SUM(GREATEST(0, billing_qty * billing_price - billing_discount)) FROM pasien_billing
         WHERE id_customer = ? AND id_visit IN ($tanda) GROUP BY id_visit");
      $st->bind_param('s' . str_repeat('s', count($potong)), $idc, ...$potong);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$v, $sum]) $jasa[$v] = (int) $sum;
      $st->close();
   }

   $rows = [];
   foreach ($hasil as $r) {
      $no = (string) $r['nomor_transaksi'];
      $tglBayar = preg_match('/^(20\d{2})(\d{2})(\d{2})\d*$/', $no, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])
         ? "$m[1]-$m[2]-$m[3]" : $r['visit_date'];
      $metode = trim((string) $r['metode_bayar']);
      $rows[] = [
         'tgl_bayar' => $tglBayar,
         'no_transaksi' => $no,
         'visit_ID' => $r['visit_ID'],
         'tgl_kunjungan' => $r['visit_date'],
         'pasien' => $r['patient_name'] ?: '-',
         'nomor_rm' => $r['nomor_rm'] ?: '-',
         'penjamin' => $r['provider_name'] ?: 'Lainnya',
         'id_provider' => (string) $r['id_provider'],
         'poli' => trim((string) $r['id_poli']) ?: '-',
         'dokter' => trim((string) $r['id_doctor']) ?: '-',
         'metode' => $metode === 'AUTO' ? 'Tanpa tagihan' : ($metode ?: 'Lainnya'),
         'total' => (int) $r['amount_results'],
         'dibayar' => (int) $r['amount_payment'],
         'kembalian' => (int) $r['amount_changes'],
         'obat' => $obat[$r['visit_ID']] ?? 0,
         'tindakan' => $jasa[$r['visit_ID']] ?? 0,
      ];
   }
   return $rows;
}

function ksRingkasan(array $rows, array $p): array
{
   $r = ['transaksi' => 0, 'berbayar' => 0, 'tanpa_tagihan' => 0, 'total' => 0, 'dibayar' => 0, 'kembalian' => 0,
      'tunai' => 0, 'non_tunai' => 0, 'obat' => 0, 'tindakan' => 0, 'rata' => 0, 'terbesar' => 0];
   $harian = [];
   for ($d = new DateTime($p['from']); $d->format('Y-m-d') <= $p['to']; $d->modify('+1 day')) {
      $harian[$d->format('Y-m-d')] = ['tanggal' => $d->format('Y-m-d'), 'transaksi' => 0, 'total' => 0, 'tunai' => 0, 'non_tunai' => 0];
   }
   $metode = $penjamin = $poli = [];
   foreach ($rows as $x) {
      $r['transaksi']++;
      $r['total'] += $x['total'];
      $r['dibayar'] += $x['dibayar'];
      $r['kembalian'] += $x['kembalian'];
      $r['obat'] += $x['obat'];
      $r['tindakan'] += $x['tindakan'];
      $r['terbesar'] = max($r['terbesar'], $x['total']);
      if ($x['total'] > 0) $r['berbayar']++; else $r['tanpa_tagihan']++;
      $tunai = $x['metode'] === 'Tunai';
      if ($x['total'] > 0) $r[$tunai ? 'tunai' : 'non_tunai'] += $x['total'];
      $h = $p['dasar'] === 'kunjungan' ? $x['tgl_kunjungan'] : $x['tgl_bayar'];
      if (isset($harian[$h])) {
         $harian[$h]['transaksi']++;
         $harian[$h]['total'] += $x['total'];
         $harian[$h][$tunai ? 'tunai' : 'non_tunai'] += $x['total'];
      }
      $metode[$x['metode']] = ksTambahGrup($metode[$x['metode']] ?? null, $x['metode'], $x['total']);
      $penjamin[$x['penjamin']] = ksTambahGrup($penjamin[$x['penjamin']] ?? null, $x['penjamin'], $x['total']);
      $poli[$x['poli']] = ksTambahGrup($poli[$x['poli']] ?? null, $x['poli'], $x['total']);
   }
   $r['rata'] = $r['berbayar'] ? (int) round($r['total'] / $r['berbayar']) : 0;
   $urut = function (array $a) { usort($a, fn($x, $y) => [$y['total'], $y['transaksi']] <=> [$x['total'], $x['transaksi']]); return $a; };
   return ['ringkasan' => $r, 'harian' => array_values($harian), 'metode' => $urut(array_values($metode)),
      'penjamin' => $urut(array_values($penjamin)), 'poli' => $urut(array_values($poli))];
}

function ksTambahGrup(?array $g, string $nama, int $total): array
{
   $g = $g ?? ['nama' => $nama, 'transaksi' => 0, 'total' => 0];
   $g['transaksi']++;
   $g['total'] += $total;
   return $g;
}

/** Kunjungan selesai diperiksa (tanggal kunjungan dalam periode) yang belum dibayar padahal ada tagihan. */
function ksBelumBayar(mysqli $k, string $idc, array $p): array
{
   $akhir = (new DateTime($p['to']))->modify('+1 day')->format('Y-m-d');
   $st = $k->prepare("SELECT visit_ID FROM pasien_visit FORCE INDEX (idx_date_cust)
      WHERE id_customer = ? AND visit_date >= ? AND visit_date < ? AND visit_status = 4 AND COALESCE(status_bayar, 0) <> 1");
   $st->bind_param('sss', $idc, $p['from'], $akhir);
   $st->execute();
   $ada = array_fill_keys(array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'visit_ID'), 0);
   $st->close();
   if (!$ada) return ['kunjungan' => 0, 'total' => 0];
   $resepDari = (new DateTime($p['from']))->modify('-1 day')->format('Y-m-d');
   $st = $k->prepare("SELECT id_permintaan_farmasi, id_visit FROM permintaan_pharmacy WHERE id_customer = ? AND created_at >= ?");
   $st->bind_param('ss', $idc, $resepDari);
   $st->execute();
   $tiket = [];
   foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$id, $v]) if (isset($ada[$v])) $tiket[(int) $id] = $v;
   $st->close();
   foreach (array_chunk(array_keys($tiket), 20000) as $ids) {
      $st = $k->prepare("SELECT id_permintaan_farmasi, SUM(qty * harga) FROM permintaan_pharmacy_details
         WHERE id_permintaan_farmasi IN (" . implode(',', array_fill(0, count($ids), '?')) . ") GROUP BY id_permintaan_farmasi");
      $st->bind_param(str_repeat('i', count($ids)), ...$ids);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$id, $sum]) $ada[$tiket[(int) $id]] += (int) $sum;
      $st->close();
   }
   $st = $k->prepare("SELECT id_visit, SUM(GREATEST(0, billing_qty * billing_price - billing_discount)) FROM pasien_billing WHERE id_customer = ? GROUP BY id_visit");
   $st->bind_param('s', $idc);
   $st->execute();
   foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$v, $sum]) if (isset($ada[$v])) $ada[$v] += (int) $sum;
   $st->close();
   $berTagihan = array_filter($ada, fn($n) => $n > 0);
   return ['kunjungan' => count($berTagihan), 'total' => array_sum($berTagihan)];
}

/** Pilihan filter (metode & penjamin yang pernah dipakai di faskes ini). */
function ksOpsi(mysqli $k, string $idc): array
{
   $st = $k->prepare("SELECT DISTINCT CASE WHEN metode_bayar = 'AUTO' THEN 'Tanpa tagihan' ELSE COALESCE(NULLIF(TRIM(metode_bayar), ''), 'Lainnya') END m
      FROM pasien_visit WHERE id_customer = ? AND status_bayar = 1 ORDER BY 1");
   $st->bind_param('s', $idc);
   $st->execute();
   $metode = array_column($st->get_result()->fetch_all(MYSQLI_NUM), 0);
   $st->close();
   $penjamin = [];
   foreach ($k->query("SELECT id_provider, provider_name FROM ms_provider ORDER BY provider_name")->fetch_all(MYSQLI_NUM) as [$id, $n]) {
      $penjamin[] = ['id' => (string) $id, 'nama' => $n];
   }
   return ['metode' => $metode, 'penjamin' => $penjamin];
}

function ksKlinik(mysqli $k, string $idc): array
{
   $st = $k->prepare("SELECT sc.clinic_name, sc.image_clinic, f.faskes_address, f.faskes_city, f.faskes_phone
      FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id_customer = ? LIMIT 1");
   $st->bind_param('s', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc() ?: [];
   $st->close();
   return ['nama' => $r['clinic_name'] ?? 'Klinik', 'logo' => $r['image_clinic'] ?? '', 'alamat' => trim(implode(', ', array_filter([$r['faskes_address'] ?? '', $r['faskes_city'] ?? '']))), 'telp' => $r['faskes_phone'] ?? ''];
}

function ksTglIndo(string $ymd): string
{
   $b = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
   [$y, $m, $d] = explode('-', $ymd);
   return (int) $d . ' ' . $b[(int) $m] . ' ' . $y;
}

function ksLabelFilter(array $p, array $opsi): string
{
   $f = [$p['dasar'] === 'kunjungan' ? 'berdasarkan tanggal kunjungan' : 'berdasarkan tanggal bayar'];
   if ($p['metode'] !== '') $f[] = 'metode ' . $p['metode'];
   if ($p['penjamin'] !== '') {
      foreach ($opsi['penjamin'] as $o) if ($o['id'] === $p['penjamin']) $f[] = 'penjamin ' . $o['nama'];
   }
   if ($p['poli'] !== '') $f[] = 'poli ' . $p['poli'];
   return implode(' · ', $f);
}
