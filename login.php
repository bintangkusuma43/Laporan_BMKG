<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SIMPels — Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --grad-start : #1e40af;
      --grad-mid   : #2563eb;
      --grad-end   : #3b82f6;
      --primary    : #2563eb;
      --primary-dk : #1d4ed8;
      --text       : #0f172a;
      --muted      : #64748b;
      --border     : #e2e8f0;
      --bg         : #f8fafc;
      --card       : #ffffff;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: stretch;
      font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
      color: var(--text);
      background: var(--bg);
    }

    /* ── Left panel ── */
    .panel-left {
      flex: 0 0 46%;
      background: linear-gradient(145deg, var(--grad-start) 0%, var(--grad-mid) 52%, var(--grad-end) 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 48px 44px;
      position: relative;
      overflow: hidden;
    }

    /* decorative blobs */
    .panel-left::before,
    .panel-left::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
    }
    .panel-left::before {
      width: 420px; height: 420px;
      background: rgba(255,255,255,0.05);
      top: -120px; right: -100px;
    }
    .panel-left::after {
      width: 280px; height: 280px;
      background: rgba(255,255,255,0.07);
      bottom: -80px; left: -60px;
    }
    .blob-mid {
      position: absolute;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(255,255,255,0.04);
      bottom: 120px; right: -50px;
      pointer-events: none;
    }

    .panel-left-inner {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
      text-align: center;
    }

    .logo-wrap {
      width: 90px; height: 90px;
      background: rgba(255,255,255,0.15);
      border: 2px solid rgba(255,255,255,0.3);
      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(6px);
      box-shadow: 0 16px 40px rgba(0,0,0,0.2);
    }
    .logo-wrap img {
      width: 60px; height: 60px;
      object-fit: contain;
    }

    .panel-app-name {
      font-size: 38px;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -1px;
      line-height: 1;
    }
    .panel-app-badge {
      display: inline-block;
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.3);
      color: #e0f2ff;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      padding: 5px 14px;
      border-radius: 999px;
    }
    .panel-tagline {
      color: rgba(255,255,255,0.78);
      font-size: 14px;
      font-weight: 500;
      line-height: 1.65;
      max-width: 280px;
    }

    /* divider dots */
    .dot-row {
      display: flex;
      gap: 8px;
      margin-top: 8px;
    }
    .dot-row span {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: rgba(255,255,255,0.35);
    }
    .dot-row span:nth-child(2) {
      background: rgba(255,255,255,0.65);
      width: 18px;
      border-radius: 3px;
    }

    /* stat pills on left panel */
    .stat-row {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 4px;
    }
    .stat-pill {
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 12px;
      padding: 8px 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 2px;
    }
    .stat-pill strong {
      color: #ffffff;
      font-size: 16px;
      font-weight: 800;
    }
    .stat-pill small {
      color: rgba(255,255,255,0.65);
      font-size: 10px;
      font-weight: 600;
      letter-spacing: 0.4px;
      text-transform: uppercase;
    }

    /* ── Right panel ── */
    .panel-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 32px;
      background: var(--card);
    }

    .form-box {
      width: 100%;
      max-width: 400px;
    }

    .form-heading {
      margin-bottom: 28px;
    }
    .form-heading h2 {
      font-size: 26px;
      font-weight: 800;
      color: var(--text);
      letter-spacing: -0.5px;
    }
    .form-heading p {
      margin-top: 6px;
      font-size: 14px;
      color: var(--muted);
      font-weight: 500;
    }

    .form-group {
      margin-bottom: 16px;
    }
    .form-group label {
      display: block;
      font-size: 12.5px;
      font-weight: 700;
      color: #374151;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 7px;
    }
    .input-wrap {
      position: relative;
    }
    .input-wrap svg {
      position: absolute;
      left: 13px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px; height: 16px;
      color: #94a3b8;
      pointer-events: none;
      flex-shrink: 0;
    }
    .input-wrap input {
      width: 100%;
      padding: 12px 16px 12px 40px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 500;
      color: var(--text);
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      outline: none;
      transition: border-color 180ms, box-shadow 180ms, background 180ms;
    }

    .input-wrap input::placeholder {
      color: #c0cce0;
      font-weight: 400;
    }
    .input-wrap input:focus {
      border-color: var(--primary);
      background: #ffffff;
      box-shadow: 0 0 0 3.5px rgba(37,99,235,0.13);
    }


    .btn-login {
      margin-top: 8px;
      width: 100%;
      padding: 13px;
      font-family: inherit;
      font-size: 15px;
      font-weight: 700;
      color: #fff;
      background: linear-gradient(135deg, var(--grad-start), var(--grad-mid) 55%, var(--grad-end));
      border: none;
      border-radius: 12px;
      cursor: pointer;
      box-shadow: 0 8px 24px rgba(37,99,235,0.32);
      transition: transform 140ms ease, box-shadow 140ms ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      letter-spacing: 0.2px;
    }
    .btn-login:hover:not(:disabled) {
      transform: translateY(-1px);
      box-shadow: 0 14px 32px rgba(37,99,235,0.38);
    }
    .btn-login:active:not(:disabled) { transform: translateY(0); }
    .btn-login:disabled { opacity: 0.65; cursor: not-allowed; box-shadow: none; }

    /* spinner */
    .spinner {
      display: none;
      width: 18px; height: 18px;
      border: 2.5px solid rgba(255,255,255,0.35);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 0.65s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loading .spinner { display: block; }
    .loading .btn-text { display: none; }

    /* status */
    .status-msg {
      margin-top: 14px;
      min-height: 20px;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      border-radius: 10px;
      padding: 0;
      transition: all 200ms;
    }
    .status-msg.error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
      padding: 10px 14px;
    }
    .status-msg.success {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
      padding: 10px 14px;
    }

    .form-footer {
      margin-top: 22px;
      text-align: center;
      font-size: 12.5px;
      color: var(--muted);
      font-weight: 500;
    }
    .form-footer a { color: var(--primary); text-decoration: none; font-weight: 600; }

    /* ── Responsive: stack vertically on small screens ── */
    @media (max-width: 780px) {
      body { flex-direction: column; }
      .panel-left {
        flex: 0 0 auto;
        padding: 36px 24px 32px;
      }
      .panel-app-name { font-size: 30px; }
      .stat-row { display: none; }
      .panel-right { padding: 32px 20px 40px; }
    }
  </style>
