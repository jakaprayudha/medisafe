<?php
/**
 * Laporan diagnosis ICD-10 (module/admin/rpt_diagnosis).
 * Semua diagnosis pada periode, diurutkan dari yang terbanyak (10 teratas ditandai), + ringkasan dashboard.
 * Agregasi dilakukan di SQL (GROUP BY), jadi rentang setahun tetap ringan.
 * Kunjungan batal (visit_status 99) tidak dihitung.
 */
require_once __DIR__ . '/appointmentService.php'; // apOpsi, apKlinik, apTglIndo

const DG_MAKS_HARI = 366;

/** Bab ICD-10 berdasarkan kode. */
function dgBab(string $kode): array
{
   static $bab = [
      ['A00', 'B99', 'I', 'Infeksi & parasit'],
      ['C00', 'D48', 'II', 'Neoplasma'],
      ['D50', 'D89', 'III', 'Darah & imunitas'],
      ['E00', 'E90', 'IV', 'Endokrin, nutrisi & metabolik'],
      ['F00', 'F99', 'V', 'Mental & perilaku'],
      ['G00', 'G99', 'VI', 'Sistem saraf'],
      ['H00', 'H59', 'VII', 'Mata'],
      ['H60', 'H95', 'VIII', 'Telinga'],
      ['I00', 'I99', 'IX', 'Sistem sirkulasi'],
      ['J00', 'J99', 'X', 'Sistem pernapasan'],
      ['K00', 'K93', 'XI', 'Sistem pencernaan'],
      ['L00', 'L99', 'XII', 'Kulit'],
      ['M00', 'M99', 'XIII', 'Muskuloskeletal'],
      ['N00', 'N99', 'XIV', 'Genitourinaria'],
      ['O00', 'O99', 'XV', 'Kehamilan & persalinan'],
      ['P00', 'P96', 'XVI', 'Perinatal'],
      ['Q00', 'Q99', 'XVII', 'Kelainan bawaan'],
      ['R00', 'R99', 'XVIII', 'Gejala & tanda'],
      ['S00', 'T98', 'XIX', 'Cedera & keracunan'],
      ['V01', 'Y98', 'XX', 'Sebab luar'],
      ['Z00', 'Z99', 'XXI', 'Pemeriksaan & faktor kesehatan'],
      ['U00', 'U99', 'XXII', 'Kode khusus'],
   ];
   $k = strtoupper(substr($kode, 0, 3));
   foreach ($bab as [$a, $z, $no, $nama]) {
      if ($k >= $a && $k <= $z) return [$no, $nama];
   }
   return ['-', 'Lainnya'];
}

function topDiagnosisPeriod(array $in): array
{
   $from = $in['fromDate'] ?? date('Y-m-01');
   $to = $in['toDate'] ?? date('Y-m-d');
   foreach ([$from, $to] as $v) {
      $d = is_string($v) ? DateTimeImmutable::createFromFormat('!Y-m-d', $v) : false;
      if (!$d || $d->format('Y-m-d') !== $v) throw new InvalidArgumentException('Format tanggal tidak valid.');
   }
   if ($from > $to) throw new InvalidArgumentException('Tanggal mulai tidak boleh melebihi tanggal akhir.');
   $hari = (new DateTimeImmutable($from))->diff(new DateTimeImmutable($to))->days + 1;
   if ($hari > DG_MAKS_HARI) throw new InvalidArgumentException('Rentang tanggal maksimal ' . DG_MAKS_HARI . ' hari.');
   $str = fn($k) => is_string($in[$k] ?? null) ? mb_substr(trim($in[$k]), 0, 150) : '';
   return [
      'fromDate' => $from,
      'toDate' => $to,
      'hari' => $hari,
      'jenis' => ($in['jenis'] ?? '') === 'semua' ? 'semua' : 'utama',
      'doctor' => $str('doctor'),
      'poli' => $str('poli'),
      'provider' => ctype_digit((string) ($in['provider'] ?? '')) ? (int) $in['provider'] : 0,
      'gender' => in_array($in['gender'] ?? '', ['pria', 'wanita'], true) ? $in['gender'] : '',
   ];
}

