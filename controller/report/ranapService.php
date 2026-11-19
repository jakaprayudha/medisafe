<?php
/**
 * Laporan rawat inap (module/admin/rpt_ranap) — dipakai controller, export Excel & cetak.
 *
 * Sumber data:
 *  - pasien_visit (status_rawatinap = 1)     : admisi, pasien, dokter, diagnosis, kondisi pulang
 *  - permintaan_ranap (visit_ID_inpatient)   : tanggal masuk ranap, kamar/bed, status aktif/pulang
 *  - resume_medis (visit_ID)                 : tanggal & jam pulang
 *  - visit_cppt (visit_ID)                   : jumlah CPPT
 * Tabel pendukung tidak ber-index visit_ID, jadi diambil sekali per tabel (IN …) lalu digabung di PHP,
 * bukan subquery EXISTS per baris seperti sebelumnya.
 */
require_once __DIR__ . '/appointmentService.php'; // apGender, apOpsi, apKlinik, apTglIndo

const RN_MAKS_HARI = 366;
const RN_LOS_MAKS = 90; // lama rawat > 90 hari dianggap salah input tanggal pulang

function rnParams(array $in): array
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
   if ($hari > RN_MAKS_HARI) throw new InvalidArgumentException('Rentang tanggal maksimal ' . RN_MAKS_HARI . ' hari.');
   $str = fn($k) => is_string($in[$k] ?? null) ? mb_substr(trim($in[$k]), 0, 150) : '';
   // status lama (Belum/Pemeriksaan/Selesai) tetap diterima
   $map = ['Belum' => 'belum', 'Pemeriksaan' => 'dirawat', 'Selesai' => 'pulang'];
   $status = $map[$in['status'] ?? ''] ?? ($in['status'] ?? '');
   return [
      'from' => $from,
      'to' => $to,
      'hari' => $hari,
      'doctor' => $str('doctor'),
      'provider' => ctype_digit((string) ($in['provider'] ?? '')) ? (int) $in['provider'] : 0,
      'room' => ctype_digit((string) ($in['room'] ?? '')) ? (int) $in['room'] : 0,
      'status' => in_array($status, ['belum', 'dirawat', 'pulang'], true) ? $status : '',
   ];
}

function rnQ(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) throw new RuntimeException('Gagal menyiapkan laporan rawat inap.');
   if ($t !== '') $st->bind_param($t, ...$a);
   if (!$st->execute()) throw new RuntimeException('Gagal mengambil laporan rawat inap.');
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

/** Ambil baris tabel pendukung untuk sekumpulan visit_ID (per 500). */
function rnPerVisit(mysqli $k, string $sql, array $ids, string $tAwal = '', array $aAwal = []): array
{
   $out = [];
   foreach (array_chunk($ids, 500) as $blok) {
      $ph = implode(',', array_fill(0, count($blok), '?'));
      foreach (rnQ($k, str_replace('{IDS}', $ph, $sql), $tAwal . str_repeat('s', count($blok)), [...$aAwal, ...$blok]) as $r) {
         $out[] = $r;
      }
   }
   return $out;
}

/** Tanggal kejadian (masuk/pulang) — tahun di luar 2000–2100 dianggap salah input. */
function rnTanggal(?string $s, int $tahunMin = 2000): ?string
{
   $s = trim((string) $s);
   if ($s === '') return null;
   foreach (['!Y-m-d', '!d-m-Y', '!d/m/Y', '!Y/m/d'] as $f) {
      $d = DateTime::createFromFormat($f, substr($s, 0, 10));
      if ($d && $d->format('Y') >= $tahunMin && $d->format('Y') <= 2100) return $d->format('Y-m-d');
   }
   return null;
}

function rnUmur(?string $lahir, string $pada): ?int
{
   $l = rnTanggal($lahir, 1900);
   if (!$l) return null;
   $u = (new DateTime($l))->diff(new DateTime($pada))->y;
   return $u >= 0 && $u <= 120 ? $u : null;
}

