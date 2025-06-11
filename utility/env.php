<?php
function loadEnv($file = null)
{
   // Gunakan jalur default jika $file tidak disediakan
   $file = $file ?? dirname(__DIR__, 1) . '/.env'; // Memperbaiki __DIR__

   if (!file_exists($file)) {
      throw new Exception("File .env tidak ditemukan pada: $file");
   }

   $env = [];
   $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

   foreach ($lines as $line) {
      if (strpos(trim($line), '#') === 0) {
         continue; // Abaikan baris komentar
      }

      // Memisahkan key dan value dengan tanda '='
      list($key, $value) = explode('=', $line, 2);
      $env[trim($key)] = trim($value);
   }

   // Set variabel lingkungan
   foreach ($env as $key => $value) {
      putenv("$key=$value");
   }

   return $env;
}
