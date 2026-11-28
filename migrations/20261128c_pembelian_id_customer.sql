-- Pemisahan data pembelian farmasi per faskes. Dulu tabel tidak punya id_customer sehingga data bocor antar faskes.
-- Data lama dibiarkan id_customer NULL (pemilik tidak diketahui) dan hanya terlihat oleh superadmin.
-- Kolom juga ditambahkan otomatis oleh utility/pembelianGuard.php -> pembelianSiapkan().
ALTER TABLE pharmacy_order_buy ADD COLUMN id_customer INT NULL, ADD KEY idx_customer (id_customer);
ALTER TABLE pharmacy_buy_detail ADD COLUMN id_customer INT NULL, ADD KEY idx_customer (id_customer);
