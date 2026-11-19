<?php
/**
 * Penulis XLSX ringan (streaming). Baris langsung ditulis ke file sementara, jadi memori tetap kecil
 * walau puluhan ribu baris (PhpSpreadsheet menyimpan objek per sel dan kehabisan memori di ±60 ribu baris).
 *
 * Gaya sel (indeks s):
 *   0 normal · 1 header (tebal, putih, latar biru) · 2 persen 0.0% · 3 judul (tebal 14) · 4 abu-abu kecil
 *   5 normal + border · 6 angka ribuan #,##0 · 7 teks tebal
 */
class XlsxRingan
{
   private array $sheets = [];
   private ?int $aktif = null;
   private string $tmp;

   public function __construct()
   {
      $this->tmp = sys_get_temp_dir() . '/xlsx-' . bin2hex(random_bytes(6));
      mkdir($this->tmp, 0700);
   }

   public function sheet(string $nama, array $lebar = []): void
   {
      $i = count($this->sheets);
      $f = "{$this->tmp}/sheet" . ($i + 1) . '.xml';
      $this->sheets[$i] = ['nama' => mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $nama), 0, 31), 'file' => $f, 'h' => fopen($f, 'w'),
         'baris' => 0, 'lebar' => $lebar, 'freeze' => false, 'filter' => null, 'kolMaks' => 0];
      $this->aktif = $i;
   }

   public function freezeHeader(): void
   {
      $this->sheets[$this->aktif]['freeze'] = true;
   }

   public function autoFilter(int $kolom): void
   {
      $this->sheets[$this->aktif]['filter'] = $kolom;
   }

   public static function kol(int $i): string
   {
      $s = '';
      for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
         $s = chr(65 + ($i - 1) % 26) . $s;
      }
      return $s;
   }

   /** @param array $sel nilai skalar, atau ['v' => nilai, 's' => gaya, 't' => 'str' paksa teks] */
   public function row(array $sel, int $gaya = 0): void
   {
      $sh = &$this->sheets[$this->aktif];
      $r = ++$sh['baris'];
      $xml = '<row r="' . $r . '">';
      foreach (array_values($sel) as $i => $c) {
         $s = $gaya;
         $paksaTeks = false;
         if (is_array($c)) {
            $s = $c['s'] ?? $gaya;
            $paksaTeks = ($c['t'] ?? '') === 'str';
            $c = $c['v'] ?? null;
         }
         if ($c === null || $c === '') {
            if ($s) $xml .= '<c r="' . self::kol($i) . $r . '" s="' . $s . '"/>';
            continue;
         }
         $ref = self::kol($i) . $r;
         if (!$paksaTeks && (is_int($c) || is_float($c))) {
            $xml .= '<c r="' . $ref . '" s="' . $s . '"><v>' . $c . '</v></c>';
         } else {
            $t = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $c), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml .= '<c r="' . $ref . '" s="' . $s . '" t="inlineStr"><is><t xml:space="preserve">' . $t . '</t></is></c>';
         }
      }
      $sh['kolMaks'] = max($sh['kolMaks'], count($sel));
      fwrite($sh['h'], $xml . '</row>');
   }

   public function kosong(): void
   {
      $this->sheets[$this->aktif]['baris']++;
   }

   public function barisSekarang(): int
   {
      return $this->sheets[$this->aktif]['baris'];
   }

   private function tutupSheet(array $sh): string
   {
      fclose($sh['h']);
      $head = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
         . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
      if ($sh['freeze']) {
         $head .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
      }
      if ($sh['lebar']) {
         $head .= '<cols>';
         foreach (array_values($sh['lebar']) as $i => $w) {
            $head .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
         }
         $head .= '</cols>';
      }
      $head .= '<sheetData>';
      $tail = '</sheetData>';
      if ($sh['filter'] && $sh['baris'] > 1) {
         $tail .= '<autoFilter ref="A1:' . self::kol($sh['filter'] - 1) . $sh['baris'] . '"/>';
      }
      $tail .= '<pageMargins left="0.4" right="0.4" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>'
         . '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/></worksheet>';
      $final = $sh['file'] . '.full';
      $o = fopen($final, 'w');
      fwrite($o, $head);
      $i = fopen($sh['file'], 'r');
      stream_copy_to_stream($i, $o);
      fclose($i);
      fwrite($o, $tail);
      fclose($o);
      return $final;
   }

   public function kirim(string $namaFile): void
   {
      $zipPath = $this->tmp . '/out.xlsx';
      $z = new ZipArchive();
      $z->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
      $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
         . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
         . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
         . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
      $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
      $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
      $def = '';
      foreach ($this->sheets as $i => $sh) {
         $n = $i + 1;
         $z->addFile($this->tutupSheet($sh), "xl/worksheets/sheet$n.xml");
         $ct .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
         $wb .= '<sheet name="' . htmlspecialchars($sh['nama'], ENT_XML1) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
         $rels .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
         if ($sh['filter'] && $sh['baris'] > 1) {
            $def .= '<definedName name="_xlnm._FilterDatabase" localSheetId="' . $i . '" hidden="1">\'' . htmlspecialchars($sh['nama'], ENT_XML1) . '\'!$A$1:$' . self::kol($sh['filter'] - 1) . '$' . $sh['baris'] . '</definedName>';
         }
      }
      $wb .= '</sheets>' . ($def ? "<definedNames>$def</definedNames>" : '') . '</workbook>';
      $rels .= '<Relationship Id="rIdS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
      $z->addFromString('[Content_Types].xml', $ct . '</Types>');
      $z->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
      $z->addFromString('xl/workbook.xml', $wb);
      $z->addFromString('xl/_rels/workbook.xml.rels', $rels);
      $z->addFromString('xl/styles.xml', self::styles());
      $z->close();

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . str_replace('"', '', $namaFile) . '"');
      header('Content-Length: ' . filesize($zipPath));
      header('Cache-Control: max-age=0');
      readfile($zipPath);
      $this->bersihkan();
   }

   public function bersihkan(): void
   {
      foreach (glob($this->tmp . '/*') ?: [] as $f) @unlink($f);
      @rmdir($this->tmp);
   }

   private static function styles(): string
   {
      return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
         . '<numFmts count="1"><numFmt numFmtId="164" formatCode="0.0%"/></numFmts>'
         . '<fonts count="4"><font><sz val="10"/><name val="Calibri"/></font><font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
         . '<font><b/><sz val="14"/><name val="Calibri"/></font><font><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/></font></fonts>'
         . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
         . '<fill><patternFill patternType="solid"><fgColor rgb="FF2563EB"/><bgColor indexed="64"/></patternFill></fill></fills>'
         . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
         . '<border><left style="thin"><color rgb="FFD9DEE5"/></left><right style="thin"><color rgb="FFD9DEE5"/></right><top style="thin"><color rgb="FFD9DEE5"/></top><bottom style="thin"><color rgb="FFD9DEE5"/></bottom><diagonal/></border></borders>'
         . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
         . '<cellXfs count="8">'
         . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
         . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
         . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
         . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
         . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
         . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
         . '<xf numFmtId="3" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
         . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
         . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
   }
}