/** Semua admisi rawat inap pada periode (berdasarkan tanggal masuk) + status, kamar, lama rawat. */
function rnRows(mysqli $k, int $idc, array $p): array
{
   $sql = "SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.noKartu, pv.patient_name_pcare, pv.id_patient,
                  pv.id_doctor, pv.diagnosa, pv.kondisi_pulang, pv.cara_keluar, pv.id_provider, mp.provider_name,
                  ms.patient_nik, ms.nomor_rm, ms.patient_gender, ms.patient_datebirth
           FROM pasien_visit pv
           LEFT JOIN ms_provider mp ON mp.id_provider = pv.id_provider
           LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
           WHERE pv.visit_date BETWEEN ? AND ? AND pv.id_customer = ?
             AND pv.status_rawatinap = 1 AND (pv.visit_status IS NULL OR pv.visit_status <> 99)";
   $t = 'ssi';
   $a = [$p['from'], $p['to'], $idc];
   if ($p['doctor'] !== '') { $sql .= ' AND pv.id_doctor = ?'; $t .= 's'; $a[] = $p['doctor']; }
   if ($p['provider']) { $sql .= ' AND pv.id_provider = ?'; $t .= 'i'; $a[] = $p['provider']; }
   $sql .= ' ORDER BY pv.visit_date DESC, pv.visit_time DESC';
   $visit = rnQ($k, $sql, $t, $a);
   if (!$visit) return [];
   $ids = array_column($visit, 'visit_ID');

   // kamar (permintaan terakhir per kunjungan)
   $kamar = [];
   foreach (rnPerVisit($k, "SELECT pr.visit_ID_inpatient v, pr.id_ranap, pr.ranap_date, pr.ranap_time, pr.status, pr.id_room,
                                   r.room_name, r.service_class, b.bed_name
                            FROM permintaan_ranap pr
                            LEFT JOIN ms_room r ON r.id_room = pr.id_room
                            LEFT JOIN ms_room_bed b ON b.id_bed = pr.id_bed
                            WHERE pr.visit_ID_inpatient IN ({IDS})", $ids) as $r) {
      if (!isset($kamar[$r['v']]) || $r['id_ranap'] > $kamar[$r['v']]['id_ranap']) $kamar[$r['v']] = $r;
   }
   $resume = [];
   foreach (rnPerVisit($k, "SELECT visit_ID v, tanggal_pulang, jam_pulang, id_resume FROM resume_medis WHERE visit_ID IN ({IDS})", $ids) as $r) {
      if (!isset($resume[$r['v']]) || $r['id_resume'] > $resume[$r['v']]['id_resume']) $resume[$r['v']] = $r;
   }
   $cppt = [];
   foreach (rnPerVisit($k, "SELECT visit_ID v, COUNT(*) n, MAX(cppt_date) akhir FROM visit_cppt WHERE id_customer = ? AND visit_ID IN ({IDS}) GROUP BY visit_ID", $ids, 'i', [$idc]) as $r) {
      $cppt[$r['v']] = $r;
   }

   $hariIni = date('Y-m-d');
   $out = [];
   foreach ($visit as $v) {
      $id = $v['visit_ID'];
      $km = $kamar[$id] ?? null;
      $rs = $resume[$id] ?? null;
      // tanggal masuk = tanggal kunjungan. ranap_date di permintaan_ranap diketik manual & sering keliru (mis. bulan +1),
      // jadi hanya jamnya yang dipakai bila tanggalnya sama dengan tanggal kunjungan.
      $masuk = $v['visit_date'];
      $jamMasuk = ($km && rnTanggal($km['ranap_date']) === $masuk && $km['ranap_time']) ? $km['ranap_time'] : $v['visit_time'];
      $pulang = rnTanggal($rs['tanggal_pulang'] ?? null);
      $sudahPulang = $pulang !== null || ($km && $km['status'] === 'pulang');
      $nCppt = (int) ($cppt[$id]['n'] ?? 0);
      $status = $sudahPulang ? 'pulang' : ($nCppt ? 'dirawat' : 'belum');
      if ($p['status'] !== '' && $p['status'] !== $status) continue;
      if ($p['room'] && (int) ($km['id_room'] ?? 0) !== $p['room']) continue;

      // lama rawat (hari, minimal 1): sampai tanggal pulang, atau sampai hari ini bila masih dirawat
      $los = null;
      $losValid = true;
      $akhir = $pulang ?? ($sudahPulang ? null : $hariIni);
      if ($akhir) {
         $selisih = (int) (new DateTime($masuk))->diff(new DateTime($akhir))->format('%r%a');
         if ($selisih < 0 || $selisih > RN_LOS_MAKS) $losValid = false;
         else $los = max(1, $selisih);
      }
      $out[] = [
         'visit_ID' => $id,
         'masuk' => $masuk,
         'jam' => substr((string) $jamMasuk, 0, 5),
         'pulang' => $pulang,
         'jam_pulang' => $pulang ? substr((string) ($rs['jam_pulang'] ?? ''), 0, 5) : '',
         'los' => $los,
         'los_valid' => $losValid,
         'status' => $status,
         'cppt' => $nCppt,
         'nama' => (string) $v['patient_name_pcare'],
         'rm' => (string) ($v['nomor_rm'] ?? ''),
         'nik' => (string) ($v['patient_nik'] ?? ''),
         'noKartu' => (string) $v['noKartu'],
         'gender' => apGender($v['patient_gender']),
         'umur' => rnUmur($v['patient_datebirth'], $masuk),
         'dokter' => trim((string) $v['id_doctor']) ?: '-',
         'diagnosa' => strtoupper(trim((string) $v['diagnosa'])),
         'kamar' => $km['room_name'] ?? '',
         'id_room' => (int) ($km['id_room'] ?? 0),
         'bed' => $km['bed_name'] ?? '',
         'kelas' => $km['service_class'] ?? '',
         'bayar' => $v['provider_name'] ?: '-',
         'kondisi' => trim((string) $v['kondisi_pulang']) ?: '',
         'cara_keluar' => trim((string) $v['cara_keluar']) ?: '',
      ];
   }
   return $out;
}

/** Jumlah bed aktif & kamar (untuk BOR dan pilihan filter). */
function rnKamar(mysqli $k, int $idc): array
{
   $rooms = rnQ($k, "SELECT r.id_room, r.room_name, r.service_class, COUNT(b.id_bed) bed
                     FROM ms_room r LEFT JOIN ms_room_bed b ON b.id_room = r.id_room
                     WHERE r.id_customer = ? AND (r.room_status IS NULL OR r.room_status <> '0')
                     GROUP BY r.id_room ORDER BY r.room_name", 'i', [$idc]);
   return ['rooms' => $rooms, 'bed' => array_sum(array_column($rooms, 'bed'))];
}

/** Pasien yang sedang dirawat sekarang (tanpa melihat periode). */
function rnSedangDirawat(mysqli $k, int $idc): int
{
   // aktif di permintaan_ranap dan belum punya tanggal pulang di resume medis
   $r = rnQ($k, "SELECT COUNT(DISTINCT pr.visit_ID_inpatient) n FROM permintaan_ranap pr
                 JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient
                 LEFT JOIN (SELECT DISTINCT visit_ID FROM resume_medis WHERE tanggal_pulang IS NOT NULL AND tanggal_pulang <> '') rm
                        ON rm.visit_ID = pr.visit_ID_inpatient
                 WHERE pr.status = 'aktif' AND rm.visit_ID IS NULL AND pv.id_customer = ? AND pv.status_rawatinap = 1
                   AND (pv.visit_status IS NULL OR pv.visit_status <> 99)
                   AND pv.visit_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)", 'i', [$idc]);
   return (int) ($r[0]['n'] ?? 0);
}

function rnRingkasan(array $rows, array $p, int $bed, mysqli $k): array
{
   $st = ['belum' => 0, 'dirawat' => 0, 'pulang' => 0];
   $los = [];
   $losTidakValid = 0;
   $kond = $dok = $diag = $bayar = $kamar = [];
   $gender = ['L' => 0, 'P' => 0, '-' => 0];
   $umur = ['0–5' => 0, '6–17' => 0, '18–44' => 0, '45–59' => 0, '60+' => 0, '-' => 0];
   $losBin = ['1 hari' => 0, '2 hari' => 0, '3 hari' => 0, '4 hari' => 0, '5 hari' => 0, '6–7 hari' => 0, '> 7 hari' => 0];
   $tanpaCppt = 0;
   $hariPasien = 0;
   $perHari = $p['hari'] <= 92;
   $tren = [];
   if ($perHari) {
      for ($d = new DateTime($p['from']); $d->format('Y-m-d') <= $p['to']; $d->modify('+1 day')) $tren[$d->format('Y-m-d')] = ['masuk' => 0, 'pulang' => 0];
   }
   $awal = new DateTime($p['from']);
   $akhirP = (new DateTime($p['to']))->modify('+1 day');
   $hariIni = new DateTime(date('Y-m-d'));

   foreach ($rows as $r) {
      $st[$r['status']]++;
      if (!$r['cppt']) $tanpaCppt++;
      $kunci = $perHari ? $r['masuk'] : substr($r['masuk'], 0, 7);
      $tren[$kunci] ??= ['masuk' => 0, 'pulang' => 0];
      $tren[$kunci]['masuk']++;
      if ($r['pulang'] && $r['pulang'] >= $p['from'] && $r['pulang'] <= $p['to']) {
         $kp = $perHari ? $r['pulang'] : substr($r['pulang'], 0, 7);
         $tren[$kp] ??= ['masuk' => 0, 'pulang' => 0];
         $tren[$kp]['pulang']++;
      }
      if ($r['status'] === 'pulang') {
         if ($r['los'] !== null) {
            $los[] = $r['los'];
            $b = $r['los'] <= 5 ? $r['los'] . ' hari' : ($r['los'] <= 7 ? '6–7 hari' : '> 7 hari');
            $losBin[$b]++;
         } elseif (!$r['los_valid'] || !$r['pulang']) {
            $losTidakValid++;
         }
         $kn = $r['kondisi'] ?: 'Tidak diisi';
         $kond[$kn] = ($kond[$kn] ?? 0) + 1;
      }
      // hari rawat di dalam periode (untuk BOR)
      if ($r['los_valid']) {
         $m = new DateTime($r['masuk']);
         $z = $r['pulang'] ? new DateTime($r['pulang']) : ($r['status'] === 'pulang' ? (clone $m)->modify('+1 day') : (clone $hariIni)->modify('+1 day'));
         if ($z <= $m) $z = (clone $m)->modify('+1 day');
         $a = max($m, $awal);
         $b = min($z, $akhirP);
         if ($b > $a) $hariPasien += (int) $a->diff($b)->days;
      }
      $dok[$r['dokter']] = ($dok[$r['dokter']] ?? 0) + 1;
      if ($r['diagnosa'] !== '') $diag[$r['diagnosa']] = ($diag[$r['diagnosa']] ?? 0) + 1;
      $bayar[$r['bayar']] = ($bayar[$r['bayar']] ?? 0) + 1;
      $km = $r['kamar'] ?: 'Tanpa kamar';
      $kamar[$km] = ($kamar[$km] ?? 0) + 1;
      $gender[$r['gender']]++;
      $u = $r['umur'];
      $umur[$u === null ? '-' : ($u <= 5 ? '0–5' : ($u <= 17 ? '6–17' : ($u <= 44 ? '18–44' : ($u <= 59 ? '45–59' : '60+'))))]++;
   }
   ksort($tren);
   $urut = function (array $a, int $n) {
      arsort($a);
      $o = [];
      foreach (array_slice($a, 0, $n, true) as $k => $v) $o[] = ['nama' => (string) $k, 'jumlah' => $v];
      $sisa = array_sum(array_slice($a, $n, null, true));
      if ($sisa) $o[] = ['nama' => 'Lainnya', 'jumlah' => $sisa];
      return $o;
   };
   // nama diagnosis 10 teratas
   arsort($diag);
   $topDiag = array_slice($diag, 0, 10, true);
   $nama = [];
   if ($topDiag) {
      $kode = array_keys($topDiag);
      $ph = implode(',', array_fill(0, count($kode), '?'));
      foreach (rnQ($k, "SELECT UPPER(TRIM(code)) c, MAX(icd10) n FROM icd_10 WHERE code IN ($ph) GROUP BY c", str_repeat('s', count($kode)), $kode) as $x) $nama[$x['c']] = $x['n'];
   }
   $total = count($rows);
   $sembuh = ($kond['Sembuh'] ?? 0) + ($kond['Membaik'] ?? 0);
   $periodeHari = $p['hari'];
   return [
      'total' => $total,
      'status' => $st,
      'alos' => $los ? round(array_sum($los) / count($los), 1) : 0,
      'los_median' => $los ? (function ($a) { sort($a); $n = count($a); return $n % 2 ? $a[intdiv($n, 2)] : ($a[$n / 2 - 1] + $a[$n / 2]) / 2; })($los) : 0,
      'los_tidak_valid' => $losTidakValid,
      'tanpa_cppt' => $tanpaCppt,
      'hari_pasien' => $hariPasien,
      'bed' => $bed,
      'bor' => $bed ? round($hariPasien / ($bed * $periodeHari) * 100, 1) : null,
      'pct_sembuh' => $st['pulang'] ? round($sembuh / $st['pulang'] * 100, 1) : 0,
      'periode' => $perHari ? 'hari' : 'bulan',
      'tren' => array_map(fn($k, $v) => ['t' => $k] + $v, array_keys($tren), $tren),
      'kondisi' => $urut($kond, 6),
      'dokter' => $urut($dok, 8),
      'bayar' => $urut($bayar, 5),
      'kamar' => $urut($kamar, 10),
      'los_bin' => array_map(fn($k, $v) => ['nama' => $k, 'jumlah' => $v], array_keys($losBin), $losBin),
      'diagnosa' => array_map(fn($c, $n) => ['kode' => $c, 'nama' => $nama[$c] ?? '', 'jumlah' => $n], array_keys($topDiag), $topDiag),
      'gender' => $gender,
      'umur' => array_map(fn($k, $v) => ['nama' => $k, 'jumlah' => $v], array_keys($umur), $umur),
   ];
}

function rnLabelFilter(array $p, array $rooms = []): string
{
   $f = [];
   if ($p['doctor'] !== '') $f[] = 'Dokter: ' . $p['doctor'];
   if ($p['provider']) $f[] = 'Provider #' . $p['provider'];
   if ($p['room']) {
      $nm = '';
      foreach ($rooms as $r) if ((int) $r['id_room'] === $p['room']) $nm = $r['room_name'];
      $f[] = 'Kamar: ' . ($nm ?: '#' . $p['room']);
   }
   if ($p['status']) $f[] = 'Status: ' . ['belum' => 'Belum ada CPPT', 'dirawat' => 'Dirawat', 'pulang' => 'Pulang'][$p['status']];
   return $f ? implode(' · ', $f) : 'Semua data';
}

const RN_STATUS_LABEL = ['belum' => 'Belum ada CPPT', 'dirawat' => 'Dirawat', 'pulang' => 'Pulang'];
