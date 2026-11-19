<?php
/**
 * Laporan appointment / kunjungan (dipakai appointmentController, export_appointment, print_appointment).
 * Satu sumber query + ringkasan agar tabel, dashboard, Excel dan cetak selalu berangka sama.
 */

const AP_MAKS_HARI = 366;

/** Kelompok status kunjungan (pasien_visit.visit_status). */
function apStatus(int $s): array
{
   return match ($s) {
      4 => ['selesai', 'Selesai'],
      99 => ['batal', 'Batal'],
      10 => ['booking', 'Belum check-in'],
      1 => ['proses', 'Menunggu'],
      2 => ['proses', 'Dipanggil'],
      3 => ['proses', 'Dilayani'],
      default => ['proses', 'Belum dilayani'],
   };
}

function apGender(?string $g): string
{
   $g = strtolower(trim((string) $g));
   if (in_array($g, ['l', 'pria', 'laki-laki', 'laki laki'], true)) return 'L';
   if (in_array($g, ['p', 'wanita', 'perempuan'], true)) return 'P';
   return '-';
}

/** Validasi & normalisasi filter. Melempar InvalidArgumentException bila tidak valid. */
function apParams(array $src): array
{
   $tgl = function ($v, $def) {
      $v = trim((string) ($v ?? ''));
      if ($v === '') return $def;
      $d = DateTime::createFromFormat('!Y-m-d', $v);
      if (!$d || $d->format('Y-m-d') !== $v) throw new InvalidArgumentException('Format tanggal tidak valid.');
      return $v;
   };
   $hari = date('Y-m-d');
   $from = $tgl($src['fromDate'] ?? null, $hari);
   $to = $tgl($src['toDate'] ?? null, $from);
   if ($to < $from) [$from, $to] = [$to, $from];
   $selisih = (new DateTime($from))->diff(new DateTime($to))->days + 1;
   if ($selisih > AP_MAKS_HARI) throw new InvalidArgumentException('Rentang tanggal maksimal ' . AP_MAKS_HARI . ' hari.');

   $str = fn($k) => mb_substr(trim((string) ($src[$k] ?? '')), 0, 150);
   $gender = in_array($src['gender'] ?? '', ['pria', 'wanita'], true) ? $src['gender'] : '';
   $status = in_array($src['status'] ?? '', ['selesai', 'proses', 'booking', 'batal'], true) ? $src['status'] : '';
   return [
      'from' => $from,
      'to' => $to,
      'hari' => $selisih,
      'doctor' => $str('doctor'),
      'poli' => $str('poli'),
      'provider' => ctype_digit((string) ($src['provider'] ?? '')) ? (int) $src['provider'] : 0,
      'gender' => $gender,
      'status' => $status,
   ];
}

/**
 * Ambil baris kunjungan. Hanya kolom yang dipakai (pasien_visit punya banyak kolom teks besar).
 * Filter tanggal memakai visit_date langsung (bukan DATE()) agar index idx_date_cust terpakai.
 */
