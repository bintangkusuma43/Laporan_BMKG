'use strict';

const APP_BASE_PATH = window.location.pathname.startsWith('/laporan_bmkg/') || window.location.pathname === '/laporan_bmkg'
    ? '/laporan_bmkg'
    : '';
const BASE_URL = window.location.origin + APP_BASE_PATH;

function navIcon(name) {
    const icons = {
        home: 'M3 10.5 12 3l9 7.5v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z',
        wrs: 'M12 13a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm6.5-2a6.5 6.5 0 0 0-13 0m10 0a3.5 3.5 0 0 0-7 0',
        accelerograph: 'M3 13h3l2-6 4 12 2-6h5',
        seismo: 'M3 15h4l2-5 2 6 2-5 2 4h4',
        history: 'M12 8v5l3 2m-3-9a7 7 0 1 0 7 7',
        parts: 'M4 17l4.5-4.5m1 1L7 18l4.5-1.5 6-6a3 3 0 1 0-4.2-4.2l-6 6Z',
        gallery: 'M4 5h16v14H4z M6 9a2 2 0 1 0 4 0 2 2 0 0 0-4 0zm-1 8 4-4 3 3 2-2 3 3',
        users: 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 8a7 7 0 0 1 14 0',
    };
    const path = icons[name] || icons.home;
    return `<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="${path}" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>`;
}

function showGlobalAlert(message, type = 'error') {
    const mainBody = document.querySelector('.page-body') || document.querySelector('main') || document.body;
    if (!mainBody) {
        return;
    }

    let container = document.getElementById('global-alert');
    if (!container) {
        container = document.createElement('div');
        container.id = 'global-alert';
        mainBody.prepend(container);
    }
    showAlert(container, message, type);
}

async function apiRequest(path, options = {}) {
    if (window.location.protocol === 'file:') {
        const error = new Error('Aplikasi harus dijalankan melalui server (contoh: http://localhost/laporan_bmkg/login.php), bukan dibuka langsung dari file Explorer.');
        error.code = 'FILE_PROTOCOL_NOT_SUPPORTED';
        throw error;
    }

    const finalOptions = {
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
        },
        ...options,
    };

    if (finalOptions.body instanceof FormData === false && finalOptions.method && finalOptions.method.toUpperCase() !== 'GET') {
        finalOptions.headers['Content-Type'] = 'application/json';
    }

    let response;
    try {
        response = await fetch(`${BASE_URL}${path}`, finalOptions);
    } catch (networkError) {
        const error = new Error('Tidak dapat menghubungi server. Pastikan kamu membuka aplikasi via URL server (http://localhost/...) dan Apache/PHP server sedang berjalan.');
        error.cause = networkError;
        error.code = 'NETWORK_ERROR';
        throw error;
    }
    const contentType = response.headers.get('content-type') || '';
    let data;
    try {
        data = await response.json();
    } catch (_) {
        let text = '';
        try {
            text = await response.text();
        } catch (_) {
            // ignore
        }
        const snippet = (text || '').toString().replace(/\s+/g, ' ').trim().slice(0, 400);
        const hint = snippet
            ? `Server mengembalikan respon non-JSON (HTTP ${response.status}). Potongan: ${snippet}`
            : `Server mengembalikan respon non-JSON (HTTP ${response.status}).`;
        data = { success: false, message: hint, _contentType: contentType };
    }

    if (!response.ok || data.success === false) {
        const error = new Error(data.message || 'Terjadi kesalahan.');
        error.payload = data;
        throw error;
    }

    return data;
}

async function getCurrentUser() {
    try {
        const { authenticated, data } = await apiRequest('/api/auth/me.php', { method: 'GET' });
        if (!authenticated) {
            throw new Error('not-authenticated');
        }
        return data;
    } catch (error) {
        throw error;
    }
}

function requireAuth(options = {}) {
    return getCurrentUser().then(user => {
        renderUserMenu(user, options.hideNavigation === true ? null : userNavigation(user));
        return user;
    }).catch(error => {
        if (error.message === 'not-authenticated' || error?.payload?.authenticated === false || error?.message === 'NOT_AUTHENTICATED') {
            window.location.href = `${APP_BASE_PATH}/login.php`;
            return;
        }

        const message = error?.message || 'Terjadi kesalahan saat memuat aplikasi.';
        console.error(error);
        showGlobalAlert(message, 'error');
    });
}

