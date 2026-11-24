-- Tipe isian hasil per parameter laboratorium (master-lab-detail -> input hasil di modul lab).
--   numerik  : hanya angka (boleh desimal, koma/titik) -> dibandingkan dengan nilai minimum/maksimum
--   teks     : teks bebas (mis. Positif/Negatif, Kuning jernih)
--   keduanya : angka maupun teks (perilaku lama; default agar data lama tidak berubah)
ALTER TABLE laboratorium_item
  ADD COLUMN tipe_isian ENUM('numerik','teks','keduanya') NOT NULL DEFAULT 'keduanya' AFTER maksimum;
