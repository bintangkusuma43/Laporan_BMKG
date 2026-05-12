<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) {
    header('Location: /laporan_bmkg/login.php');
    exit;
}

if (($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Akses ditolak. Hanya admin yang dapat mengelola akun.';
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Kelola Akun - Laporan BMKG</title>
  <link rel="stylesheet" href="/laporan_bmkg/assets/css/styles.css" />
  <style>
    /* ── Topbar gradient ── */
    .app-shell .app-topbar {
      padding: 18px 24px !important;
      background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6) !important;
      box-shadow: 0 4px 24px rgba(37,99,235,0.22) !important;
      display: flex !important;
      align-items: center !important;
      gap: 14px !important;
    }
    .app-shell .app-topbar .page-title { display: flex; flex-direction: column; gap: 3px; }
    .app-shell .app-topbar .page-title h1 {
      font-size: 22px !important;
      font-weight: 800 !important;
      color: #ffffff !important;
      letter-spacing: -0.3px;
      margin: 0 !important;
    }
    .app-shell .app-topbar .page-title .subtitle {
      color: #bfdbfe !important;
      font-size: 13px !important;
      margin: 0 !important;
    }
    .app-shell .app-topbar .user-box {
      margin-left: auto;
      min-width: 0 !important;
      background: rgba(255,255,255,0.13) !important;
      border: 1px solid rgba(255,255,255,0.26) !important;
      box-shadow: none !important;
      padding: 7px 14px !important;
      border-radius: 999px !important;
    }
    .app-shell .app-topbar .user-box > div[data-user-info] { color: #fff !important; font-size: 13px; font-weight: 700; }
    .app-shell .app-topbar .user-box .user-role { color: #bfdbfe !important; font-size: 11px !important; }

    /* ── Layout ── */
    .app-shell .page-body { display: flex; flex-direction: column; gap: 18px; }

    /* ── Cards ── */
    .card {
      background: #ffffff;
      border: 1px solid #e1e7f0;
      border-radius: 16px;
      padding: 22px 24px;
      box-shadow: 0 4px 20px rgba(15, 23, 42, 0.07);
    }
    .card-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
      padding-bottom: 14px;
      border-bottom: 1px solid #f1f5f9;
    }
    .card-icon {
      width: 36px; height: 36px;
      border-radius: 10px;
      background: linear-gradient(135deg, #1e40af, #2563eb);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .card-icon svg { width: 18px; height: 18px; color: #fff; }
    .card h2 { margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; letter-spacing: -0.2px; }
    .card .muted { color: #64748b; font-size: 13px; margin-top: 2px; }

    /* ── Form grid ── */
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
    .form-group-inner { display: flex; flex-direction: column; gap: 6px; }
    .form-group-inner label {
      font-size: 11.5px;
      font-weight: 700;
      color: #374151;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .form-group-inner input,
    .form-group-inner select {
      padding: 10px 12px;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      background: #f8fafc;
      font-size: 13px;
      font-family: inherit;
      color: #0f172a;
      transition: border-color 150ms, box-shadow 150ms;
      outline: none;
    }
    .form-group-inner input:focus,
    .form-group-inner select:focus {
      border-color: #2563eb;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    /* ── Actions ── */
    .actions { display: flex; align-items: center; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
    .btn { font-family: inherit; font-size: 13px; font-weight: 700; border-radius: 10px; padding: 10px 18px; cursor: pointer; border: none; transition: transform 120ms ease, box-shadow 120ms ease; display: inline-flex; align-items: center; gap: 6px; }
    .btn:hover:not(:disabled) { transform: translateY(-1px); }
    .btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .btn.primary { background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6); color: #ffffff; box-shadow: 0 6px 18px rgba(37,99,235,0.28); }
    .btn.primary:hover:not(:disabled) { box-shadow: 0 10px 26px rgba(37,99,235,0.35); }
    .btn.secondary { background: #f1f5f9; color: #374151; border: 1px solid #e2e8f0; }
    .btn.secondary:hover:not(:disabled) { background: #e2e8f0; }
    .btn.danger { background: #fff0f0; color: #b91c1c; border: 1px solid #fecaca; padding: 7px 13px; font-size: 12px; }
    .btn.danger:hover:not(:disabled) { background: #fee2e2; }

    /* ── Table ── */
    .table-wrapper { overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0; }
    table { width: 100%; border-collapse: collapse; min-width: 540px; }
    thead th {
      background: linear-gradient(135deg, #1e40af, #2563eb);
      color: #dbeafe;
      text-transform: uppercase;
      font-size: 10px;
      letter-spacing: 0.7px;
      font-weight: 700;
      padding: 11px 14px;
      text-align: left;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      white-space: nowrap;
    }
    tbody td { padding: 11px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #eff6ff; }
    tbody tr:nth-child(even) td { background: #f8fafc; }
    tbody tr:nth-child(even):hover td { background: #eff6ff; }

    /* ── Badges ── */
    .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-weight: 700; font-size: 11px; }
    .badge.admin { background: #e0f2fe; color: #075985; }
    .badge.petugas { background: #f1f5f9; color: #334155; }

    .table-actions { display: flex; gap: 6px; flex-wrap: wrap; }

    .empty { padding: 32px; text-align: center; color: #94a3b8; font-size: 13px; }

    /* ── Toast ── */
    .toast {
      position: fixed; bottom: 22px; right: 22px;
      background: #0f172a; color: #ffffff;
      padding: 13px 18px; border-radius: 14px;
      box-shadow: 0 12px 32px rgba(15, 23, 42, 0.25);
      display: none; font-size: 13px; font-weight: 600;
      z-index: 999;
    }
    .toast.show { display: block; animation: slideUp 0.25s ease; }
    @keyframes slideUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

    @media (max-width: 640px) {
      .card { padding: 16px; }
      tbody td, thead th { padding: 10px 10px; font-size: 12px; }
    }
  </style>
</head>
<body class="app-shell">
  <div class="mobile-topbar">
    <button id="mobileMenuBtn" class="hamburger-btn" type="button" aria-label="Buka menu">☰</button>
    <div class="mobile-title">SIMPels BMKG</div>
  </div>
  <div id="sidebarOverlay" class="sidebar-overlay"></div>
  <div class="app-layout">
    <aside class="app-sidebar">
      <div class="sidebar-brand">
        <div class="brand-mark"></div>
        <div>
          <div class="brand-title">Laporan BMKG</div>
          <div class="brand-sub">Pengaturan</div>
        </div>
      </div>
      <nav class="sidebar-nav" data-nav data-static-nav="true"><?php include __DIR__ . '/includes/nav.php'; ?></nav>
      <div class="sidebar-footer">
        <button class="sidebar-logout" data-logout>Keluar</button>
      </div>
    </aside>
    <div class="app-shell-main">
      <header class="app-topbar">
        <div class="page-title">
          <h1>Kelola Akun</h1>
          <div class="subtitle">Buat, lihat, dan hapus akun pengguna</div>
        </div>
        <div class="user-box">
          <div data-user-info><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
          <div class="user-role">Admin</div>
        </div>
      </header>

      <main class="page-body">
        <section class="card">
          <div class="card-header">
            <div class="card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            </div>
            <div>
              <h2>Buat Akun Baru</h2>
              <div class="muted">Username minimal 4 karakter, password minimal 6 karakter.</div>
            </div>
          </div>
          <form id="create-form">
            <div class="form-grid">
              <div class="form-group-inner">
                <label>Nama Lengkap</label>
                <input type="text" name="full_name" required placeholder="Nama lengkap" />
              </div>
              <div class="form-group-inner">
                <label>Username</label>
                <input type="text" name="username" minlength="4" pattern="[A-Za-z0-9_.-]+" required placeholder="min. 4 karakter" />
              </div>
              <div class="form-group-inner">
                <label>Password</label>
                <input type="password" name="password" minlength="6" required placeholder="min. 6 karakter" />
              </div>
              <div class="form-group-inner">
                <label>Role</label>
                <select name="role">
                  <option value="admin">Admin</option>
                  <option value="petugas" selected>Petugas</option>
                </select>
              </div>
            </div>
            <div class="actions">
              <button type="submit" class="btn primary" id="submit-btn">Simpan Akun</button>
              <button type="reset" class="btn secondary">Reset</button>
            </div>
          </form>
        </section>

        <section class="card">
          <div class="card-header">
            <div class="card-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
              <h2>Daftar Akun</h2>
              <div class="muted">Semua akun yang terdaftar di sistem.</div>
            </div>
          </div>
          <div class="table-responsive">
            <table id="users-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Dibuat</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="users-body">
                <tr><td colspan="5" class="empty">Memuat data...</td></tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script>
    const baseUrl = '<?php echo BASE_URL; ?>';
    const createForm = document.getElementById('create-form');
    const usersBody = document.getElementById('users-body');
    const submitBtn = document.getElementById('submit-btn');
    const toastEl = document.getElementById('toast');
    const logoutBtn = document.querySelector('[data-logout]');

    function showToast(message, variant = 'default') {
      toastEl.textContent = message;
      toastEl.style.background = variant === 'error' ? '#b91c1c' : '#0f172a';
      toastEl.classList.add('show');
      setTimeout(() => toastEl.classList.remove('show'), 2600);
    }

    function formatDate(value) {
      if (!value) return '-';
      const date = new Date(value);
      return isNaN(date.getTime()) ? value : date.toLocaleString('id-ID');
    }

    if (logoutBtn) {
      logoutBtn.addEventListener('click', async (event) => {
        event.preventDefault();
        try {
          await fetch(`${baseUrl}/api/auth/logout.php`, { method: 'POST', credentials: 'same-origin' });
        } catch (err) {
          // ignore network error; still redirect to login
        }
        window.location.href = `${baseUrl}/login.php`;
      });
    }

    async function loadUsers() {
      usersBody.innerHTML = '<tr><td colspan="5" class="empty">Memuat data...</td></tr>';
      try {
        const res = await fetch(`${baseUrl}/api/users/list.php`, { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Gagal memuat data');
        const rows = data.data;
        if (!rows || rows.length === 0) {
          usersBody.innerHTML = '<tr><td colspan="5" class="empty">Belum ada akun.</td></tr>';
          return;
        }
        usersBody.innerHTML = rows.map((row) => {
          const roleBadge = `<span class="badge ${row.role}">${row.role}</span>`;
          return `
            <tr>
              <td>${row.full_name ?? '-'}</td>
              <td>${row.username}</td>
              <td>${roleBadge}</td>
              <td>${formatDate(row.created_at)}</td>
              <td>
                <div class="table-actions">
                  <button class="btn danger" data-delete="${row.id}">Hapus</button>
                </div>
              </td>
            </tr>`;
        }).join('');
      } catch (err) {
        usersBody.innerHTML = `<tr><td colspan="5" class="empty">${err.message}</td></tr>`;
      }
    }

    createForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      submitBtn.disabled = true;
      const formData = new FormData(createForm);
      const payload = {
        full_name: formData.get('full_name')?.trim(),
        username: formData.get('username')?.trim(),
        password: formData.get('password') || '',
        role: formData.get('role') || 'petugas',
      };
      if (!payload.full_name || !payload.username || !payload.password) {
        showToast('Nama lengkap, username, dan password wajib diisi.', 'error');
        submitBtn.disabled = false;
        return;
      }
      try {
        const res = await fetch(`${baseUrl}/api/users/create.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Gagal membuat akun');
        showToast('Akun berhasil dibuat');
        createForm.reset();
        await loadUsers();
      } catch (err) {
        showToast(err.message, 'error');
      } finally {
        submitBtn.disabled = false;
      }
    });

    document.getElementById('users-table').addEventListener('click', async (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;
      const deleteId = target.getAttribute('data-delete');
      if (!deleteId) return;
      const confirmed = window.confirm('Hapus akun ini?');
      if (!confirmed) return;
      target.disabled = true;
      try {
        const res = await fetch(`${baseUrl}/api/users/delete.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ id: Number(deleteId) }),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Gagal menghapus akun');
        showToast('Akun berhasil dihapus');
        await loadUsers();
      } catch (err) {
        showToast(err.message, 'error');
      } finally {
        target.disabled = false;
      }
    });

    loadUsers();
  </script>
  <script src="/laporan_bmkg/assets/js/main.js"></script>
</body>
</html>
