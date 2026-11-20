<?php
/**
 * Halaman tujuan QR tanda tangan di dokumen kontrak (kontrak.php).
 * Publik (dipindai pihak luar), jadi hanya menampilkan data minimum: nama faskes, nomor & masa berlaku kontrak,
 * dan pihak penanda tangan. Nilai kontrak & kontak PIC tidak ditampilkan.
 * Dulu QR mengarah ke file ini tetapi filenya tidak ada (404).
 */
require '../../database/connect.php';
require_once __DIR__ . '/kontrak_token.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$no = (int) ($_GET['no'] ?? 0);
$pihak = strtoupper((string) ($_GET['pihak'] ?? ''));
$token = (string) ($_GET['token'] ?? '');
$valid = $no > 0 && in_array($pihak, ['KATES', 'MITRA'], true) && hash_equals(generateContractToken($no, $pihak), $token);

$d = null;
if ($valid) {
   $st = mysqli_prepare($koneksi, "SELECT sc.clinic_name, f.contract_number, f.contract_date, f.contract_start, f.contract_end, f.pic_name, f.faskes_status
      FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id = ? LIMIT 1");
   mysqli_stmt_bind_param($st, 'i', $no);
   mysqli_stmt_execute($st);
   $d = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
   mysqli_stmt_close($st);
   if (!$d) $valid = false;
}

function h($v): string { return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function tgl($v): string
{
   $t = $v ? strtotime($v) : false;
   if (!$t || (int) date('Y', $t) < 2000) return '-';
   $b = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
   return date('j', $t) . ' ' . $b[(int) date('n', $t)] . ' ' . date('Y', $t);
}

$status = '';
if ($valid) {
   $akhir = ($d['contract_end'] ?? '') && (int) substr($d['contract_end'], 0, 4) >= 2000 ? strtotime($d['contract_end'] . ' 23:59:59') : 0;
   $status = !$akhir ? 'Masa berlaku belum diisi' : ($akhir < time() ? 'Masa berlaku sudah berakhir' : 'Berlaku');
}
$penandatangan = $pihak === 'KATES' ? 'Khairul Fadhli Margolang, M.Kom — Direktur PT. Kreatif Techno Solusindo' : (($d['pic_name'] ?? '') ?: '-') . ' — Penanggung Jawab ' . ($d['clinic_name'] ?? '');
?>
<!doctype html>
<html lang="id">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="robots" content="noindex">
   <title>Validasi Kontrak</title>
   <style>
      :root { color-scheme: light; }
      body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f1f5f9; font-family: system-ui, -apple-system, "Segoe UI", Arial, sans-serif; color: #1e293b; padding: 16px; box-sizing: border-box; }
      .kartu { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); max-width: 440px; width: 100%; overflow: hidden; }
      .atas { padding: 28px 24px 20px; text-align: center; }
      .ikon { width: 64px; height: 64px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 12px; }
      .ok .ikon { background: #dcfce7; color: #16a34a; }
      .no .ikon { background: #fee2e2; color: #dc2626; }
      h1 { font-size: 1.15rem; margin: 0 0 4px; }
      .sub { color: #64748b; font-size: .85rem; margin: 0; }
      dl { margin: 0; padding: 4px 24px 20px; }
      dt { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-top: 12px; }
      dd { margin: 2px 0 0; font-weight: 600; font-size: .92rem; }
      .kaki { border-top: 1px solid #e2e8f0; padding: 12px 24px; font-size: .75rem; color: #94a3b8; text-align: center; }
   </style>
</head>
<body>
   <div class="kartu <?= $valid ? 'ok' : 'no' ?>">
      <div class="atas">
         <div class="ikon"><?= $valid ? '&#10003;' : '&#10007;' ?></div>
         <h1><?= $valid ? 'Tanda tangan kontrak terverifikasi' : 'Tanda tangan tidak dapat diverifikasi' ?></h1>
         <p class="sub"><?= $valid ? 'QR ini diterbitkan oleh sistem Medisafe.' : 'QR tidak valid, sudah diubah, atau kontrak tidak ditemukan.' ?></p>
      </div>
      <?php if ($valid): ?>
         <dl>
            <dt>Penanda tangan</dt><dd><?= h($penandatangan) ?></dd>
            <dt>Fasilitas kesehatan</dt><dd><?= h($d['clinic_name']) ?></dd>
            <dt>Nomor kontrak</dt><dd><?= h(($d['contract_number'] ?? '') ?: '-') ?></dd>
            <dt>Tanggal kontrak</dt><dd><?= h(tgl($d['contract_date'] ?? null)) ?></dd>
            <dt>Masa berlaku</dt><dd><?= h(tgl($d['contract_start'] ?? null)) ?> s.d. <?= h(tgl($d['contract_end'] ?? null)) ?></dd>
            <dt>Status</dt><dd><?= h($status) ?></dd>
         </dl>
      <?php endif; ?>
      <div class="kaki">Medisafe · PT. Kreatif Techno Solusindo</div>
   </div>
</body>
</html>
