-- Tabel log_kunjungan versi awal (commit 3cfe3001) belum punya kolom pelaku_asli (akun asli saat switch user).
-- Kolom juga ditambahkan otomatis oleh utility/visitLog.php bila insert gagal karena kolom belum ada.
ALTER TABLE log_kunjungan ADD COLUMN pelaku_asli VARCHAR(150) NULL AFTER roles;
