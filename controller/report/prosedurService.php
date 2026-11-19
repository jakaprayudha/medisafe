<?php
/**
 * Laporan tindakan / prosedur (module/admin/rpt_prosedur) — dipakai controller, export Excel & cetak.
 *
 * Sumber: pasien_billing (baris tindakan/konsultasi/BMHP per kunjungan) + pasien_visit (tanggal, dokter, poli, pasien).
 * - Nilai baris = harga × jumlah − diskon (sama dengan kasir).
 * - Fee dokter/perawat/admin diambil dari master tarif FASKES YANG SAMA (nama dicocokkan tanpa beda huruf/spasi),
 *   dikalikan jumlah. Versi lama mencocokkan nama ke tarif semua faskes sehingga nilai bisa berlipat.
 * Kunjungan batal (99) tidak dihitung.
 */
require_once __DIR__ . '/appointmentService.php'; // apOpsi, apKlinik, apTglIndo, apGender

const PR_MAKS_HARI = 366;
const PR_KATEGORI = ['Tindakan', 'Konsultasi', 'Obat/BMHP/Alkes', 'Lainnya'];

function prParams(array $in): array
{
   $tgl = function ($v, $def) {
      $v = is_string($v) ? trim($v) : '';
      if ($v === '') return $def;
      $d = DateTime::createFromFormat('!Y-m-d', $v);
      if (!$d || $d->format('Y-m-d') !== $v) throw new InvalidArgumentException('Format tanggal tidak valid.');
      return $v;
   };
   $from = $tgl($in['fromDate'] ?? null, date('Y-m-01'));
   $to = $tgl($in['toDate'] ?? null, date('Y-m-d'));
   if ($to < $from) [$from, $to] = [$to, $from];
   $hari = (new DateTime($from))->diff(new DateTime($to))->days + 1;
   if ($hari > PR_MAKS_HARI) throw new InvalidArgumentException('Rentang tanggal maksimal ' . PR_MAKS_HARI . ' hari.');
   $str = fn($k) => is_string($in[$k] ?? null) ? mb_substr(trim($in[$k]), 0, 150) : '';
   return [
      'from' => $from,
      'to' => $to,
      'hari' => $hari,
      'doctor' => $str('doctor'),
      'poli' => $str('poli'),
      'provider' => ctype_digit((string) ($in['provider'] ?? '')) ? (int) $in['provider'] : 0,
      'kategori' => in_array($in['kategori'] ?? '', PR_KATEGORI, true) ? $in['kategori'] : '',
   ];
}

function prQ(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) throw new RuntimeException('Gagal menyiapkan laporan tindakan.');
   if ($t !== '') $st->bind_param($t, ...$a);
   if (!$st->execute()) throw new RuntimeException('Gagal mengambil laporan tindakan.');
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

function prKunci(string $s): string
{
   return mb_strtolower(preg_replace('/\s+/', ' ', trim($s)));
}

/** WHERE kunjungan bersama. */
function prWhereVisit(int $idc, array $p): array
{
   $w = 'pv.visit_date BETWEEN ? AND ? AND pv.id_customer = ? AND (pv.visit_status IS NULL OR pv.visit_status <> 99)';
   $t = 'ssi';
   $a = [$p['from'], $p['to'], $idc];
   if ($p['doctor'] !== '') { $w .= ' AND pv.id_doctor = ?'; $t .= 's'; $a[] = $p['doctor']; }
   if ($p['poli'] !== '') { $w .= ' AND pv.id_poli = ?'; $t .= 's'; $a[] = $p['poli']; }
   if ($p['provider']) { $w .= ' AND pv.id_provider = ?'; $t .= 'i'; $a[] = $p['provider']; }
   return [$w, $t, $a];
}

