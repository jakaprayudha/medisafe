<?php

function generalisTubuhFields()
{
   return [
      'Mata' => [
         'konjungtiva' => ['label' => 'Konjungtiva', 'storage_label' => 'Konjungtiva', 'options' => ['Anemis', 'Tidak Anemis']],
         'sklera' => ['label' => 'Sklera', 'storage_label' => 'Sklera', 'options' => ['Ikterik', 'Tidak Ikterik']],
         'pupil' => ['label' => 'Pupil', 'storage_label' => 'Pupil', 'options' => ['Isokor', 'Anisokor']],
         'refleks_cahaya' => ['label' => 'Refleks cahaya', 'storage_label' => 'Refleks cahaya', 'options' => ['Positif', 'Negatif']],
         'palpebra' => ['label' => 'Palpebra', 'storage_label' => 'Palpebra', 'options' => ['Normal', 'Tidak Normal']],
      ],
      'Hidung' => [
         'septum_nasi' => ['label' => 'Septum nasi', 'storage_label' => 'Septum nasi', 'options' => ['Normal', 'Tidak Normal']],
         'konka' => ['label' => 'Konka', 'storage_label' => 'Konka', 'options' => ['Hiperemis', 'Tidak Hiperemis']],
         'hidung_massa' => ['label' => 'Massa', 'storage_label' => 'Hidung - Massa', 'options' => ['Positif', 'Negatif']],
         'discharge' => ['label' => 'Discharge', 'storage_label' => 'Discharge', 'options' => ['Positif', 'Negatif']],
      ],
      'Telinga' => [
         'kanalis_eksternus' => ['label' => 'Kanalis eksternus', 'storage_label' => 'Kanalis eksternus', 'options' => ['Normal', 'Tidak Normal']],
         'membran_timpani' => ['label' => 'Membran timpani', 'storage_label' => 'Membran timpani', 'options' => ['Normal', 'Tidak Normal']],
         'serumen' => ['label' => 'Serumen', 'storage_label' => 'Serumen', 'options' => ['Positif', 'Negatif']],
      ],
      'Orofaring' => [
         'tonsil' => ['label' => 'Tonsil', 'storage_label' => 'Tonsil', 'options' => ['T0-T0', 'T1-T1', 'T2-T2', 'T3-T3', 'T4-T4']],
         'mukosa' => ['label' => 'Mukosa', 'storage_label' => 'Mukosa', 'options' => ['Hiperemis', 'Tidak Hiperemis']],
         'orofaring_massa' => ['label' => 'Massa', 'storage_label' => 'Orofaring - Massa', 'options' => ['Positif', 'Negatif']],
      ],
      'Leher' => [
         'kgb' => ['label' => 'KGB', 'storage_label' => 'Leher - KGB', 'options' => ['Membesar', 'Tidak Membesar']],
         'kelenjar_thyroid' => ['label' => 'Kelenjar Thyroid', 'storage_label' => 'Leher - Kelenjar Thyroid', 'options' => ['Membesar', 'Tidak Membesar']],
      ],
      'Paru' => [
         'ronkhi' => ['label' => 'Ronkhi', 'storage_label' => 'Paru - Ronkhi', 'options' => ['Positif', 'Negatif']],
         'wheezing' => ['label' => 'Wheezing', 'storage_label' => 'Paru - Wheezing', 'options' => ['Positif', 'Negatif']],
      ],
      'Jantung' => [
         'murmur' => ['label' => 'Murmur', 'storage_label' => 'Jantung - Murmur', 'options' => ['Positif', 'Negatif']],
      ],
      'Abdomen' => [
         'bising_usus' => ['label' => 'Bising Usus', 'storage_label' => 'Abdomen - Bising Usus', 'options' => ['Positif', 'Negatif']],
         'nyeri_tekan' => ['label' => 'Nyeri Tekan', 'storage_label' => 'Abdomen - Nyeri Tekan', 'options' => ['Positif', 'Negatif']],
      ],
      'Ekstremitas Atas' => [
         'ekstremitas_atas_edema' => ['label' => 'Edema', 'storage_label' => 'Ekstremitas Atas - Edema', 'options' => ['Positif', 'Negatif']],
         'ekstremitas_atas_parese' => ['label' => 'Parese', 'storage_label' => 'Ekstremitas Atas - Parese', 'options' => ['Positif', 'Negatif']],
      ],
      'Ekstremitas Bawah' => [
         'ekstremitas_bawah_edema' => ['label' => 'Edema', 'storage_label' => 'Ekstremitas Bawah - Edema', 'options' => ['Positif', 'Negatif']],
         'ekstremitas_bawah_parese' => ['label' => 'Parese', 'storage_label' => 'Ekstremitas Bawah - Parese', 'options' => ['Positif', 'Negatif']],
      ],
   ];
}

function generalisTubuhValues($examinationText)
{
   $values = [];
   $fields = generalisTubuhFields();
   foreach ($fields as $groupFields) {
      foreach ($groupFields as $key => $_field) {
         $values[$key] = '';
      }
   }

   $start = '[PEMERIKSAAN GENERALIS TUBUH]';
   $end = '[/PEMERIKSAAN GENERALIS TUBUH]';
   $pattern = '/' . preg_quote($start, '/') . "\\R(.*?)\\R" . preg_quote($end, '/') . '/s';
   if (!preg_match($pattern, (string) $examinationText, $match)) {
      return $values;
   }

   foreach ($fields as $groupFields) {
      foreach ($groupFields as $key => $field) {
         $line = preg_quote($field['storage_label'], '/') . ':\\s*(.*)';
         if (preg_match('/^' . $line . '$/m', $match[1], $valueMatch)) {
            $values[$key] = trim($valueMatch[1]);
         }
      }
   }

   return $values;
}

function replaceGeneralisTubuhSection($examinationText, $values)
{
   $start = '[PEMERIKSAAN GENERALIS TUBUH]';
   $end = '[/PEMERIKSAAN GENERALIS TUBUH]';
   $pattern = '/' . preg_quote($start, '/') . "\\R.*?\\R" . preg_quote($end, '/') . '/s';
   $text = trim((string) preg_replace($pattern, '', (string) $examinationText));

   $lines = [];
   $hasValue = false;
   foreach (generalisTubuhFields() as $group => $groupFields) {
      $groupLines = [];
      foreach ($groupFields as $key => $field) {
         $value = trim((string) ($values[$key] ?? ''));
         if ($value === '') {
            continue;
         }
         $hasValue = true;
         $groupLines[] = $field['storage_label'] . ': ' . $value;
      }
      if ($groupLines) {
         $lines[] = $group;
         array_push($lines, ...$groupLines);
      }
   }

   if (!$hasValue) {
      return $text;
   }

   $section = $start . "\n" . implode("\n", $lines) . "\n" . $end;
   return $text === '' ? $section : $text . "\n\n" . $section;
}