</head>
<body>

  <!-- Left decorative panel -->
  <div class="panel-left">
    <div class="blob-mid"></div>
    <div class="panel-left-inner">
      <div class="logo-wrap">
        <img src="/laporan_bmkg/assets/kop/logo_bmkg.png" alt="Logo BMKG" />
      </div>
      <div class="panel-app-name">SIMPels</div>
      <div class="panel-app-badge">BMKG — Stasiun Seismik</div>
      <p class="panel-tagline">Sistem Informasi Manajemen Pemeliharaan Instrumen. Pantau, catat, dan kelola laporan pemeliharaan secara terpadu.</p>
      <div class="dot-row"><span></span><span></span><span></span></div>
      <div class="stat-row">
        <div class="stat-pill"><strong>3</strong><small>Jenis Laporan</small></div>
        <div class="stat-pill"><strong>PDF</strong><small>Unduh Laporan</small></div>
        <div class="stat-pill"><strong>Real-time</strong><small>Status</small></div>
      </div>
    </div>
  </div>

  <!-- Right form panel -->
  <div class="panel-right">
    <div class="form-box">
      <div class="form-heading">
        <h2>Selamat Datang</h2>
        <p>Masuk ke akun Anda untuk melanjutkan.</p>
      </div>

      <form id="login-form" novalidate>
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <input id="username" name="username" type="text" autocomplete="username" required placeholder="Masukkan username" />
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Masukkan password" />
          </div>
        </div>

        <button class="btn-login" id="login-btn" type="submit">
          <div class="spinner" id="spinner"></div>
          <span class="btn-text">Masuk</span>
        </button>

        <div id="status" class="status-msg"></div>

        <div class="form-footer">
          Belum memiliki akun? Hubungi <a href="mailto:admin@bmkg.go.id">administrator</a>.
        </div>
      </form>
    </div>
  </div>

  <script>
    // Login form
    const form      = document.getElementById('login-form');
    const statusEl  = document.getElementById('status');
    const btn       = document.getElementById('login-btn');
    const spinner   = document.getElementById('spinner');

    function setLoading(on) {
      btn.disabled = on;
      btn.classList.toggle('loading', on);
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      statusEl.textContent = '';
      statusEl.className   = 'status-msg';
      setLoading(true);

      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;

      try {
        const res  = await fetch('/laporan_bmkg/api/auth/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
          throw new Error(data.message || 'Username atau password salah.');
        }

        statusEl.textContent = '✓ Login berhasil. Mengalihkan…';
        statusEl.className   = 'status-msg success';
        setTimeout(() => { window.location.href = '/laporan_bmkg/dashboard.php'; }, 600);
      } catch (err) {
        statusEl.textContent = err.message || 'Terjadi kesalahan. Coba lagi.';
        statusEl.className   = 'status-msg error';
        setLoading(false);
      }
    });
  </script>
</body>
</html>
