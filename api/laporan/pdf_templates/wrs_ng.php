<?php

require_once __DIR__ . '/../_pdf_helpers.php';

/**
 * WRS NG PDF template (Dompdf HTML) based on legacy ReportLab layout.
 *
 * @param array $payload {laporan, detail, dokumentasi, lampiran}
 */
function pdf_template_wrs_ng(array $payload, ?string $kopDataUri, bool $imagesEnabled = true): string
{
    $laporan = is_array($payload['laporan'] ?? null) ? $payload['laporan'] : [];
    $detail = is_array($payload['detail'] ?? null) ? $payload['detail'] : [];
    $dokumentasi = is_array($payload['dokumentasi'] ?? null) ? $payload['dokumentasi'] : [];
    $lampiran = is_array($payload['lampiran'] ?? null) ? $payload['lampiran'] : [];

  $upper = static function (string $value): string {
    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
  };

  $tempatUpper = $upper((string)($detail['tempat'] ?? ''));
    $waktu = pdf_format_date_id((string)($laporan['tanggal_laporan'] ?? $detail['tanggal_kegiatan'] ?? ''), true);
    $generatedAt = pdf_format_datetime_id(new DateTime('now', new DateTimeZone('Asia/Jakarta')));

    $nomorSurat = pdf_escape((string)($laporan['nomor_surat'] ?? $detail['nomor_surat'] ?? '-'));
    $kegiatan = pdf_escape((string)($detail['kegiatan'] ?? '-'));
    $tempat = pdf_escape((string)($detail['tempat'] ?? '-'));

    $petugas = is_array($detail['petugas_pelaksana'] ?? null) ? $detail['petugas_pelaksana'] : [];
    $petugasRows = '';
    if ($petugas) {
      foreach ($petugas as $i => $p) {
        $nama = pdf_escape((string)($p['nama'] ?? '-'));
        $nip = trim((string)($p['nip'] ?? ''));
        $nipText = $nip !== '' ? ('NIP. ' . pdf_escape($nip)) : '-';

          $petugasRows .= '<tr>'
            . '<td class="c" style="width:1.2cm;">' . ($i + 1) . '</td>'
            . '<td style="width:8cm;">' . $nama . '</td>'
            . '<td>' . $nipText . '</td>'
            . '</tr>';
      }
    } else {
      $petugasRows = '<tr><td class="c" style="width:1.2cm;">-</td><td style="width:8cm;">-</td><td>-</td></tr>';
    }

    $kegiatanItems = is_array($detail['kegiatan_pemeliharaan'] ?? null) ? $detail['kegiatan_pemeliharaan'] : [];
    $kegiatanRows = '';
    if ($kegiatanItems) {
      foreach ($kegiatanItems as $i => $item) {
        $judul = pdf_escape((string)($item['title'] ?? $item['uraian'] ?? '-'));
        $descRaw = trim((string)($item['description'] ?? $item['keterangan'] ?? ''));
        $desc = $descRaw !== '' ? nl2br(pdf_escape($descRaw)) : '-';

          $kegiatanRows .= '<tr>'
            . '<td class="c" style="width:1.2cm;">' . ($i + 1) . '</td>'
            . '<td style="width:9.5cm;">' . $judul . '</td>'
            . '<td>' . $desc . '</td>'
            . '</tr>';
      }
    } else {
      $kegiatanRows = '<tr><td class="c" style="width:1.2cm;">-</td><td style="width:9.5cm;">-</td><td>-</td></tr>';
    }

    $penggantian = is_array($detail['catatan_penggantian'] ?? null) ? $detail['catatan_penggantian'] : [];
    $penggantianRows = '';
    if ($penggantian) {
      foreach ($penggantian as $i => $item) {
        $penggantianRows .= '<tr>'
          . '<td class="c" style="width:1.2cm;">' . ($i + 1) . '</td>'
          . '<td style="width:3.6cm;">' . pdf_escape((string)($item['nama_alat'] ?? '-')) . '</td>'
          . '<td style="width:2.4cm;">' . pdf_escape((string)($item['merk_type'] ?? '-')) . '</td>'
          . '<td class="c" style="width:1.2cm;">' . pdf_escape((string)($item['jumlah'] ?? '-')) . '</td>'
          . '<td class="c" style="width:2.4cm;">' . pdf_escape((string)($item['sn_baru'] ?? '-')) . '</td>'
          . '<td class="c" style="width:2.4cm;">' . pdf_escape((string)($item['sn_lama'] ?? '-')) . '</td>'
          . '<td class="c" style="width:2.2cm;">' . pdf_escape((string)($item['keterangan'] ?? '-')) . '</td>'
          . '</tr>';
      }
    } else {
      $penggantianRows = '<tr><td class="c" style="width:1.2cm;">-</td><td style="width:3.6cm;">-</td><td style="width:2.4cm;">-</td><td class="c" style="width:1.2cm;">-</td><td class="c" style="width:2.4cm;">-</td><td class="c" style="width:2.4cm;">-</td><td class="c" style="width:2.2cm;">-</td></tr>';
    }

    $penggantianHtml = <<<HTML
  <div class="section">
    <div class="section-title">CATATAN PENGGANTIAN ALAT</div>
      <table class="grid">
      <thead>
        <tr>
          <th style="width:1.2cm;">No</th>
          <th style="width:3.6cm;">Nama Alat</th>
          <th style="width:2.4cm;">Merk/Type</th>
          <th style="width:1.2cm;">Jml</th>
          <th style="width:2.4cm;" class="c">S/N Baru</th>
          <th style="width:2.4cm;" class="c">S/N Lama</th>
          <th style="width:2.2cm;" class="c">Ket</th>
        </tr>
      </thead>
      <tbody>
        {$penggantianRows}
      </tbody>
    </table>
  </div>
HTML;

    // Kop surat (header) is drawn via Dompdf canvas in api/laporan/pdf.php to match legacy ReportLab.

    $docs = [];
    foreach ($dokumentasi as $doc) {
      if (is_array($doc)) {
        $docs[] = $doc;
      }
    }

    $renderDocGrid = static function (array $docs, string $emptyText) use ($imagesEnabled): string {
      $cells = [];

      if (!$docs) {
        $cells[] = '<div class="photo photo-fallback"></div>';
        $cells[] = '<div class="photo photo-fallback"></div>';
      } else {
        foreach ($docs as $doc) {
          $caption = pdf_escape((string)($doc['caption'] ?? 'Dokumentasi'));
          $fileName = (string)($doc['file_name'] ?? '');
          $src = $imagesEnabled ? pdf_image_data_uri_from_upload($fileName) : null;
          $baseName = pdf_escape(basename(str_replace('\\', '/', $fileName)));

          if ($src) {
            $cells[] = '<div class="photo">'
              . '<div class="cap">' . $caption . '</div>'
              . '<img src="' . $src . '" />'
              . '</div>';
          } else {
            $fallback = $imagesEnabled ? '[Gambar Tidak Ditemukan]' : '[Gambar tidak ditampilkan (GD nonaktif)]';
            $cells[] = '<div class="photo photo-fallback">'
              . '<div class="cap">' . $caption . '</div>'
              . '<div class="muted">' . pdf_escape($fallback) . '</div>'
              . ($baseName !== '' ? '<div class="muted">' . $baseName . '</div>' : '')
              . '</div>';
          }
        }
      }

      if (count($cells) % 2 === 1) {
        $cells[] = '<div class="photo photo-fallback"></div>';
      }

      $rows = '';
      for ($i = 0; $i < count($cells); $i += 2) {
        $rows .= '<tr><td class="doc-cell">' . $cells[$i] . '</td><td class="doc-cell">' . $cells[$i + 1] . '</td></tr>';
      }

      return '<table class="doc-grid"><tbody>' . $rows . '</tbody></table>';
    };

    // Lampiran PDF (surat tugas/checklist) are appended via merge=1, not listed inside the PDF content.

    $docSection = '<div class="page-break"></div>'
      . '<div class="page">'
      . '<div style="height:1cm;"></div>'
      . '<div class="section-title center">DOKUMENTASI (LAMPIRKAN FOTO/VIDEO HASIL PEMELIHARAAN DAN PERBAIKAN)</div>'
      . $renderDocGrid($docs, 'Tidak ada dokumentasi.')
      . '</div>';

    $footerLeft = pdf_escape('Digenerasi pada ' . $generatedAt);
    $footerRight = pdf_escape('Nomor Surat: ' . (string)($laporan['nomor_surat'] ?? $detail['nomor_surat'] ?? '-'));

    $kopHtml = $kopDataUri ? '<div class="kop"><img src="' . $kopDataUri . '" alt="Kop BMKG" /></div>' : '';

    return <<<HTML
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <style>
    /* A4 margin now that kop is only on page 1 */
    @page { margin: 2.5cm 2cm 2.5cm 2cm; }
    html, body { margin: 0; padding: 0; }
    body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.35; }
    .kop { height: 3.5cm; width: 100%; margin: 0; }
    .kop img { width: 100%; height: 3.5cm; object-fit: cover; }
    .pad-after-kop { height: 0.6cm; }
    .page-pad { height: 0.5cm; }

    .page { width: 17cm; margin: 0 auto; }
    .title { text-align:center; margin-top: 0.25cm; }
    .title .t1 { font-weight: bold; font-size: 12pt; margin: 0.2cm 0 0.15cm 0; }
    .title .t2 { font-weight: bold; font-size: 12pt; margin: 0 0 0.55cm 0; }

    table { width: 100%; max-width: 100%; border-collapse: collapse; }
    .info { margin: 0 0 9pt 0; width: 100%; }
    .info td { border: none; padding: 1pt 0 4pt 0; vertical-align: top; font-size: 11pt; }
    .info .label { width: 3.1cm; }
    .info .sep { width: 0.35cm; text-align: center; }

    .section { margin-top: 0.55cm; }
    .section-title { font-weight: bold; margin: 0 0 4pt 0; text-align: left; }
    .section-title.left { text-align: left; }
    .section-title.center { text-align: center; font-weight: bold; font-size: 12pt; letter-spacing: 0.3pt; }

    /* Borderless listing tables */
    .plain { width: 100%; border-collapse: collapse; }
    .plain td, .plain th { border: none; padding: 2pt 0 2pt 0; vertical-align: top; }
    .plain .no { width: 1cm; padding-left: 0; }
    .plain .nama { width: 4.2cm; }
    .plain .nip { width: auto; }
    .keg-title { margin: 0; font-weight: 600; }
    .keg-desc { margin: 0; padding-left: 0.3cm; color: #333; }
    .muted { color: #555; font-style: italic; }
    .c { text-align: center; }

    /* Base grid with fixed layout for consistent column widths */
    .grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .grid th, .grid td { border: 0.9pt solid #000; padding: 5pt 5pt; font-size: 11pt; vertical-align: middle; }
    .grid th { text-align: center; font-weight: bold; }

    .page-break { page-break-after: always; }

    .doc-grid { width: 100%; border-collapse: collapse; margin-top: 10pt; table-layout: fixed; }
    .doc-grid td { border: 1pt solid #000; vertical-align: top; padding: 6pt 6pt 10pt 6pt; text-align: center; width: 50%; background: #fff; }
    .photo { background: #fff; }
    .photo img { max-width: 100%; height: 5.2cm; object-fit: contain; margin-top: 4pt; }
    .cap { font-size: 9pt; font-weight: bold; margin: 0 0 6pt 0; text-align: center; }
    .photo-fallback { min-height: 6cm; display: block; background: #fff; }
    .muted { color: #555; font-style: italic; }

  </style>
</head>
<body>
  {$kopHtml}
  <div class="pad-after-kop"></div>
  <div class="page">
  <div class="title">
    <div class="t1">LAPORAN PEMELIHARAAN MANDIRI WRS NG</div>
    <div class="t2">{$tempatUpper}</div>
  </div>

  <table class="info">
    <tr><td class="label">Surat Tugas</td><td class="sep">:</td><td>{$nomorSurat}</td></tr>
    <tr><td class="label">Kegiatan</td><td class="sep">:</td><td>{$kegiatan}</td></tr>
    <tr><td class="label">Tempat</td><td class="sep">:</td><td>{$tempat}</td></tr>
    <tr><td class="label">Waktu</td><td class="sep">:</td><td>{$waktu}</td></tr>
  </table>

  <div class="section">
    <div class="section-title">PETUGAS PELAKSANA</div>
    <table class="grid">
      <thead>
        <tr>
          <th style="width:1.2cm;">No</th>
          <th style="width:8cm;">Nama</th>
          <th>NIP</th>
        </tr>
      </thead>
      <tbody>{$petugasRows}</tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-title">KEGIATAN PEMELIHARAAN</div>
    <table class="grid">
      <thead>
        <tr>
          <th style="width:1.2cm;">No</th>
          <th style="width:9.5cm;">Uraian Kegiatan</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>{$kegiatanRows}</tbody>
    </table>
  </div>

  {$penggantianHtml}

  {$docSection}
  </div>
</body>
</html>
HTML;
}
