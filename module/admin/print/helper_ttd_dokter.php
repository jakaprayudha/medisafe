<?php

/*
 * Normalisasi nama dokter: abaikan prefix (dr, dr., dokter, drg, prof),
 * gelar belakang (Sp.PD, M.Ked, dll), huruf besar/kecil, titik, koma, dan spasi.
 */
if (!function_exists('normalisasiNamaDokter')) {
    function normalisasiNamaDokter(?string $nama): string
    {
        $nama = strtolower(trim((string)$nama));
        if ($nama === '') return '';

        // gelar belakang yang dipisah koma
        $nama = explode(',', $nama)[0];
        $nama = preg_replace('/\([^)]*\)/', ' ', $nama);
        $nama = str_replace('.', ' ', $nama);

        $prefix = ['dr', 'dokter', 'drg', 'prof', 'dra'];
        $gelarAwal = '/^(sp[a-z]*|mked[a-z]*|mkes|mkm|msi|mm|mbiomed|msc|mars|sked|fics|facs|finasim|phd)$/';
        $gelarKe2  = ['ked', 'kes', 'km', 'si', 'm', 'sc', 'biomed', 'h', 'kom'];

        $tokens = preg_split('/\s+/', trim($nama), -1, PREG_SPLIT_NO_EMPTY);
        while ($tokens && in_array($tokens[0], $prefix, true)) {
            array_shift($tokens);
        }
        // potong dari gelar belakang pertama (tanpa koma), nama minimal 1 kata
        foreach ($tokens as $i => $t) {
            if ($i === 0) continue;
            $next = $tokens[$i + 1] ?? '';
            if (preg_match($gelarAwal, $t) || (in_array($t, ['m', 's'], true) && in_array($next, $gelarKe2, true))) {
                $tokens = array_slice($tokens, 0, $i);
                break;
            }
        }
        return implode('', $tokens);
    }
}

if (!function_exists('cariTtdDokter')) {
    function cariTtdDokter($koneksi, $namaDokter, $idCustomer): ?string
    {
        $target = normalisasiNamaDokter($namaDokter);
        if ($target === '' || !$idCustomer) return null;

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT fullname, signature_user FROM ms_users
             WHERE id_customer = ? AND signature_user IS NOT NULL AND signature_user != ''"
        );
        if (!$stmt) return null;
        mysqli_stmt_bind_param($stmt, "s", $idCustomer);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $found = null;
        while ($u = mysqli_fetch_assoc($res)) {
            if (normalisasiNamaDokter($u['fullname']) === $target) {
                $found = $u['signature_user'];
                break;
            }
        }
        mysqli_stmt_close($stmt);
        return $found;
    }
}
