<?php
/**
 * Pemisahan data pembelian farmasi per faskes (pharmacy_order_buy, pharmacy_buy_detail).
 *
 * Dulu kedua tabel tidak punya id_customer: semua faskes melihat, mengubah, dan menghapus pembelian faskes lain.
 * Sekarang:
 *  - kolom id_customer ditambahkan otomatis bila belum ada (migrations/20261128c_pembelian_id_customer.sql);
 *  - data baru diisi id faskes session;
 *  - data lama (id_customer NULL, faskes pemilik tidak diketahui) hanya terlihat oleh superadmin.
 *
 *   pembelianSiapkan($koneksi);
 *   [$w, $t, $p] = pembelianFilter('o');   // "(o.id_customer = ? OR o.id_customer IS NULL)" / "o.id_customer = ?"
 */

function pembelianSiapkan(mysqli $db): void
{
   static $sudah = false;
   if ($sudah) return;
   $sudah = true;
   foreach (['pharmacy_order_buy', 'pharmacy_buy_detail'] as $tb) {
      try {
         $r = $db->query("SHOW COLUMNS FROM `$tb` LIKE 'id_customer'");
         if ($r && $r->num_rows === 0) {
            $db->query("ALTER TABLE `$tb` ADD COLUMN id_customer INT NULL, ADD KEY idx_customer (id_customer)");
         }
      } catch (Throwable $e) {
         if ((int) $e->getCode() !== 1060) error_log('[pembelianGuard] ' . $tb . ': ' . $e->getMessage()); // 1060 = sudah ada
      }
   }
}

function pembelianIdc(): int
{
   return (int) ($_SESSION['id_customer'] ?? 0);
}

/** Klausa WHERE faskes: [sql, tipe bind, nilai]. */
function pembelianFilter(string $alias = ''): array
{
   $k = ($alias !== '' ? $alias . '.' : '') . 'id_customer';
   $super = ($_SESSION['roles'] ?? '') === 'superadmin';
   return [$super ? "($k = ? OR $k IS NULL)" : "$k = ?", 'i', [pembelianIdc()]];
}

/** Tolak request bila tidak login / tanpa faskes. */
function pembelianWajibLogin(): void
{
   if (pembelianIdc() > 0) return;
   http_response_code(401);
   header('Content-Type: application/json; charset=utf-8');
   echo json_encode(['status' => 'error', 'message' => 'Session tidak valid / expired']);
   exit;
}

/** Nomor order milik faskes ini (atau data lama bila superadmin)? */
function pembelianOrderMilik(mysqli $db, string $orderNumber): bool
{
   [$w, $t, $p] = pembelianFilter();
   $st = $db->prepare("SELECT 1 FROM pharmacy_order_buy WHERE order_number = ? AND $w LIMIT 1");
   $st->bind_param('s' . $t, $orderNumber, ...$p);
   $st->execute();
   $ada = (bool) $st->get_result()->fetch_row();
   $st->close();
   return $ada;
}