function apRows(mysqli $k, int $idc, array $p): array
{
   $sql = "SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.visit_status, pv.noKartu, pv.patient_name_pcare,
                  pv.id_patient, pv.id_doctor, pv.id_poli, pv.id_provider, mp.provider_name,
                  ms.patient_nik, ms.patient_gender
           FROM pasien_visit pv
           LEFT JOIN ms_provider mp ON mp.id_provider = pv.id_provider
           LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
           WHERE pv.visit_date BETWEEN ? AND ? AND pv.id_customer = ?";
   $tipe = 'ssi';
   $arg = [$p['from'], $p['to'], $idc];
   if ($p['doctor'] !== '') {
      $sql .= ' AND pv.id_doctor = ?';
      $tipe .= 's';
      $arg[] = $p['doctor'];
   }
   if ($p['poli'] !== '') {
      $sql .= ' AND pv.id_poli = ?';
      $tipe .= 's';
      $arg[] = $p['poli'];
   }
   if ($p['provider']) {
      $sql .= ' AND pv.id_provider = ?';
      $tipe .= 'i';
      $arg[] = $p['provider'];
   }
   $sql .= ' ORDER BY pv.visit_date DESC, pv.visit_time DESC';

   $st = $k->prepare($sql);
   $st->bind_param($tipe, ...$arg);
   $st->execute();
   $res = $st->get_result();
   $out = [];
   while ($r = $res->fetch_assoc()) {
      $g = apGender($r['patient_gender']);
      if ($p['gender'] === 'pria' && $g !== 'L') continue;
      if ($p['gender'] === 'wanita' && $g !== 'P') continue;
      [$grup, $label] = apStatus((int) $r['visit_status']);
      if ($p['status'] !== '' && $p['status'] !== $grup) continue;
      $out[] = [
         'visit_ID' => $r['visit_ID'],
         'tanggal' => $r['visit_date'],
         'jam' => substr((string) $r['visit_time'], 0, 5),
         'status' => (int) $r['visit_status'],
         'grup' => $grup,
         'status_label' => $label,
         'noKartu' => (string) $r['noKartu'],
         'nik' => (string) $r['patient_nik'],
         'nama' => (string) $r['patient_name_pcare'],
         'id_patient' => (string) $r['id_patient'],
         'gender' => $g,
         'dokter' => trim((string) $r['id_doctor']) ?: '-',
         'poli' => trim((string) $r['id_poli']) ?: '-',
         'bayar' => $r['provider_name'] ?: '-',
      ];
   }
   $st->close();
   return $out;
}

/** Ringkasan untuk dashboard / Excel / cetak. */
function apRingkasan(array $rows, array $p): array
{
   $st = ['selesai' => 0, 'proses' => 0, 'booking' => 0, 'batal' => 0];
   $hari = $dok = $poli = $bayar = $jam = [];
   $gender = ['L' => 0, 'P' => 0, '-' => 0];
   $pasien = [];
   $bpjs = 0;

   // isi semua tanggal di rentang agar grafik tren tidak bolong
   if ($p['hari'] <= 92) {
      for ($d = new DateTime($p['from']); $d->format('Y-m-d') <= $p['to']; $d->modify('+1 day')) {
         $hari[$d->format('Y-m-d')] = ['selesai' => 0, 'proses' => 0, 'booking' => 0, 'batal' => 0];
      }
   }
   foreach ($rows as $r) {
      $st[$r['grup']]++;
      $kunci = $p['hari'] <= 92 ? $r['tanggal'] : substr($r['tanggal'], 0, 7);
      $hari[$kunci] ??= ['selesai' => 0, 'proses' => 0, 'booking' => 0, 'batal' => 0];
      $hari[$kunci][$r['grup']]++;
      if ($r['grup'] === 'batal') continue; // distribusi di bawah tanpa kunjungan batal
      $dok[$r['dokter']] = ($dok[$r['dokter']] ?? 0) + 1;
      $poli[$r['poli']] = ($poli[$r['poli']] ?? 0) + 1;
      $bayar[$r['bayar']] = ($bayar[$r['bayar']] ?? 0) + 1;
      $gender[$r['gender']]++;
      if ($r['jam'] !== '') {
         $h = (int) substr($r['jam'], 0, 2);
         $jam[$h] = ($jam[$h] ?? 0) + 1;
      }
      if ($r['id_patient'] !== '') $pasien[$r['id_patient']] = true;
      if ($r['noKartu'] !== '' || stripos($r['bayar'], 'bpjs') !== false) $bpjs++;
   }
   ksort($hari);
   ksort($jam);
   $urut = function (array $a, int $n) {
      arsort($a);
      $top = array_slice($a, 0, $n, true);
      $sisa = array_sum(array_slice($a, $n, null, true));
      $o = [];
      foreach ($top as $k => $v) $o[] = ['nama' => (string) $k, 'jumlah' => $v];
      if ($sisa) $o[] = ['nama' => 'Lainnya', 'jumlah' => $sisa];
      return $o;
   };
   $total = count($rows);
   $aktif = $total - $st['batal'];
   $jamSibuk = $jam ? array_search(max($jam), $jam, true) : null;
   return [
      'total' => $total,
      'aktif' => $aktif,
      'status' => $st,
      'pasien_unik' => count($pasien),
      'rate_selesai' => $aktif ? round($st['selesai'] / $aktif * 100, 1) : 0,
      'rate_batal' => $total ? round($st['batal'] / $total * 100, 1) : 0,
      'bpjs' => $bpjs,
      'rata_hari' => round($aktif / max(1, $p['hari']), 1),
      'jam_sibuk' => $jamSibuk,
      'periode' => $p['hari'] <= 92 ? 'hari' : 'bulan',
      'tren' => array_map(fn($k, $v) => ['t' => $k] + $v, array_keys($hari), $hari),
      'dokter' => $urut($dok, 8),
      'poli' => $urut($poli, 6),
      'bayar' => $urut($bayar, 5),
      'gender' => $gender,
      'jam' => array_map(fn($h, $v) => ['jam' => $h, 'jumlah' => $v], array_keys($jam), $jam),
   ];
}