/** Baris tindakan pada periode, lengkap dengan nilai & fee. */
function prRows(mysqli $k, int $idc, array $p): array
{
   [$w, $t, $a] = prWhereVisit($idc, $p);
   // pasien_billing tidak ber-index; tabelnya kecil, jadi kunjungan difilter dulu (index tanggal) lalu digabung.
   $sql = "SELECT pb.id_billing, pb.id_visit, pb.billing_item, pb.billing_category, pb.billing_price, pb.billing_qty, pb.billing_discount,
                  pv.visit_date, pv.visit_time, pv.patient_name_pcare, pv.id_doctor, pv.id_poli, pv.id_patient, mp.provider_name,
                  ms.nomor_rm
           FROM pasien_visit pv
           JOIN pasien_billing pb ON pb.id_visit = pv.visit_ID
           LEFT JOIN ms_provider mp ON mp.id_provider = pv.id_provider
           LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
           WHERE $w AND (pb.billing_status IS NULL OR pb.billing_status <> 0)";
   if ($p['kategori'] !== '') { $sql .= ' AND pb.billing_category = ?'; $t .= 's'; $a[] = $p['kategori']; }
   $sql .= ' ORDER BY pv.visit_date DESC, pv.visit_time DESC, pb.id_billing';
   $data = prQ($k, $sql, $t, $a);

   // master tarif faskes ini (nama → fee); bila nama ganda, pakai yang aktif/terbaru
   $tarif = [];
   foreach (prQ($k, 'SELECT id_tarif, tarif_name, tarif_amount, tarif_share_doctor d, tarif_share_nurse n, tarif_share_admin a, tarif_status
                     FROM ms_tarif WHERE id_customer = ? ORDER BY (tarif_status = 1) ASC, id_tarif ASC', 'i', [$idc]) as $r) {
      $tarif[prKunci((string) $r['tarif_name'])] = $r;
   }

   $out = [];
   foreach ($data as $r) {
      $qty = max(1, (int) $r['billing_qty']);
      $harga = (float) $r['billing_price'];
      $diskon = (float) $r['billing_discount'];
      $tf = $tarif[prKunci((string) $r['billing_item'])] ?? null;
      $out[] = [
         'tanggal' => $r['visit_date'],
         'jam' => substr((string) $r['visit_time'], 0, 5),
         'visit' => $r['id_visit'],
         'nama' => (string) $r['patient_name_pcare'],
         'rm' => (string) ($r['nomor_rm'] ?? ''),
         'dokter' => trim((string) $r['id_doctor']) ?: '-',
         'poli' => trim((string) $r['id_poli']) ?: '-',
         'bayar' => $r['provider_name'] ?: '-',
         'item' => trim((string) $r['billing_item']) ?: '(tanpa nama)',
         'kategori' => $r['billing_category'] ?: 'Lainnya',
         'qty' => $qty,
         'harga' => $harga,
         'diskon' => $diskon,
         'total' => max(0, $harga * $qty - $diskon),
         'fee_dokter' => $tf ? (float) $tf['d'] * $qty : 0,
         'fee_perawat' => $tf ? (float) $tf['n'] * $qty : 0,
         'fee_admin' => $tf ? (float) $tf['a'] * $qty : 0,
         'tarif' => $tf !== null,
      ];
   }
   return $out;
}

/** Jumlah kunjungan pada periode (dengan filter yang sama) — pembanding "kunjungan dengan tindakan". */
function prKunjungan(mysqli $k, int $idc, array $p): int
{
   [$w, $t, $a] = prWhereVisit($idc, $p);
   return (int) (prQ($k, "SELECT COUNT(*) n FROM pasien_visit pv WHERE $w", $t, $a)[0]['n'] ?? 0);
}

function prRingkasan(array $rows, array $p, int $kunjungan): array
{
   $item = $dok = $kat = $bayar = $poli = [];
   $visit = [];
   $tot = ['total' => 0, 'qty' => 0, 'diskon' => 0, 'kotor' => 0, 'fee_dokter' => 0, 'fee_perawat' => 0, 'fee_admin' => 0];
   $tanpaTarif = [];
   $perHari = $p['hari'] <= 92;
   $tren = [];
   if ($perHari) {
      for ($d = new DateTime($p['from']); $d->format('Y-m-d') <= $p['to']; $d->modify('+1 day')) $tren[$d->format('Y-m-d')] = ['total' => 0, 'qty' => 0];
   }
   foreach ($rows as $r) {
      $tot['total'] += $r['total'];
      $tot['qty'] += $r['qty'];
      $tot['diskon'] += $r['diskon'];
      $tot['kotor'] += $r['harga'] * $r['qty'];
      foreach (['fee_dokter', 'fee_perawat', 'fee_admin'] as $f) $tot[$f] += $r[$f];
      $visit[$r['visit']] = true;
      $ki = prKunci($r['item']);
      if (!$r['tarif']) $tanpaTarif[$ki] = ($tanpaTarif[$ki] ?? 0) + $r['qty'];

      $item[$ki] ??= ['item' => $r['item'], 'kategori' => $r['kategori'], 'qty' => 0, 'kunjungan' => [], 'total' => 0, 'fee_dokter' => 0, 'fee_perawat' => 0, 'fee_admin' => 0, 'tarif' => $r['tarif'], 'dokter' => []];
      $item[$ki]['qty'] += $r['qty'];
      $item[$ki]['kunjungan'][$r['visit']] = true;
      $item[$ki]['total'] += $r['total'];
      foreach (['fee_dokter', 'fee_perawat', 'fee_admin'] as $f) $item[$ki][$f] += $r[$f];
      $item[$ki]['dokter'][$r['dokter']] = ($item[$ki]['dokter'][$r['dokter']] ?? 0) + $r['qty'];

      $dok[$r['dokter']] ??= ['dokter' => $r['dokter'], 'kunjungan' => [], 'qty' => 0, 'total' => 0, 'fee_dokter' => 0, 'fee_perawat' => 0, 'fee_admin' => 0, 'item' => []];
      $dok[$r['dokter']]['kunjungan'][$r['visit']] = true;
      $dok[$r['dokter']]['qty'] += $r['qty'];
      $dok[$r['dokter']]['total'] += $r['total'];
      foreach (['fee_dokter', 'fee_perawat', 'fee_admin'] as $f) $dok[$r['dokter']][$f] += $r[$f];
      $dok[$r['dokter']]['item'][$r['item']] = true;

      $kat[$r['kategori']] = ($kat[$r['kategori']] ?? 0) + $r['total'];
      $bayar[$r['bayar']] = ($bayar[$r['bayar']] ?? 0) + $r['total'];
      $poli[$r['poli']] = ($poli[$r['poli']] ?? 0) + $r['total'];
      $kunci = $perHari ? $r['tanggal'] : substr($r['tanggal'], 0, 7);
      $tren[$kunci] ??= ['total' => 0, 'qty' => 0];
      $tren[$kunci]['total'] += $r['total'];
      $tren[$kunci]['qty'] += $r['qty'];
   }
   ksort($tren);
   $items = array_values(array_map(function ($v) {
      arsort($v['dokter']);
      return ['item' => $v['item'], 'kategori' => $v['kategori'], 'qty' => $v['qty'], 'kunjungan' => count($v['kunjungan']), 'total' => $v['total'],
         'rata' => $v['qty'] ? round($v['total'] / $v['qty']) : 0, 'fee_dokter' => $v['fee_dokter'], 'fee_perawat' => $v['fee_perawat'], 'fee_admin' => $v['fee_admin'],
         'tarif' => $v['tarif'], 'dokter_utama' => (string) array_key_first($v['dokter'])];
   }, $item));
   usort($items, fn($x, $y) => [$y['qty'], $y['total']] <=> [$x['qty'], $x['total']]);
   foreach ($items as $i => &$it) $it['rank'] = $i + 1;
   unset($it);
   $doks = array_values(array_map(fn($v) => ['dokter' => $v['dokter'], 'kunjungan' => count($v['kunjungan']), 'qty' => $v['qty'], 'jenis' => count($v['item']), 'total' => $v['total'],
      'fee_dokter' => $v['fee_dokter'], 'fee_perawat' => $v['fee_perawat'], 'fee_admin' => $v['fee_admin']], $dok));
   usort($doks, fn($x, $y) => $y['total'] <=> $x['total']);
   $urut = function (array $a) { arsort($a); return array_map(fn($k, $v) => ['nama' => (string) $k, 'jumlah' => $v], array_keys($a), $a); };
   arsort($tanpaTarif);
   $kv = count($visit);
   return $tot + [
      'kunjungan' => $kunjungan,
      'kunjungan_tindakan' => $kv,
      'pct_kunjungan' => $kunjungan ? round($kv / $kunjungan * 100, 1) : 0,
      'rata_per_kunjungan' => $kv ? round($tot['total'] / $kv) : 0,
      'jenis' => count($items),
      'tanpa_tarif' => count($tanpaTarif),
      'tanpa_tarif_item' => array_map(fn($k) => $item[$k]['item'], array_slice(array_keys($tanpaTarif), 0, 8)),
      'periode' => $perHari ? 'hari' : 'bulan',
      'tren' => array_map(fn($k, $v) => ['t' => $k] + $v, array_keys($tren), $tren),
      'kategori' => $urut($kat),
      'bayar' => $urut($bayar),
      'poli' => $urut($poli),
      'items' => $items,
      'dokter' => $doks,
   ];
}

function prLabelFilter(array $p): string
{
   $f = [];
   if ($p['kategori'] !== '') $f[] = 'Kategori: ' . $p['kategori'];
   if ($p['doctor'] !== '') $f[] = 'Dokter: ' . $p['doctor'];
   if ($p['poli'] !== '') $f[] = 'Poli: ' . $p['poli'];
   if ($p['provider']) $f[] = 'Provider #' . $p['provider'];
   return $f ? implode(' · ', $f) : 'Semua data';
}

function prRp($v): string
{
   return 'Rp ' . number_format((float) $v, 0, ',', '.');
}
