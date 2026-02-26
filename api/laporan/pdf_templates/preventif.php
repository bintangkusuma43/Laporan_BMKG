<?php

require_once __DIR__ . '/../_pdf_helpers.php';

/**
 * @param array $payload {laporan, detail, dokumentasi, lampiran}
 */
function pdf_template_preventif(array $payload, ?string $kopDataUri, bool $imagesEnabled = true): string
{
  $laporan = is_array($payload['laporan'] ?? null) ? $payload['laporan'] : [];
  $detail = is_array($payload['detail'] ?? null) ? $payload['detail'] : [];
  $dokumentasi = is_array($payload['dokumentasi'] ?? null) ? $payload['dokumentasi'] : [];

  // Kop surat is drawn via Dompdf canvas (see api/laporan/pdf.php) to match legacy ReportLab.

  $tipeRaw = (string)($detail['tipe_laporan'] ?? 'accelerograph');
  $tipe = $tipeRaw === 'intensitymeter' ? 'INTENSITYMETER' : 'ACCELEROGRAPH';

  // Caption -> data URI (case-insensitive)
  $captionToSrc = [];
  if ($imagesEnabled) {
    foreach ($dokumentasi as $doc) {
      if (!is_array($doc)) {
        continue;
      }
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

  $tanggalPelaksanaan = pdf_format_date_id((string)($laporan['tanggal_laporan'] ?? ''), true);

  // === HALAMAN 1: Judul + Info Umum (match GenerateLaporanPreventif) ===
  $nomorSpt = pdf_escape((string)($detail['nomor_spt'] ?? $laporan['nomor_surat'] ?? '-'));
  $kodeSite = pdf_escape((string)($detail['kode_site'] ?? '-'));
  $namaSite = pdf_escape((string)($detail['nama_site'] ?? '-'));

  $petugas = is_array($detail['petugas_pelaksana'] ?? null) ? $detail['petugas_pelaksana'] : [];
  $petugasHtml = '';
  if ($petugas) {
    foreach ($petugas as $i => $p) {
      if (!is_array($p)) {
        continue;
      }
      $nama = pdf_escape((string)($p['nama'] ?? '-'));
      $nip = trim((string)($p['nip'] ?? ''));
      $nipText = $nip !== '' ? ('NIP ' . pdf_escape($nip)) : '';
      $petugasHtml .= '<div>' . ($i + 1) . '. ' . $nama . ($nipText ? ' ' . $nipText : '') . '</div>';
    }
  } else {
    $petugasHtml = '<div>-</div>';
  }

  $kerusakan = nl2br(pdf_escape((string)($detail['deskripsi_kerusakan'] ?? '-')));
  $rekomendasi = nl2br(pdf_escape((string)($detail['rekomendasi'] ?? '-')));
  $status = pdf_escape((string)($detail['status_alat'] ?? '-'));

  // === CHECKLIST (HALAMAN 2 & 4): 3 kolom dengan 2 baris per item (Foto + Kondisi) ===
  $kondisiList = is_array($detail['kondisi_peralatan'] ?? null) ? $detail['kondisi_peralatan'] : [];

  $categoryOrder = ['WRSNG', 'Sistem Kelistrikan', 'Sistem Komunikasi', 'Peralatan Seismik'];
  $grouped = [];
  foreach ($kondisiList as $row) {
    if (!is_array($row)) {
      continue;
    }
    $cat = trim((string)($row['category'] ?? ''));
    if ($cat === '') {
      $cat = 'Lainnya';
    }
    $grouped[$cat][] = $row;
  }
  // Append any categories not in the master order.
  foreach (array_keys($grouped) as $cat) {
    if (!in_array($cat, $categoryOrder, true)) {
      $categoryOrder[] = $cat;
    }
  }

  $buildChecklist = function (string $mode) use ($categoryOrder, $grouped, $captionToSrc, $imagesEnabled): string {
    $rows = '';
    foreach ($categoryOrder as $cat) {
      $items = $grouped[$cat] ?? [];
      if (!$items) {
        continue;
      }
      $rows .= '<tr class="subhead"><td colspan="3"><b>' . pdf_escape($cat) . '</b></td></tr>';

      foreach ($items as $item) {
        if (!is_array($item)) {
          continue;
        }
        $label = trim((string)($item['label'] ?? ''));
        $labelShown = $label !== '' ? $label : '-';
        $labelEsc = pdf_escape($labelShown);

        $kondKey = $mode === 'sebelum' ? 'kondisi_sebelum' : 'kondisi_sesudah';
        $kondisi = pdf_escape((string)($item[$kondKey] ?? '-'));

        $capKey = mb_strtolower(($label !== '' ? $label : 'Checklist') . ' - ' . $mode, 'UTF-8');
        $imgHtml = '-';
        if ($imagesEnabled && isset($captionToSrc[$capKey])) {
          $imgHtml = '<img class="check-img" src="' . $captionToSrc[$capKey] . '" />';
        }

        $rows .= '<tr>'
          . '<td class="desc" rowspan="2">' . $labelEsc . '</td>'
          . '<td class="label">Foto</td>'
          . '<td class="value c">' . $imgHtml . '</td>'
          . '</tr>'
          . '<tr>'
          . '<td class="label">Kondisi</td>'
          . '<td class="value">' . $kondisi . '</td>'
          . '</tr>';
      }
    }

    if ($rows === '') {
      $rows = '<tr><td colspan="3" class="c">-</td></tr>';
    }

    return '<table class="check">'
      . '<colgroup><col style="width:6cm" /><col style="width:4cm" /><col /></colgroup>'
      . '<thead><tr><th>DESKRIPSI</th><th colspan="2">KETERANGAN</th></tr></thead>'
      . '<tbody>' . $rows . '</tbody>'
      . '</table>';
  };

  $checklistSebelumHtml = $buildChecklist('sebelum');
  $checklistSesudahHtml = $buildChecklist('sesudah');

  $kopHtml = $kopDataUri ? '<div class="kop"><img src="' . $kopDataUri . '" alt="Kop BMKG" /></div>' : '';

  // === HALAMAN 3: Pelaksanaan (Bab B) ===
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

  $param = is_array($detail['pencatatan_parameter'] ?? null) ? $detail['pencatatan_parameter'] : [];
  $pembersihan = is_array($detail['pembersihan'] ?? null) ? $detail['pembersihan'] : [];
  $listrik = is_array($detail['pengecekan_listrik'] ?? null) ? $detail['pengecekan_listrik'] : [];
  $kom = is_array($detail['pengecekan_komunikasi'] ?? null) ? $detail['pengecekan_komunikasi'] : [];
  $seismik = is_array($detail['pengecekan_peralatan_seismik'] ?? null) ? $detail['pengecekan_peralatan_seismik'] : [];

  $pelaksanaanRows = '';
  $addSub = function (string $title) use (&$pelaksanaanRows): void {
    $pelaksanaanRows .= '<tr class="subhead"><td colspan="2"><b>' . pdf_escape($title) . '</b></td></tr>';
  };
  $addRow = function (string $label, string $value) use (&$pelaksanaanRows): void {
    $pelaksanaanRows .= '<tr><td class="pel-label">' . pdf_escape($label) . '</td><td>' . $value . '</td></tr>';
  };

  $addSub('Pembersihan');
  $addRow('Pembersihan Lingkungan', $fmt($pembersihan['lingkungan'] ?? '-'));
  $addRow('Pembersihan Peralatan', $fmt($pembersihan['peralatan'] ?? '-'));

  $addSub('Pengecekan Kelistrikan');
  $addRow('Sisa Token', $fmt($param['sisa_token'] ?? '-', 'kWH'));
  $addRow('Tegangan Sumber', $fmt($param['tegangan_sumber'] ?? '-', 'Volt'));
  $addRow('Tegangan Output Stabilizer', $fmt($param['tegangan_output_stabilizer'] ?? '-', 'Volt'));
  $addRow('Tegangan Output UPS', $fmt($param['tegangan_output_ups'] ?? '-', 'Volt'));
  $addRow('Tegangan Baterai (Avg)', $fmt($param['tegangan_baterai'] ?? '-', 'Volt'));
  $addRow('Jumlah Baterai', $fmt($param['jumlah_baterai'] ?? '-'));
  $addRow('Ketahanan UPS', $fmt($listrik['ketahanan_ups'] ?? '-'));

  $addSub('Pengecekan Sistem Komunikasi');
  $addRow('Ping IP Modem/Gateway', $fmt($kom['ping_modem'] ?? '-'));
  $addRow('Ping IP Digitizer', $fmt($kom['ping_digitizer'] ?? '-'));
  $addRow('Ping PC Display', $fmt($kom['ping_display'] ?? '-'));
  $addRow('Ping IP Server', $fmt($kom['ping_server'] ?? '-'));

  $addSub('Pengecekan Peralatan Seismik');
  $addRow('Leveling Sensor', $fmt($seismik['leveling_sensor'] ?? '-'));
  $addRow('Pengecekan SLMON Simora', $fmt($seismik['slmon_simora'] ?? '-'));

  $pelaksanaanHtml = '<table class="pel">'
    . '<colgroup><col style="width:8cm" /><col /></colgroup>'
    . '<thead><tr><th>DESKRIPSI</th><th>KETERANGAN</th></tr></thead>'
    . '<tbody>' . $pelaksanaanRows . '</tbody>'
    . '</table>';

  // === HALAMAN 5: Penggantian Alat (Bab D) ===
  $penggantian = is_array($detail['catatan_penggantian'] ?? null) ? $detail['catatan_penggantian'] : [];
  $penggantianSection = '<div class="page-break"></div><div class="page-pad-later"></div>';
  if ($penggantian) {
    $rows = '';
    foreach ($penggantian as $i => $item) {
      if (!is_array($item)) {
        continue;
      }
      $rows .= '<tr>'
        . '<td class="c">' . ($i + 1) . '</td>'
        . '<td>' . pdf_escape((string)($item['nama_alat'] ?? '')) . '</td>'
        . '<td>' . pdf_escape((string)($item['merk_type'] ?? '-')) . '</td>'
        . '<td class="c">' . pdf_escape((string)($item['jumlah'] ?? '')) . '</td>'
        . '<td>' . pdf_escape((string)($item['sn_baru'] ?? '')) . '</td>'
        . '<td>' . pdf_escape((string)($item['sn_lama'] ?? '')) . '</td>'
        . '<td>' . pdf_escape((string)($item['keterangan'] ?? '')) . '</td>'
        . '</tr>';
    }

    $penggantianSection .= '<div class="section-title">D. CATATAN PENGGANTIAN ALAT (SUKU CADANG)</div>'
      . '<div class="sp"></div>'
      . '<table class="grid">'
      . '<thead><tr>'
      . '<th style="width:0.9cm">No</th>'
      . '<th style="width:3.6cm">Nama Alat</th>'
      . '<th style="width:2.3cm">Merk/Type</th>'
      . '<th style="width:1.0cm">Jml</th>'
      . '<th style="width:2.4cm">S/N Baru</th>'
      . '<th style="width:2.4cm">S/N Lama</th>'
      . '<th style="width:2.6cm">Keterangan</th>'
      . '</tr></thead>'
      . '<tbody>' . ($rows !== '' ? $rows : '<tr><td colspan="7" class="c">-</td></tr>') . '</tbody>'
      . '</table>';
  }

    return <<<HTML
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <style>
    @page { margin: 2.5cm 2cm 2cm 2cm; }
    html, body { margin: 0; padding: 0; }
    body { font-family: "Times-Roman", Times, serif; font-size: 11pt; }
    .page { width: 17cm; margin: 0 auto; }
      .kop { height: 3.5cm; width: 100%; margin: 0; }
      .kop img { width: 100%; height: 3.5cm; object-fit: cover; }
    .pad-after-kop { height: 0.6cm; }
    .page-pad-first { height: 0.5cm; }
    .page-pad-later { height: 1.0cm; }
    h1,h2 { margin: 0; }
    .title { text-align:center; margin-top: 0.2cm; }
    .title .t1 { font-weight: bold; font-size: 12pt; margin: 0 0 4pt 0; }
    .title .t2 { font-weight: bold; font-size: 12pt; margin: 0 0 0.6cm 0; }
    table { width: 100%; max-width: 100%; border-collapse: collapse; }
    td, th { border: 0.5pt solid #000; padding: 4pt; vertical-align: top; }
    th { background: #e6e6e6; text-align: center; }
    .c { text-align: center; }
    .section-title { font-weight: bold; font-size: 12pt; text-align: left; margin: 0.2cm 0; }
    .sp { height: 0.3cm; }

    .kv td { border: 0.5pt solid #000; }
    .kv .k { width: 4cm; }
    .kv .sep { width: 0.4cm; text-align: center; }

    .check th { background: #e6e6e6; }
    .check .subhead td { background: #e6e6e6; }
    .check .desc { vertical-align: top; }
    .check .label { vertical-align: middle; }
    .check-img { max-width: 6.5cm; max-height: 4.875cm; object-fit: contain; }

    .pel th { background: #e6e6e6; }
    .pel .subhead td { background: #e6e6e6; }
    .pel td { vertical-align: middle; padding: 4pt 6pt; }
    .pel .pel-label { font-size: 10pt; }

    .grid th { background: #e6e6e6; }
    .page-break { page-break-after: always; }
  </style>
</head>
<body style="margin-bottom:2.5cm !important;">
<style>
  table.kv { border-collapse: separate; border-spacing: 0; width: 100%; }
  table.kv td, table.kv th { border: 1px solid #888; padding: 6px 8px; }
  table.kv tr:first-child th, table.kv tr:first-child td { border-top: 1px solid #888; }
  table.kv tr:last-child th, table.kv tr:last-child td { border-bottom: 1px solid #888; }
  table.kv th { background: #eee; font-weight: bold; }
</style>
  {$kopHtml}
  <div class="pad-after-kop"></div>
  <div class="page">
  <div class="title">
    <div class="t1">LAPORAN PREVENTIF MAINTENANCE</div>
    <div class="t2">{$tipe}</div>
  </div>

  <table class="kv">
    <tr><td class="k">No SPT</td><td class="sep">:</td><td>{$nomorSpt}</td></tr>
    <tr><td class="k">Tanggal Pelaksanaan</td><td class="sep">:</td><td>{$tanggalPelaksanaan}</td></tr>
    <tr><td class="k">Kode Site</td><td class="sep">:</td><td>{$kodeSite}</td></tr>
    <tr><td class="k">Nama Site</td><td class="sep">:</td><td>{$namaSite}</td></tr>
    <tr><td class="k">Tim Pemeliharaan</td><td class="sep">:</td><td>{$petugasHtml}</td></tr>
    <tr><td class="k">Kerusakan</td><td class="sep">:</td><td>{$kerusakan}</td></tr>
    <tr><td class="k">Rekomendasi</td><td class="sep">:</td><td>{$rekomendasi}</td></tr>
    <tr><td class="k">Status</td><td class="sep">:</td><td>{$status}</td></tr>
  </table>

  <div class="page-break"></div>
  <div class="page-pad-first"></div>
  <div class="section-title">A. SEBELUM PREVENTIF MAINTENANCE</div>
  <div class="sp"></div>
  {$checklistSebelumHtml}

  <div class="page-break"></div>
  <div class="page-pad-later"></div>
  <div class="section-title">B. PREVENTIF MAINTENANCE</div>
  <div class="sp"></div>
  {$pelaksanaanHtml}

  <div class="page-break"></div>
  <div class="page-pad-later"></div>
  <div class="section-title">C. SESUDAH PREVENTIF MAINTENANCE</div>
  <div class="sp"></div>
  {$checklistSesudahHtml}

  {$penggantianSection}
  </div>
</body>
</html>
HTML;
}
