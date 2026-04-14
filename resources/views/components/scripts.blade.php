/* ============================================
   Laravel API Docs - JavaScript
   Try It, Search, Theme Toggle, Export
   ============================================ */

// === Dropdown ===
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    if (!dropdown) return;
    dropdown.classList.toggle('open');
}

// Dışarı tıklayınca dropdown kapat
document.addEventListener('click', function(e) {
    document.querySelectorAll('.dropdown.open').forEach(dd => {
        if (!dd.contains(e.target)) {
            dd.classList.remove('open');
        }
    });
});

// === Endpoint Selection ===
function showEndpoint(id, event) {
    if (event) event.preventDefault();

    // Welcome screen gizle
    const welcome = document.getElementById('welcomeScreen');
    if (welcome) welcome.style.display = 'none';

    // Tüm endpoint detayları gizle
    document.querySelectorAll('.endpoint-detail').forEach(el => {
        el.style.display = 'none';
    });

    // Seçili endpoint'i göster
    const target = document.getElementById('endpoint-' + id);
    if (target) {
        target.style.display = 'block';
    }

    // Sidebar aktif durumunu güncelle
    document.querySelectorAll('.sidebar-endpoint').forEach(el => {
        el.classList.remove('active');
    });

    const activeLink = document.querySelector('[data-endpoint-id="' + id + '"]');
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// === Sidebar Group Toggle ===
function toggleGroup(button) {
    const group = button.closest('.sidebar-group');
    group.classList.toggle('collapsed');
}

// === Code Tab Switching ===
function switchCodeTab(button, lang) {
    const section = button.closest('.section');
    if (!section) return;

    // Tab butonları
    section.querySelectorAll('.code-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    button.classList.add('active');

    // Panel'ler
    section.querySelectorAll('.code-panel').forEach(panel => {
        if (panel.dataset.lang === lang) {
            panel.style.display = 'block';
            panel.classList.add('active');
        } else {
            panel.style.display = 'none';
            panel.classList.remove('active');
        }
    });
}

// === Theme Toggle ===
document.getElementById('themeToggle').addEventListener('click', function() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('api-docs-theme', newTheme);
});

// Load saved theme
(function() {
    const savedTheme = localStorage.getItem('api-docs-theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
})();

// === Search ===
document.getElementById('searchInput').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const endpoints = document.querySelectorAll('.sidebar-endpoint');
    const groups = document.querySelectorAll('.sidebar-group');

    if (!query) {
        // Arama temizlendi, tümünü göster
        endpoints.forEach(el => el.style.display = '');
        groups.forEach(g => {
            g.style.display = '';
            g.classList.remove('collapsed');
        });
        return;
    }

    // Her endpoint'i kontrol et
    const visibleGroups = new Set();

    endpoints.forEach(el => {
        const uri = (el.dataset.uri || '').toLowerCase();
        const method = (el.dataset.method || '').toLowerCase();
        const text = (el.textContent || '').toLowerCase();

        if (uri.includes(query) || method.includes(query) || text.includes(query)) {
            el.style.display = '';
            visibleGroups.add(el.dataset.group);
        } else {
            el.style.display = 'none';
        }
    });

    // Boş grupları gizle
    groups.forEach(g => {
        const groupKey = g.dataset.group;
        if (visibleGroups.has(groupKey)) {
            g.style.display = '';
            g.classList.remove('collapsed');
        } else {
            g.style.display = 'none';
        }
    });
});

// === Try It ===
async function sendRequest(id, method, requiresAuth) {
    const panel = document.getElementById('tryit-' + id);
    if (!panel) return;

    const urlInput = panel.querySelector('.try-it-url');
    let url = urlInput.value;

    // URL parametrelerini değiştir
    const urlParams = panel.querySelectorAll('.try-it-url-param');
    urlParams.forEach(input => {
        const paramName = input.dataset.paramName;
        const value = input.value || paramName;
        url = url.replace('{' + paramName + '}', encodeURIComponent(value));
    });

    // Headers
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    // Auth token
    if (requiresAuth) {
        const tokenInput = panel.querySelector('.try-it-token');
        if (tokenInput && tokenInput.value) {
            headers[AUTH_HEADER] = AUTH_PREFIX + ' ' + tokenInput.value;
        }
    }

    // Body
    let body = null;
    const bodyTextarea = panel.querySelector('.try-it-body');
    if (bodyTextarea && bodyTextarea.value && method !== 'GET' && method !== 'DELETE') {
        try {
            body = bodyTextarea.value;
            JSON.parse(body); // Validate JSON
        } catch (e) {
            alert('Geçersiz JSON formatı!');
            return;
        }
    }

    // Send button disable
    const sendBtn = panel.querySelector('.btn-send');
    const originalText = sendBtn.innerHTML;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<svg class="spin" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg> Sending...';

    const startTime = performance.now();

    try {
        const options = {
            method: method,
            headers: headers,
        };

        if (body && method !== 'GET' && method !== 'DELETE') {
            options.body = body;
        }

        const response = await fetch(url, options);
        const endTime = performance.now();
        const duration = Math.round(endTime - startTime);

        let responseText;
        try {
            const json = await response.json();
            responseText = JSON.stringify(json, null, 2);
        } catch {
            responseText = await response.text();
        }

        // Response göster
        showResponse(id, response.status, duration, responseText);
    } catch (error) {
        const endTime = performance.now();
        const duration = Math.round(endTime - startTime);
        showResponse(id, 0, duration, 'Error: ' + error.message);
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = originalText;
    }
}

function showResponse(id, statusCode, duration, body) {
    const responseEl = document.getElementById('response-' + id);
    const statusEl = document.getElementById('response-status-' + id);
    const timeEl = document.getElementById('response-time-' + id);
    const bodyEl = document.getElementById('response-body-' + id);

    if (!responseEl) return;

    responseEl.style.display = 'block';

    // Status code
    statusEl.textContent = statusCode || 'Error';
    statusEl.style.background = (statusCode >= 200 && statusCode < 300)
        ? 'var(--success-bg)' : 'var(--error-bg)';
    statusEl.style.color = (statusCode >= 200 && statusCode < 300)
        ? 'var(--success)' : 'var(--error)';

    // Duration
    timeEl.textContent = duration + 'ms';

    // Body
    bodyEl.querySelector('code').textContent = body;
}

function clearResponse(id) {
    const responseEl = document.getElementById('response-' + id);
    if (responseEl) {
        responseEl.style.display = 'none';
    }
}

// === Keyboard shortcut ===
document.addEventListener('keydown', function(e) {
    // Ctrl+K veya Cmd+K → Search focus
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('searchInput').focus();
    }

    // Escape → Clear search
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('searchInput');
        if (document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.blur();
        }
    }
});
