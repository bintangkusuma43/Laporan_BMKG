<?php

require_once __DIR__ . '/../_pdf_helpers.php';

/**
 * Template PDF Seismograph (Preventif Maintenance) berbasis Dompdf.
 *
 * Payload format mengikuti output api/laporan/detail.php:
 * - laporan: row laporan
 * - detail: decoded JSON (detail_json)
 * - dokumentasi: rows dokumentasi_foto
 * - lampiran: rows laporan_lampiran (opsional)
 */
function pdf_template_seismograph(array $payload, ?string $kopDataUri, bool $imagesEnabled = true): string
{
    $laporan = $payload['laporan'] ?? [];
    $detail = $payload['detail'] ?? [];
    $dokumentasi = is_array($payload['dokumentasi'] ?? null) ? $payload['dokumentasi'] : [];

    $stasiun = is_array($detail['stasiun'] ?? null) ? $detail['stasiun'] : [];
    $tugas = is_array($detail['tugas'] ?? null) ? $detail['tugas'] : [];

    $namaStasiunUpper = mb_strtoupper((string)($stasiun['nama_stasiun'] ?? ($laporan['nama_stasiun'] ?? '')), 'UTF-8');

    $tanggalPemeliharaan = pdf_format_date_id((string)($tugas['tanggal_pemeliharaan'] ?? ($laporan['tanggal_laporan'] ?? '')), true);

    // Map caption -> dataURI (case-insensitive)
    $captionToSrc = [];
    if ($imagesEnabled) {
      foreach ($dokumentasi as $doc) {
        $cap = trim((string)($doc['caption'] ?? ''));
        $fileName = (string)($doc['file_name'] ?? '');
        if ($cap === '' || $fileName === '') {
          continue;
        }
        $src = pdf_image_data_uri_from_upload($fileName);
        if (!$src) {
          continue;
        }
        $captionToSrc[mb_strtolower($cap, 'UTF-8')] = $src;
      }
    }

    // Kop surat is drawn via Dompdf canvas (see api/laporan/pdf.php) to match legacy ReportLab.

    $petugas = is_array($detail['petugas_pelaksana'] ?? null) ? $detail['petugas_pelaksana'] : [];
    $petugasHtml = '';
    if ($petugas) {
      foreach ($petugas as $i => $p) {
        if (!is_array($p)) {
          continue;
        }
        $nama = trim((string)($p['nama'] ?? ''));
        $peran = trim((string)($p['peran'] ?? ''));
        if ($nama === '' && $peran === '') {
          continue;
        }
        $line = ($i + 1) . '. ' . pdf_escape($nama !== '' ? $nama : '-');
        if ($peran !== '') {
          $line .= ' (' . pdf_escape($peran) . ')';
        }
        $petugasHtml .= '<div>' . $line . '</div>';
      }
    }
    if ($petugasHtml === '') {
      $petugasHtml = '<div>-</div>';
    }

    $kontak = is_array($stasiun['kontak'] ?? null) ? $stasiun['kontak'] : [];

    $sectionF = 'F';
    $sectionG = 'G';
    $hasPenggantian = false;
    $penggantian = is_array($detail['catatan_penggantian'] ?? null) ? $detail['catatan_penggantian'] : [];
    foreach ($penggantian as $item) {
        if (!is_array($item)) {
            continue;
        }
        $hasMeaningful = trim((string)($item['nama_alat'] ?? '')) !== '' || trim((string)($item['merk_type'] ?? '')) !== '' || ($item['jumlah'] ?? null) !== null;
        if ($hasMeaningful) {
            $hasPenggantian = true;
            break;
        }
    }
    if (!$hasPenggantian) {
        $sectionF = 'F';
        $sectionG = '';
    }

    // Bab A: Data Site (key-value)
    $alamat = nl2br(pdf_escape((string)($stasiun['alamat'] ?? '-')));
    $tanggalInstalasi = pdf_format_date_id((string)($stasiun['tanggal_instalasi'] ?? ''), true);

    $dataSiteRows = [
      ['Nama UPT Penanggung Jawab', (string)($stasiun['nama_upt'] ?? '-')],
      ['Nama Stasiun Seismik', (string)($stasiun['nama_stasiun'] ?? '-')],
      ['Kode Stasiun Seismik', (string)($stasiun['kode_stasiun'] ?? ($laporan['kode_stasiun'] ?? '-'))],
      ['Koordinat Stasiun Seismik<br/><i>(Lintang • Bujur • Elevasi)</i>', (string)($stasiun['koordinat'] ?? '-')],
      ['Alamat Lengkap Stasiun Seismik<br/><i>(Remote Site)</i>', $alamat],
      ['Nama Kontak Person', (string)($kontak['nama'] ?? '-')],
      ['No. HP Kontak Person', (string)($kontak['hp'] ?? '-')],
      ['Tahun Instalasi', $tanggalInstalasi],
      ['Status Stasiun Seismik', (string)($stasiun['status'] ?? '-')],
    ];

    $dataSiteHtml = '';
    foreach ($dataSiteRows as $row) {
      $k = (string)$row[0]; // label boleh berisi HTML (hardcoded)
      $v = (string)$row[1];
      // $v bisa sudah berisi HTML (alamat)
      if ($k !== 'Alamat Lengkap Stasiun Seismik<br/><i>(Remote Site)</i>') {
        $v = pdf_escape($v);
      }
      $dataSiteHtml .= '<tr><td class="k">' . $k . '</td><td class="v">' . $v . '</td></tr>';
    }

    // Bab B: Data Preventif Maintenance (match legacy)
    $nomorSpt = pdf_escape((string)($tugas['nomor_surat_tugas'] ?? ($laporan['nomor_surat'] ?? '-')));

    $dataPreventifRows = [
      ['Nomor Surat Tugas', $nomorSpt],
      ['Tanggal Pemeliharaan', $tanggalPemeliharaan],
      ['Tim Pemeliharaan', $petugasHtml],
    ];
    $dataPreventifHtml = '';
    foreach ($dataPreventifRows as $row) {
      $dataPreventifHtml .= '<tr><td class="k">' . pdf_escape((string)$row[0]) . '</td><td class="v">' . (string)$row[1] . '</td></tr>';
    }

    // Checklist sebelum/sesudah
    $checklist = is_array($detail['checklist'] ?? null) ? $detail['checklist'] : [];

    $buildChecklistTable = function (string $mode) use ($checklist, $captionToSrc, $imagesEnabled): string {
        $rows = '';
        foreach ($checklist as $item) {
            if (!is_array($item)) {
                continue;
            }
            $deskripsi = trim((string)($item['deskripsi'] ?? ''));
            $parameter = trim((string)($item['parameter'] ?? ''));
            $label = $deskripsi !== '' ? $deskripsi : 'Checklist';

            $capKeyPrimary = $parameter !== ''
              ? mb_strtolower($label . ' - ' . $parameter . ' - ' . $mode, 'UTF-8')
              : '';
            $capKeyFallback = mb_strtolower($label . ' - ' . $mode, 'UTF-8');
            $imgHtml = '<div class="muted">Tidak ada dokumentasi</div>';
            if ($imagesEnabled) {
              if ($capKeyPrimary !== '' && isset($captionToSrc[$capKeyPrimary])) {
                $imgHtml = '<div class="doc-center"><img class="doc" src="' . $captionToSrc[$capKeyPrimary] . '" /></div>';
              } elseif (isset($captionToSrc[$capKeyFallback])) {
                $imgHtml = '<div class="doc-center"><img class="doc" src="' . $captionToSrc[$capKeyFallback] . '" /></div>';
              }
            }

            $descCell = '<div class="b">' . pdf_escape($label) . '</div>';
            if ($parameter !== '') {
                $descCell .= '<div class="muted">' . pdf_escape($parameter) . '</div>';
            }

            $rows .= '<tr>'
                . '<td class="k">' . $descCell . '</td>'
                . '<td class="v">' . $imgHtml . '</td>'
                . '</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="2" class="c">-</td></tr>';
        }

        return '<table class="kv">'
            . '<thead><tr><th style="width:6cm;">DESKRIPSI</th><th>DOKUMENTASI</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>';
    };

    $checklistSebelumHtml = $buildChecklistTable('sebelum');
    $checklistSesudahHtml = $buildChecklistTable('sesudah');

    $kopHtml = $kopDataUri ? '<div class="kop"><img src="' . $kopDataUri . '" alt="Kop BMKG" /></div>' : '';

    // Bab D: Kegiatan Preventif
    $pembersihan = is_array($detail['pembersihan'] ?? null) ? $detail['pembersihan'] : [];
    $pengukuran = is_array($detail['pengukuran_power'] ?? null) ? $detail['pengukuran_power'] : [];
    $digitizerSensor = is_array($detail['digitizer_sensor'] ?? null) ? $detail['digitizer_sensor'] : [];

    $digitizerImg = '<div class="muted">Tidak ada dokumentasi</div>';
    $digitizerKey = mb_strtolower('Dokumentasi digitizer', 'UTF-8');
    if ($imagesEnabled && isset($captionToSrc[$digitizerKey])) {
        $digitizerImg = '<div class="doc-center"><img class="doc-wide" src="' . $captionToSrc[$digitizerKey] . '" /></div>';
    }

    $fmt = static function ($value, string $unit = ''): string {
        if ($value === null) {
            return '-';
        }
        $raw = trim((string)$value);
        if ($raw === '' || $raw === '-') {
            return '-';
        }
        return pdf_escape(trim($raw . ($unit !== '' ? ' ' . $unit : '')));
    };

    $teganganPsn = $fmt($pengukuran['tegangan_pln'] ?? '-', 'Volt');
    $sisaToken = $fmt($pengukuran['sisa_token'] ?? '-', 'kWh');

    $pRumput = $fmt($pembersihan['rumput'] ?? '-');
    $pSarang = $fmt($pembersihan['sarang'] ?? '-');
    $pPanel = $fmt($pembersihan['panel'] ?? '-');
    $pReg = $fmt($pembersihan['regulator'] ?? '-');
    $pBat = $fmt($pembersihan['baterai'] ?? '-');

    $centBB = $fmt($digitizerSensor['centering_broadband'] ?? '-');
    $levBB = $fmt($digitizerSensor['leveling_broadband'] ?? '-');
    $levAcc = $fmt($digitizerSensor['leveling_accelerometer'] ?? '-');
    $kabel = $fmt($digitizerSensor['kondisi_kabel'] ?? '-');

    $kegiatanRowsHtml = '';
    $addSub = function (string $title) use (&$kegiatanRowsHtml): void {
        $kegiatanRowsHtml .= '<tr class="subhead"><td colspan="2"><b>' . pdf_escape($title) . '</b></td></tr>';
    };
    $addRow = function (string $label, string $value) use (&$kegiatanRowsHtml): void {
        $kegiatanRowsHtml .= '<tr><td class="pel-label">' . pdf_escape($label) . '</td><td>' . $value . '</td></tr>';
    };

    $addSub('Pembersihan Lingkungan Shelter');
    $addRow('Pembersihan Rumput dan Dahan Pohon', $pRumput);
    $addRow('Pembersihan sarang Laba-laba', $pSarang);

    $addSub('Pembersihan Seluruh Peralatan');
    $addRow('Pembersihan Parabola dan Solar Cell', $pPanel);
    $addRow('Pembersihan Solar Regulator', $pReg);
    $addRow('Pembersihan Battery', $pBat);

    $addSub('Pengecekan Digitizer');
    $addRow('Dokumentasi', '<div class="img-title">Dokumentasi Digitizer</div>' . $digitizerImg);

    $addSub('Centering');
    $addRow('Centering Broadband', $centBB);

    $addSub('Leveling');
    $addRow('Leveling Sensor Broadband', $levBB);
    $addRow('Leveling Sensor Accelerometer', $levAcc);

    $addSub('Kabel');
    $addRow('Kondisi Kabel-Kabel', $kabel);

    $addSub('Pengukuran Power Solar Regulator');
    $regulatorList = is_array($pengukuran['regulator'] ?? null) ? $pengukuran['regulator'] : [];
    if ($regulatorList) {
        foreach ($regulatorList as $i => $r) {
            if (!is_array($r)) continue;
            $nama = pdf_escape((string)($r['nama'] ?? ('Regulator ' . ($i + 1))));
            $volt = $fmt($r['tegangan'] ?? '-', 'Volt');
            $cat = $fmt($r['catatan'] ?? '-');
            $val = '<div><b>' . $nama . '</b></div>'
                . '<div>Tegangan: ' . $volt . '</div>'
                . '<div>Catatan: ' . $cat . '</div>';
            $addRow('Regulator ' . ($i + 1), $val);
        }
    } else {
        $addRow('Detail Pengukuran Regulator', '-');
    }

    $addSub('Pengukuran Power Baterai');
    $bateraiList = is_array($pengukuran['baterai'] ?? null) ? $pengukuran['baterai'] : [];
    if ($bateraiList) {
        foreach ($bateraiList as $i => $b) {
            if (!is_array($b)) continue;
            $pos = pdf_escape((string)($b['posisi'] ?? ('Baterai ' . ($i + 1))));
            $volt = $fmt($b['tegangan'] ?? '-', 'Volt');
            $kon = $fmt($b['kondisi'] ?? '-');
            $val = '<div><b>' . $pos . '</b></div>'
                . '<div>Tegangan: ' . $volt . '</div>'
                . '<div>Kondisi: ' . $kon . '</div>';
            $addRow('Baterai ' . ($i + 1), $val);
        }
    } else {
        $addRow('Detail Pengukuran Baterai', '-');
    }

    $addSub('Pengukuran Listrik PLN');
    $addRow('Tegangan PLN', $teganganPsn);
    $addRow('Sisa Token', $sisaToken);

    $kegiatanTableHtml = '<table class="pel">'
        . '<colgroup><col style="width:5cm" /><col /></colgroup>'
        . '<thead><tr><th>DESKRIPSI</th><th>KETERANGAN</th></tr></thead>'
        . '<tbody>' . $kegiatanRowsHtml . '</tbody>'
        . '</table>';

    // Bab F Penggantian atau Rekomendasi
    $penggantianRows = '';
    foreach ($penggantian as $i => $p) {
        if (!is_array($p)) {
            continue;
        }
        $penggantianRows .= '<tr>'
            . '<td class="c">' . ($i + 1) . '</td>'
            . '<td>' . pdf_escape((string)($p['nama_alat'] ?? '-')) . '</td>'
            . '<td>' . pdf_escape((string)($p['merk_type'] ?? '-')) . '</td>'
            . '<td class="c">' . pdf_escape((string)($p['jumlah'] ?? '-')) . '</td>'
            . '<td>' . pdf_escape((string)($p['sn_baru'] ?? '-')) . '</td>'
            . '<td>' . pdf_escape((string)($p['sn_lama'] ?? '-')) . '</td>'
            . '<td>' . pdf_escape((string)($p['keterangan'] ?? '-')) . '</td>'
            . '</tr>';
    }
    if ($penggantianRows === '') {
        $penggantianRows = '<tr><td colspan="7" class="c">-</td></tr>';
    }

    $rekomendasi = nl2br(pdf_escape((string)($detail['rekomendasi'] ?? '-')));
    $catatan = nl2br(pdf_escape((string)($detail['catatan'] ?? '-')));

    // (Bab D values are formatted and assembled into $kegiatanTableHtml above)

    $fTitle = $hasPenggantian ? 'F. PENGGANTIAN ALAT (SUKU CADANG)' : 'F. REKOMENDASI';
    $gTitle = $hasPenggantian ? 'G. REKOMENDASI' : '';

    $penggantianSectionHtml = '';
    $rekomendasiSectionHtml = '';

    if ($hasPenggantian) {
        $penggantianSectionHtml = <<<HTML
      <div class="page-break"></div>
      <div class="page-pad"></div>
      <div class="section">
  <div class="section-title">{$fTitle}</div>
  <table>
    <thead>
      <tr>
        <th style="width:0.9cm;">No</th>
        <th>Nama Alat</th>
        <th>Merk/Type</th>
        <th style="width:1.6cm;">Jumlah</th>
        <th>S/N Baru</th>
        <th>S/N Lama</th>
        <th>Keterangan</th>
      </tr>
    </thead>
    <tbody>{$penggantianRows}</tbody>
  </table>
</div>
HTML;
        $rekomendasiSectionHtml = <<<HTML
      <div class="page-break"></div>
      <div class="page-pad"></div>
      <div class="section">
  <div class="section-title">{$gTitle}</div>
  <table class="kv">
    <tr><td class="v">{$rekomendasi}</td></tr>
  </table>
  <div style="height:0.5cm"></div>
  <div><b>Catatan:</b></div>
  <div>{$catatan}</div>
</div>
HTML;
    } else {
        $rekomendasiSectionHtml = <<<HTML
      <div class="page-break"></div>
      <div class="page-pad"></div>
      <div class="section">
  <div class="section-title">{$fTitle}</div>
  <table class="kv">
    <tr><td class="v">{$rekomendasi}</td></tr>
  </table>
  <div style="height:0.5cm"></div>
  <div><b>Catatan:</b></div>
  <div>{$catatan}</div>
</div>
HTML;
    }

    // Kop surat drawn via canvas.

    return <<<HTML
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <style>
    @page { margin: 2.5cm 2cm 3cm 2cm; }
    html, body { margin: 0; padding: 0; }
    body { font-family: "Times-Roman", Times, serif; font-size: 11pt; }
    .page { width: 17cm; margin: 0 auto; }
    .kop { height: 3.5cm; width: 100%; margin: 0; }
    .kop img { width: 100%; height: 3.5cm; object-fit: cover; }
    .pad-after-kop { height: 0.6cm; }
    .page-pad { height: 1cm; }
    .title { text-align:center; margin-top: 0.2cm; }
    .t1 { font-weight: bold; font-size: 12pt; margin: 0 0 6pt 0; }
    .t2 { font-weight: bold; font-size: 12pt; margin: 0 0 18pt 0; }

    table { width: 100%; max-width: 100%; border-collapse: collapse; }
    td, th { border: 0.5pt solid #000; padding: 4pt 6pt; vertical-align: top; }
    th { background: #e6e6e6; text-align: center; font-weight: bold; }
    .c { text-align: center; }
    .muted { color: #444; font-size: 9pt; }
    .b { font-weight: bold; }

    .section { margin-top: 14pt; margin-bottom: 12pt; }
    .footer-pad { height: 1.2cm; }
    .section-title { font-weight: bold; margin: 10pt 0 8pt 0; font-size: 11pt; }
    .kv td { border: 0.5pt solid #000; }
    .kv .k { width: 7cm; }

    .page-break { page-break-after: always; }

    .img-title { text-align: center; font-weight: bold; margin: 0 0 4pt 0; }
    .doc-center { text-align: center; }
    img.doc { width: 9.5cm; height: 7.125cm; object-fit: contain; }
    img.doc-wide { width: 9.5cm; height: 7.125cm; object-fit: contain; }

    .pel { page-break-inside: auto; margin-bottom: 12pt; }
    .pel tr { page-break-inside: avoid; page-break-after: auto; }
    .pel td { padding: 4pt 6pt; vertical-align: middle; }
    .pel .subhead td { background: #e6e6e6; }
    .pel .pel-label { font-size: 9pt; }
  </style>
</head>
<body>
  {$kopHtml}
  <div class="pad-after-kop"></div>
  <div class="page">
  <div class="title">
    <div class="t1">LAPORAN PREVENTIF MAINTENANCE</div>
    <div class="t2">STASIUN SEISMIK {$namaStasiunUpper}</div>
  </div>

  <div class="section">
    <div class="section-title">A. DATA SITE</div>
    <table class="kv">
      {$dataSiteHtml}
    </table>
  </div>

  <div class="section">
    <div class="section-title">B. DATA PREVENTIF MAINTENANCE</div>
    <table class="kv">
      {$dataPreventifHtml}
    </table>
  </div>

  <div class="page-break"></div>
  <div class="page-pad"></div>

  <div class="section">
    <div class="section-title">C. SEBELUM PREVENTIF MAINTENANCE</div>
    {$checklistSebelumHtml}
  </div>

  <div class="page-break"></div>
  <div class="page-pad"></div>

  <div class="section">
    <div class="section-title">D. KEGIATAN PREVENTIF MAINTENANCE</div>
    {$kegiatanTableHtml}
    <div class="footer-pad"></div>
  </div>

  <div class="page-break"></div>
  <div class="page-pad"></div>

  <div class="section">
    <div class="section-title">E. SESUDAH PREVENTIF MAINTENANCE</div>
    {$checklistSesudahHtml}
  </div>

  {$penggantianSectionHtml}
  {$rekomendasiSectionHtml}

  </div>

</body>
</html>
HTML;
}