function userNavigation(user) {
    const sections = [
        {
            title: 'Pelaporan',
            items: [
                { href: 'dashboard.php', label: 'Dashboard', icon: 'home' },
                { href: 'laporan_form_wrs.php', label: 'Laporan WRS NG', icon: 'wrs' },
                { href: 'laporan_form_accelerograph.php', label: 'Laporan Accelerograph / Intensitymeter', icon: 'accelerograph' },
                { href: 'laporan_form_seismograph.php', label: 'Laporan Seismograph', icon: 'seismo' },
            ],
        },
        {
            title: 'Riwayat',
            items: [
                { href: 'laporan_list.php', label: 'Riwayat Laporan', icon: 'history' },
                { href: 'laporan_list.php?view=suku_cadang', label: 'Riwayat Suku Cadang', icon: 'parts' },
            ],
        },
        {
            title: 'Media',
            items: [
                { href: 'laporan_list.php?view=galeri', label: 'Galeri Foto', icon: 'gallery' },
            ],
        },
    ];

    if (user.role === 'admin') {
        sections.push({
            title: 'Admin',
            items: [
                { href: 'kelola_akun.php', label: 'Kelola Akun', icon: 'users' },
            ],
        });
    }

    return sections
        .map(section => ({
            ...section,
            items: section.items.filter(item => !item.hidden),
        }))
        .filter(section => section.items.length > 0);
}

function renderUserMenu(user, navigation) {
    const navContainer = document.querySelector('[data-nav]');
    const userInfo = document.querySelector('[data-user-info]');

    if (navContainer && navigation) {
        const hasStaticNav = navContainer.dataset.staticNav === 'true' || navContainer.querySelector('.nav-link');
        if (!hasStaticNav) {
            const markup = navigation.map(section => {
                const items = section.items.map(item => `<a class="nav-link" href="${item.href}"><span class="nav-icon">${navIcon(item.icon)}</span><span class="nav-label">${item.label}</span></a>`).join('');
                return items ? `<div class="nav-section"><div class="nav-section-title">${section.title}</div>${items}</div>` : '';
            }).join('');
            navContainer.innerHTML = markup;
        }
        setActiveNav(navContainer);
    }

    if (userInfo) {
        userInfo.textContent = `${user.full_name ?? user.username} (${user.role})`;
    }

    const logoutButton = document.querySelector('[data-logout]');
    if (logoutButton) {
        logoutButton.addEventListener('click', async event => {
            event.preventDefault();
            await apiRequest('/api/auth/logout.php', { method: 'POST' });
            window.location.href = `${APP_BASE_PATH}/login.php`;
        });
    }
}

function setActiveNav(navContainer) {
    const currentUrl = new URL(window.location.href);
    const normalizePath = (path) => path.replace(/\/+/g, '/').replace(/\/$/, '');
    const currentPath = normalizePath(currentUrl.pathname);
    const currentSearch = currentUrl.search;

    navContainer.querySelectorAll('.nav-link').forEach(anchor => {
        const targetUrl = new URL(anchor.getAttribute('href') || '', window.location.origin);
        const targetPath = normalizePath(targetUrl.pathname);
        const targetSearch = targetUrl.search;

        let isActive = targetPath === currentPath;
        if (isActive) {
            if (targetSearch) {
                isActive = targetSearch === currentSearch;
            } else {
                isActive = currentSearch === '' || currentSearch === '?';
            }
        }

        anchor.classList.toggle('is-active', isActive);
    });
}

function buildSelectOptions(element, items, config) {
    if (!element) {
        return;
    }
    const { valueKey = 'id', labelKey = 'nama', placeholder = 'Pilih' } = config;
    element.innerHTML = `<option value="">${placeholder}</option>` + items.map(item => `<option value="${item[valueKey]}">${item[labelKey]}</option>`).join('');
}

function formatDate(dateString) {
    if (!dateString) {
        return '-';
    }
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatStatus(status) {
    const map = {
        diajukan: 'Diajukan',
        diproses: 'Diproses',
        selesai: 'Selesai',
        ditolak: 'Ditolak',
        draft: 'Draft'
    };
    return map[status] ?? status;
}

function formatJenis(jenis) {
    const map = {
        wrs_ng: 'WRS NG',
        accelerograph: 'Accelerograph / Intensitymeter',
        seismograph: 'Seismograph'
    };
    return map[jenis] ?? jenis;
}

function showAlert(container, message, type = 'error') {
    if (!container) {
        return;
    }
    container.innerHTML = `<div class="alert ${type}">${message}</div>`;
}

function markFilled(el) {
    if (!el) return;
    const type = (el.type || '').toLowerCase();
    let hasValue = false;
    if (type === 'checkbox' || type === 'radio') {
        hasValue = el.checked;
    } else if (type === 'file') {
        hasValue = !!(el.files && el.files.length); // best-effort; existing file previews handled separately
    } else {
        hasValue = !!(el.value || '').trim();
    }
    el.classList.toggle('filled', hasValue);
}

function initFilledIndicators(scope) {
    const root = scope || document;
    const controls = root.querySelectorAll('input, textarea, select');
    controls.forEach((el) => {
        markFilled(el);
        ['input', 'change', 'blur'].forEach((evt) => {
            el.addEventListener(evt, () => markFilled(el));
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initFilledIndicators();
});
