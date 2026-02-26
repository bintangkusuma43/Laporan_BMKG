<?php
if (!function_exists('nav_active')) {
  function nav_active(string $target): string {
    $currentUri = $_SERVER['REQUEST_URI'] ?? '';

    $targetParts = parse_url($target);
    $currentParts = parse_url($currentUri);

    $targetPath = rtrim($targetParts['path'] ?? '', '/');
    $currentPath = rtrim($currentParts['path'] ?? '', '/');
    if ($targetPath !== $currentPath) {
      return '';
    }

    $targetQuery = $targetParts['query'] ?? '';
    $currentQuery = $currentParts['query'] ?? '';

    if ($targetQuery !== '') {
      return $targetQuery === $currentQuery ? 'is-active' : '';
    }

    return $currentQuery === '' ? 'is-active' : '';
  }
}

if (!function_exists('nav_active_contains')) {
    function nav_active_contains(string $needle): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, $needle) !== false ? 'is-active' : '';
    }
}

$currentUser = $currentUser ?? ($user ?? (function_exists('current_user') ? current_user() : null));

$navSections = [
    [
        'title' => 'Pelaporan',
        'items' => [
            ['href' => '/laporan_bmkg/dashboard.php', 'label' => 'Dashboard', 'icon' => 'home', 'active' => nav_active('/laporan_bmkg/dashboard.php')],
            ['href' => '/laporan_bmkg/laporan_form_wrs.php', 'label' => 'Laporan WRS NG', 'icon' => 'wrs', 'active' => nav_active('/laporan_bmkg/laporan_form_wrs.php')],
            ['href' => '/laporan_bmkg/laporan_form_accelerograph.php', 'label' => 'Laporan Accelerograph / Intensitymeter', 'icon' => 'accelerograph', 'active' => nav_active('/laporan_bmkg/laporan_form_accelerograph.php')],
            ['href' => '/laporan_bmkg/laporan_form_seismograph.php', 'label' => 'Laporan Seismograph', 'icon' => 'seismo', 'active' => nav_active('/laporan_bmkg/laporan_form_seismograph.php')],
        ],
    ],
    [
        'title' => 'Riwayat',
        'items' => [
          ['href' => '/laporan_bmkg/laporan_list.php', 'label' => 'Riwayat Laporan', 'icon' => 'history', 'active' => nav_active('/laporan_bmkg/laporan_list.php')],
          ['href' => '/laporan_bmkg/laporan_suku_cadang.php', 'label' => 'Riwayat Suku Cadang', 'icon' => 'parts', 'active' => nav_active('/laporan_bmkg/laporan_suku_cadang.php')],
        ],
    ],
    [
        'title' => 'Media',
        'items' => [
          ['href' => '/laporan_bmkg/galeri.php', 'label' => 'Galeri Foto', 'icon' => 'gallery', 'active' => nav_active('/laporan_bmkg/galeri.php')],
        ],
    ],
];

    if (($currentUser['role'] ?? null) === 'admin') {
      $navSections[] = [
        'title' => 'Pengaturan',
        'items' => [
          ['href' => '/laporan_bmkg/kelola_akun.php', 'label' => 'Kelola Akun', 'icon' => 'users', 'active' => nav_active('/laporan_bmkg/kelola_akun.php')],
        ],
      ];
    }
?>
<nav class="nav-sections" aria-label="Navigasi">
  <?php foreach ($navSections as $section): ?>
    <div class="nav-section">
      <div class="nav-section-title"><?= htmlspecialchars($section['title']); ?></div>
      <?php foreach ($section['items'] as $item): ?>
        <a class="nav-link <?= $item['active']; ?>" href="<?= htmlspecialchars($item['href']); ?>">
          <span class="nav-icon" aria-hidden="true">
            <?php
              $icons = [
                'home' => 'M3 10.5 12 3l9 7.5v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z',
                'wrs' => 'M12 13a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm6.5-2a6.5 6.5 0 0 0-13 0m10 0a3.5 3.5 0 0 0-7 0',
                'accelerograph' => 'M3 13h3l2-6 4 12 2-6h5',
                'seismo' => 'M3 15h4l2-5 2 6 2-5 2 4h4',
                'history' => 'M12 8v5l3 2m-3-9a7 7 0 1 0 7 7',
                'parts' => 'M4 17l4.5-4.5m1 1L7 18l4.5-1.5 6-6a3 3 0 1 0-4.2-4.2l-6 6Z',
                'gallery' => 'M4 5h16v14H4z M6 9a2 2 0 1 0 4 0 2 2 0 0 0-4 0zm-1 8 4-4 3 3 2-2 3 3',
                'users' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 8a7 7 0 0 1 14 0',
              ];
              $path = $icons[$item['icon']] ?? $icons['home'];
            ?>
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="<?= $path; ?>" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </span>
          <span class="nav-label"><?= htmlspecialchars($item['label']); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</nav>