/** Pilihan filter (dokter/poli) dari kunjungan di rentang tanggal, tanpa filter lain. */
function apOpsi(mysqli $k, int $idc, array $p): array
{
   $st = $k->prepare("SELECT DISTINCT TRIM(id_doctor) d, TRIM(id_poli) po FROM pasien_visit WHERE visit_date BETWEEN ? AND ? AND id_customer = ?");
   $st->bind_param('ssi', $p['from'], $p['to'], $idc);
   $st->execute();
   $dok = $poli = [];
   foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      if ($r['d'] !== null && $r['d'] !== '') $dok[$r['d']] = true;
      if ($r['po'] !== null && $r['po'] !== '') $poli[$r['po']] = true;
   }
   $st->close();
   $dok = array_keys($dok);
   $poli = array_keys($poli);
   natcasesort($dok);
   natcasesort($poli);
   $prov = $k->query("SELECT id_provider, provider_name FROM ms_provider ORDER BY provider_name")->fetch_all(MYSQLI_ASSOC);
   return ['dokter' => array_values($dok), 'poli' => array_values($poli), 'provider' => $prov];
}

function apLabelFilter(array $p): string
{
   $f = [];
   if ($p['doctor'] !== '') $f[] = 'Dokter: ' . $p['doctor'];
   if ($p['poli'] !== '') $f[] = 'Poli: ' . $p['poli'];
   if ($p['provider']) $f[] = 'Provider #' . $p['provider'];
   if ($p['gender']) $f[] = 'Gender: ' . ucfirst($p['gender']);
   if ($p['status']) $f[] = 'Status: ' . ['selesai' => 'Selesai', 'proses' => 'Dalam proses', 'booking' => 'Belum check-in', 'batal' => 'Batal'][$p['status']];
   return $f ? implode(' · ', $f) : 'Semua data';
}

function apTglIndo(string $ymd): string
{
   static $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
   $t = explode('-', $ymd);
   return count($t) === 3 ? (int) $t[2] . ' ' . $bln[(int) $t[1]] . ' ' . $t[0] : $ymd;
}

/** Nama & alamat klinik untuk kop laporan. */
function apKlinik(mysqli $k, int $idc): array
{
   $st = $k->prepare("SELECT * FROM setting_clinic WHERE id_customer = ? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc() ?: [];
   $st->close();
   return [
      'nama' => (string) ($r['clinic_name'] ?? 'Klinik'),
      'alamat' => (string) ($r['clinic_address'] ?? $r['address'] ?? ''),
   ];
}
