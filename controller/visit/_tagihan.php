<?php
/**
 * Hitung tagihan satu kunjungan (dipakai controller/visit/kasirDetail & controller/visit/bayar).
 * Rumus sama dengan controller/visit/kasirList dan module/print/struk_billing:
 *   obat = semua tiket resep kunjungan (qty * harga), jasa = pasien_billing (qty * harga - diskon, min 0).
 */
function ambilTagihan(mysqli $k, string $no, string $idc): array
{
   $st = $k->prepare("SELECT id_permintaan_farmasi, permintaan_number, status_permintaan, tipe_obat, rck_nama, created_at
      FROM permintaan_pharmacy WHERE id_visit = ? AND id_customer = ? ORDER BY created_at, id_permintaan_farmasi");
   $st->bind_param('ss', $no, $idc);
   $st->execute();
   $tiket = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   $obat = [];
   $totalObat = 0;
   if ($tiket) {
      $info = [];
      foreach ($tiket as $t) $info[(int) $t['id_permintaan_farmasi']] = $t;
      $ids = array_keys($info);
      // permintaan_pharmacy_details belum punya index id_permintaan_farmasi -> 1 query IN, bukan per tiket
      $st = $k->prepare("SELECT d.id_pharmacy_details, d.id_permintaan_farmasi, d.qty, d.harga, d.signa,
            m.pharmacy_name_generic, m.pharmacy_name_trade, m.pharmacy_unit
         FROM permintaan_pharmacy_details d LEFT JOIN ms_pharmacy m ON m.id_pharmacy = d.id_pharmacy
         WHERE d.id_permintaan_farmasi IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
         ORDER BY d.id_permintaan_farmasi, d.id_pharmacy_details");
      $st->bind_param(str_repeat('i', count($ids)), ...$ids);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $d) {
         $t = $info[(int) $d['id_permintaan_farmasi']];
         $qty = (int) $d['qty'];
         $harga = (int) $d['harga'];
         $obat[] = [
            'nama' => $d['pharmacy_name_generic'] ?: ($d['pharmacy_name_trade'] ?: '(obat tidak ditemukan di master)'),
            'satuan' => $d['pharmacy_unit'],
            'qty' => $qty,
            'harga' => $harga,
            'subtotal' => $qty * $harga,
            'id_tiket' => (int) $d['id_permintaan_farmasi'],
            'tiket' => $t['permintaan_number'],
            'tiket_dibuat' => $t['created_at'],
            'status_tiket' => (int) $t['status_permintaan'],
            'racikan' => strcasecmp((string) $t['tipe_obat'], 'Racikan') === 0 ? ($t['rck_nama'] ?: 'Racikan') : null,
         ];
         $totalObat += $qty * $harga;
      }
      $st->close();
   }

   $st = $k->prepare("SELECT id_billing, billing_item, billing_category, billing_price, billing_qty, billing_discount, billing_notes, created_at
      FROM pasien_billing WHERE id_visit = ? AND id_customer = ? ORDER BY id_billing");
   $st->bind_param('ss', $no, $idc);
   $st->execute();
   $jasa = [];
   $totalJasa = 0;
   foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $b) {
      $b['id_billing'] = (int) $b['id_billing'];
      $b['billing_price'] = (int) $b['billing_price'];
      $b['billing_qty'] = (int) $b['billing_qty'];
      $b['billing_discount'] = (int) $b['billing_discount'];
      $b['subtotal'] = max(0, $b['billing_qty'] * $b['billing_price'] - $b['billing_discount']);
      $totalJasa += $b['subtotal'];
      $jasa[] = $b;
   }
   $st->close();

   return [
      'obat' => $obat,
      'jasa' => $jasa,
      'tiket_belum_selesai' => count(array_filter($tiket, fn($t) => (int) $t['status_permintaan'] !== 3)),
      'total_obat' => $totalObat,
      'total_jasa' => $totalJasa,
      'total' => $totalObat + $totalJasa,
   ];
}

/** Kunjungan milik faskes (+ status bayar). null bila tidak ada. */
function ambilKunjungan(mysqli $k, string $no, string $idc): ?array
{
   $st = $k->prepare("SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.visit_status, pv.id_doctor, pv.id_poli, pv.id_provider, pv.status_rawatinap,
         pv.status_bayar, pv.metode_bayar, pv.nomor_transaksi, pv.amount_results, pv.amount_payment, pv.amount_changes, pv.noKartu,
         COALESCE(NULLIF(TRIM(pv.patient_name_pcare), ''), mp.patient_name) AS patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth,
         prv.provider_name
      FROM pasien_visit pv
      LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
      WHERE pv.visit_ID = ? AND pv.id_customer = ? LIMIT 1");
   $st->bind_param('ss', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}