/** WHERE bersama + parameter. Kolom tanggal dipakai langsung agar index idx_date_cust terpakai. */
function dgWhere(int $idc, array $p): array
{
   $w = "pv.visit_date BETWEEN ? AND ? AND pv.id_customer = ? AND (pv.visit_status IS NULL OR pv.visit_status <> 99)";
   $t = 'ssi';
   $a = [$p['fromDate'], $p['toDate'], $idc];
   if ($p['doctor'] !== '') { $w .= ' AND pv.id_doctor = ?'; $t .= 's'; $a[] = $p['doctor']; }
   if ($p['poli'] !== '') { $w .= ' AND pv.id_poli = ?'; $t .= 's'; $a[] = $p['poli']; }
   if ($p['provider']) { $w .= ' AND pv.id_provider = ?'; $t .= 'i'; $a[] = $p['provider']; }
   if ($p['gender'] === 'pria') $w .= " AND LOWER(TRIM(ms.patient_gender)) IN ('l','pria','laki-laki','laki laki')";
   if ($p['gender'] === 'wanita') $w .= " AND LOWER(TRIM(ms.patient_gender)) IN ('p','wanita','perempuan')";
   return [$w, $t, $a];
}

function dgQuery(mysqli $k, string $sql, string $t, array $a): array
{
   $st = $k->prepare($sql);
   if (!$st) throw new RuntimeException('Gagal menyiapkan laporan diagnosis.');
   $st->bind_param($t, ...$a);
   if (!$st->execute()) throw new RuntimeException('Gagal mengambil laporan diagnosis.');
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

/** Nama diagnosis untuk kode-kode tertentu; kode tanpa nama dicoba memakai induknya (mis. Z00.09 -> Z00.0 -> Z00). */
function dgNama(mysqli $k, array $kode): array
{
   if (!$kode) return [];
   $cari = [];
   foreach ($kode as $c) {
      $cari[$c] = true;
      if (strlen($c) > 3) { $cari[substr($c, 0, 3)] = true; $cari[rtrim(substr($c, 0, 5), '.')] = true; }
   }
   $nama = [];
   foreach (array_chunk(array_keys($cari), 500) as $blok) {
      $ph = implode(',', array_fill(0, count($blok), '?'));
      foreach (dgQuery($k, "SELECT UPPER(TRIM(code)) c, icd10 n FROM icd_10 WHERE code IN ($ph)", str_repeat('s', count($blok)), $blok) as $r) {
         if ($r['n'] !== '' && !isset($nama[$r['c']])) $nama[$r['c']] = $r['n'];
      }
   }
   $out = [];
   foreach ($kode as $c) {
      $out[$c] = $nama[$c] ?? $nama[rtrim(substr($c, 0, 5), '.')] ?? (isset($nama[substr($c, 0, 3)]) ? $nama[substr($c, 0, 3)] . ' (induk)' : null);
   }
   return $out;
}

function topDiagnosisReport(mysqli $k, string $customerId, array $in): array
{
   $idc = (int) $customerId;
   $p = topDiagnosisPeriod($in);
   [$w, $t, $a] = dgWhere($idc, $p);
   $join = 'LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient';
   $L = "LOWER(TRIM(ms.patient_gender)) IN ('l','pria','laki-laki','laki laki')";
   $P = "LOWER(TRIM(ms.patient_gender)) IN ('p','wanita','perempuan')";

   // 1. ringkasan kunjungan
   $tot = dgQuery($k, "SELECT COUNT(*) kunjungan,
                              SUM(pv.diagnosa IS NULL OR TRIM(pv.diagnosa) = '') tanpa,
                              COUNT(DISTINCT CASE WHEN TRIM(pv.diagnosa) <> '' THEN pv.id_patient END) pasien
                       FROM pasien_visit pv $join WHERE $w", $t, $a)[0];

   // 2. diagnosis utama per kode
   $per = [];
   foreach (dgQuery($k, "SELECT UPPER(TRIM(pv.diagnosa)) kode, COUNT(*) n, SUM($L) l, SUM($P) p, COUNT(DISTINCT pv.id_patient) pasien
                         FROM pasien_visit pv $join
                         WHERE $w AND pv.diagnosa IS NOT NULL AND TRIM(pv.diagnosa) <> ''
                         GROUP BY UPPER(TRIM(pv.diagnosa))", $t, $a) as $r) {
      $per[$r['kode']] = ['utama' => (int) $r['n'], 'sekunder' => 0, 'l' => (int) $r['l'], 'p' => (int) $r['p'], 'pasien' => (int) $r['pasien']];
   }

   // 3. diagnosis sekunder (teks "K30,J06.9," — sebagian kecil kunjungan) dihitung di PHP
   $sekunderAda = 0;
   foreach (dgQuery($k, "SELECT pv.diagnosa_sekunder s, UPPER(TRIM(pv.diagnosa)) u FROM pasien_visit pv $join
                         WHERE $w AND pv.diagnosa_sekunder IS NOT NULL AND pv.diagnosa_sekunder NOT IN ('', ',', '[]', 'null')", $t, $a) as $r) {
      // kode yang sama dengan diagnosis utama kunjungan itu tidak dihitung dua kali
      $kode = array_diff(array_unique(array_filter(array_map(fn($x) => strtoupper(trim($x, " \t\"'[]")), preg_split('/[,;]+/', $r['s'])))), [(string) $r['u']]);
      if ($kode) $sekunderAda++;
      foreach ($kode as $c) {
         if (!preg_match('/^[A-Z][0-9]{2}(\.[0-9A-Z]{1,4})?$/', $c)) continue;
         $per[$c] ??= ['utama' => 0, 'sekunder' => 0, 'l' => 0, 'p' => 0, 'pasien' => 0];
         $per[$c]['sekunder']++;
      }
   }

   // 4. susun & urutkan semua diagnosis
   $nilai = fn($v) => $p['jenis'] === 'semua' ? $v['utama'] + $v['sekunder'] : $v['utama'];
   $per = array_filter($per, fn($v) => $nilai($v) > 0);
   uksort($per, fn($x, $y) => [$nilai($per[$y]), $x] <=> [$nilai($per[$x]), $y]);
   $nama = dgNama($k, array_keys($per));
   $total = array_sum(array_map($nilai, $per));
   $rows = [];
   $babAgg = [];
   $i = 0;
   foreach ($per as $c => $v) {
      [$babNo, $babNama] = dgBab($c);
      $n = $nilai($v);
      $rows[] = [
         'rank' => ++$i,
         'icd_code' => $c,
         'diagnosis_name' => $nama[$c] ?? 'Nama diagnosis tidak tersedia',
         'total_visits' => $n,
         'utama' => $v['utama'],
         'sekunder' => $v['sekunder'],
         'pct' => $total ? round($n / $total * 100, 2) : 0,
         'laki' => $v['l'],
         'perempuan' => $v['p'],
         'pasien' => $v['pasien'],
         'bab' => $babNo,
         'bab_nama' => $babNama,
      ];
      $babAgg[$babNo] ??= ['bab' => $babNo, 'nama' => $babNama, 'jumlah' => 0, 'kode' => 0];
      $babAgg[$babNo]['jumlah'] += $n;
      $babAgg[$babNo]['kode']++;
   }
   usort($babAgg, fn($x, $y) => $y['jumlah'] <=> $x['jumlah']);

   // 5. tren 5 diagnosis utama teratas
   $top5 = array_slice(array_keys(array_filter($per, fn($v) => $v['utama'] > 0)), 0, 5);
   $perHari = $p['hari'] <= 92;
   $tren = ['periode' => $perHari ? 'hari' : 'bulan', 'label' => [], 'seri' => []];
   if ($top5) {
      $grp = $perHari ? 'pv.visit_date' : "DATE_FORMAT(pv.visit_date, '%Y-%m')";
      $ph = implode(',', array_fill(0, count($top5), '?'));
      $data = dgQuery($k, "SELECT $grp t, UPPER(TRIM(pv.diagnosa)) kode, COUNT(*) n FROM pasien_visit pv $join
                           WHERE $w AND UPPER(TRIM(pv.diagnosa)) IN ($ph) GROUP BY t, kode", $t . str_repeat('s', count($top5)), [...$a, ...$top5]);
      $lab = [];
      if ($perHari) {
         for ($d = new DateTime($p['fromDate']); $d->format('Y-m-d') <= $p['toDate']; $d->modify('+1 day')) $lab[] = $d->format('Y-m-d');
      } else {
         for ($d = new DateTime(substr($p['fromDate'], 0, 7) . '-01'); $d->format('Y-m') <= substr($p['toDate'], 0, 7); $d->modify('+1 month')) $lab[] = $d->format('Y-m');
      }
      $idx = array_flip($lab);
      $seri = array_fill_keys($top5, array_fill(0, count($lab), 0));
      foreach ($data as $r) {
         if (isset($idx[$r['t']], $seri[$r['kode']])) $seri[$r['kode']][$idx[$r['t']]] = (int) $r['n'];
      }
      $tren['label'] = $lab;
      foreach ($seri as $c => $d) $tren['seri'][] = ['kode' => $c, 'nama' => $nama[$c] ?? $c, 'data' => $d];
   }

   $top10 = array_sum(array_column(array_slice($rows, 0, 10), 'total_visits'));
   $kunjungan = (int) $tot['kunjungan'];
   $tanpa = (int) $tot['tanpa'];
   return $p + [
      'data' => $rows,
      'total_top10' => $top10,
      'ringkasan' => [
         'kunjungan' => $kunjungan,
         'terdiagnosis' => $kunjungan - $tanpa,
         'tanpa_diagnosis' => $tanpa,
         'pct_tanpa' => $kunjungan ? round($tanpa / $kunjungan * 100, 1) : 0,
         'kode_unik' => count($rows),
         'pasien' => (int) $tot['pasien'],
         'total_diagnosis' => $total,
         'top10' => $top10,
         'pct_top10' => $total ? round($top10 / $total * 100, 1) : 0,
         'dengan_sekunder' => $sekunderAda,
         'bab' => $babAgg,
         'tren' => $tren,
      ],
   ];
}

/** Daftar kunjungan untuk satu kode (utama, atau utama+sekunder bila jenis=semua). */
function topDiagnosisPatients(mysqli $k, string $customerId, array $in): array
{
   $idc = (int) $customerId;
   $p = topDiagnosisPeriod($in);
   $code = is_string($in['icd_code'] ?? null) ? strtoupper(trim($in['icd_code'])) : '';
   if ($code === '' || strlen($code) > 32) throw new InvalidArgumentException('Kode diagnosis tidak valid.');
   [$w, $t, $a] = dgWhere($idc, $p);
   $kond = 'UPPER(TRIM(pv.diagnosa)) = ?';
   $t .= 's';
   $a[] = $code;
   if ($p['jenis'] === 'semua') {
      $kond = "($kond OR CONCAT(',', REPLACE(UPPER(pv.diagnosa_sekunder), ' ', ''), ',') LIKE ?)";
      $t .= 's';
      $a[] = '%,' . str_replace(['%', '_'], ['\\%', '\\_'], $code) . ',%';
   }
   $rows = dgQuery($k, "SELECT pv.visit_ID, pv.visit_date,
                               COALESCE(NULLIF(ms.nomor_rm, ''), '-') nomor_rm,
                               COALESCE(NULLIF(ms.patient_name, ''), NULLIF(pv.patient_name_pcare, ''), '-') patient_name,
                               ms.patient_gender, COALESCE(NULLIF(pv.id_doctor, ''), '-') doctor_name,
                               COALESCE(NULLIF(pv.id_poli, ''), '-') poli,
                               IF(UPPER(TRIM(pv.diagnosa)) = ?, 'Utama', 'Sekunder') peran
                        FROM pasien_visit pv LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
                        WHERE $w AND $kond
                        ORDER BY pv.visit_date DESC, patient_name ASC", 's' . $t, [$code, ...$a]);
   foreach ($rows as &$r) {
      $r['patient_gender'] = apGender($r['patient_gender']);
   }
   unset($r);
   return ['fromDate' => $p['fromDate'], 'toDate' => $p['toDate'], 'icd_code' => $code, 'data' => $rows, 'total' => count($rows)];
}

function dgLabelFilter(array $p): string
{
   $f = [$p['jenis'] === 'semua' ? 'Diagnosis utama + sekunder' : 'Diagnosis utama'];
   if ($p['doctor'] !== '') $f[] = 'Dokter: ' . $p['doctor'];
   if ($p['poli'] !== '') $f[] = 'Poli: ' . $p['poli'];
   if ($p['provider']) $f[] = 'Provider #' . $p['provider'];
   if ($p['gender']) $f[] = 'Gender: ' . ($p['gender'] === 'pria' ? 'Laki-laki' : 'Perempuan');
   return implode(' · ', $f);
}
