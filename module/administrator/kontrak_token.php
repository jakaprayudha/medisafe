<?php
/**
 * Token HMAC untuk QR tanda tangan di dokumen kontrak (kontrak.php) dan verifikasinya (validasi-kontrak.php).
 * Kunci & format token sama persis dengan versi lama agar QR di kontrak yang sudah dicetak tetap valid.
 */
if (!function_exists('generateContractToken')) {
   function kontrakSecret(): string
   {
      $env = getenv('MEDISAFE_KONTRAK_SECRET');
      return $env !== false && $env !== '' ? $env : 'MEDISAFE-KONTRAK-2026-SECRET';
   }

   function generateContractToken($no, $party, $secretKey = null): string
   {
      return hash_hmac('sha256', $no . '|' . $party, $secretKey ?? kontrakSecret());
   }
}
