/* ============================================
   Laravel API Docs - Styles
   ClickUp API Docs Benzeri Tasarım
   ============================================ */

/* === CSS Variables === */
:root,
[data-theme="light"] {
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --bg-tertiary: #f1f3f5;
    --bg-sidebar: #fafbfc;
    --bg-code: #f6f8fa;
    --bg-hover: #f0f2f5;
    --bg-active: #e8f0fe;

    --text-primary: #1a1a2e;
    --text-secondary: #4a5568;
    --text-tertiary: #718096;
    --text-code: #e83e8c;

    --border-color: #e2e8f0;
    --border-light: #edf2f7;

    --accent: #4f46e5;
    --accent-hover: #4338ca;
    --accent-light: #eef2ff;

    --method-get: #22c55e;
    --method-get-bg: #f0fdf4;
    --method-post: #3b82f6;
    --method-post-bg: #eff6ff;
    --method-put: #f97316;
    --method-put-bg: #fff7ed;
    --method-delete: #ef4444;
    --method-delete-bg: #fef2f2;

    --success: #22c55e;
    --success-bg: #f0fdf4;
    --error: #ef4444;
    --error-bg: #fef2f2;
    --warning: #f59e0b;

    --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);

    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;

    --sidebar-width: 280px;
    --header-height: 56px;
}

[data-theme="dark"] {
    --bg-primary: #0f172a;
    --bg-secondary: #1e293b;
    --bg-tertiary: #334155;
    --bg-sidebar: #0f172a;
    --bg-code: #1e293b;
    --bg-hover: #1e293b;
    --bg-active: #1e3a5f;

    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --text-tertiary: #64748b;
    --text-code: #f472b6;

    --border-color: #334155;
    --border-light: #1e293b;

    --accent: #818cf8;
    --accent-hover: #6366f1;
    --accent-light: #1e1b4b;

    --method-get-bg: #052e16;
    --method-post-bg: #172554;
    --method-put-bg: #431407;
    --method-delete-bg: #450a0a;

    --success-bg: #052e16;
    --error-bg: #450a0a;

    --shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
    --shadow-md: 0 4px 6px rgba(0,0,0,0.4);
    --shadow-lg: 0 10px 15px rgba(0,0,0,0.5);
}

/* === Reset & Base === */
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: var(--text-primary);
    background: var(--bg-primary);
    -webkit-font-smoothing: antialiased;
}

/* === Layout === */
.app-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

.app-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: var(--header-height);
    padding: 0 20px;
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
    z-index: 100;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}

/* === Search === */
.search-wrapper {
    position: relative;
    width: 280px;
}

.search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-tertiary);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 7px 12px 7px 32px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.search-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-light);
}

.search-input::placeholder {
    color: var(--text-tertiary);
}

/* === Theme Toggle === */
.btn-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

[data-theme="dark"] .icon-sun { display: block; }
[data-theme="dark"] .icon-moon { display: none; }
[data-theme="light"] .icon-sun { display: none; }
[data-theme="light"] .icon-moon { display: block; }

/* === Sidebar === */
.sidebar {
    width: var(--sidebar-width);
    background: var(--bg-sidebar);
    border-right: 1px solid var(--border-color);
    overflow-y: auto;
    flex-shrink: 0;
    padding: 12px 0;
}

.sidebar-nav {
    padding: 0 8px;
}

.sidebar-group {
    margin-bottom: 4px;
}

.sidebar-group-header {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 8px 12px;
    background: none;
    border: none;
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    cursor: pointer;
    transition: all 0.15s;
    gap: 8px;
}

.sidebar-group-header:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.group-name {
    flex: 1;
    text-align: left;
}

.group-count {
    font-size: 11px;
    background: var(--bg-tertiary);
    color: var(--text-tertiary);
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 500;
}

.chevron {
    transition: transform 0.2s;
}

.sidebar-group.collapsed .chevron {
    transform: rotate(-90deg);
}

.sidebar-group.collapsed .sidebar-endpoints {
    display: none;
}

.sidebar-endpoints {
    list-style: none;
    padding: 0 0 0 4px;
    margin: 0;
}

.sidebar-endpoint {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    margin: 1px 0;
    border-radius: var(--radius-sm);
    text-decoration: none;
    color: var(--text-secondary);
    font-size: 13px;
    transition: all 0.15s;
    cursor: pointer;
}

.sidebar-endpoint:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.sidebar-endpoint.active {
    background: var(--bg-active);
    color: var(--accent);
    font-weight: 500;
}

.endpoint-path {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* === Method Badges === */
.method-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 8px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    flex-shrink: 0;
}

.method-badge-sm {
    padding: 1px 6px;
    font-size: 10px;
    min-width: 42px;
}

.method-get {
    color: var(--method-get);
    background: var(--method-get-bg);
}

.method-post {
    color: var(--method-post);
    background: var(--method-post-bg);
}

.method-put {
    color: var(--method-put);
    background: var(--method-put-bg);
}

.method-delete {
    color: var(--method-delete);
    background: var(--method-delete-bg);
}

.method-default {
    color: var(--text-tertiary);
    background: var(--bg-tertiary);
}

/* === Main Content === */
.main-content {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

/* === Welcome Screen === */
.welcome-screen {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    color: var(--text-tertiary);
    padding: 40px;
}

.welcome-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.welcome-screen h2 {
    font-size: 24px;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.welcome-screen p {
    font-size: 15px;
    margin-bottom: 32px;
}

.stats {
    display: flex;
    gap: 32px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: var(--accent);
}

.stat-label {
    font-size: 13px;
    color: var(--text-tertiary);
}

/* === Endpoint Detail === */
.endpoint-detail {
    height: 100%;
    overflow-y: auto;
}

.endpoint-content {
    max-width: 900px;
    padding: 32px 40px;
    margin: 0 auto;
}

.endpoint-header {
    margin-bottom: 16px;
}

.endpoint-method-url {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.endpoint-url {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', Consolas, monospace;
    background: none;
    padding: 0;
    word-break: break-all;
}

.endpoint-name {
    font-size: 12px;
    color: var(--text-tertiary);
    font-family: monospace;
}

.endpoint-description {
    font-size: 15px;
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: 16px;
}

/* === Meta Badges === */
.endpoint-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 24px;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 500;
}

.meta-auth {
    background: var(--method-put-bg);
    color: var(--method-put);
}

.meta-public {
    background: var(--method-get-bg);
    color: var(--method-get);
}

.meta-permission {
    background: var(--accent-light);
    color: var(--accent);
}

.meta-middleware {
    background: var(--bg-tertiary);
    color: var(--text-tertiary);
}

/* === Sections === */
.section {
    margin-bottom: 28px;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* === Params Table === */
.params-table-wrapper {
    overflow-x: auto;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
}

.params-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.params-table th {
    text-align: left;
    padding: 10px 14px;
    background: var(--bg-secondary);
    color: var(--text-tertiary);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border-color);
}

.params-table td {
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-light);
    vertical-align: top;
}

.params-table tr:last-child td {
    border-bottom: none;
}

.params-table tr:hover {
    background: var(--bg-hover);
}

.param-name {
    color: var(--accent);
    font-weight: 600;
    font-size: 13px;
    background: var(--accent-light);
    padding: 2px 6px;
    border-radius: 3px;
}

.param-type {
    color: var(--text-code);
    font-size: 12px;
    font-family: monospace;
}

.badge-required {
    display: inline-block;
    padding: 1px 6px;
    background: var(--error-bg);
    color: var(--error);
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}

.badge-optional {
    display: inline-block;
    padding: 1px 6px;
    background: var(--bg-tertiary);
    color: var(--text-tertiary);
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}

.param-description {
    color: var(--text-secondary);
    font-size: 13px;
}

.param-enum {
    display: inline-block;
    font-size: 11px;
    color: var(--accent);
    background: var(--accent-light);
    padding: 1px 6px;
    border-radius: 3px;
    margin-top: 4px;
}

.param-constraint {
    display: inline-block;
    font-size: 11px;
    color: var(--text-tertiary);
    background: var(--bg-tertiary);
    padding: 1px 6px;
    border-radius: 3px;
    margin: 2px 2px 0 0;
}

/* === Code Blocks === */
.code-block {
    background: var(--bg-code);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 16px;
    overflow-x: auto;
    font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', Consolas, monospace;
    font-size: 13px;
    line-height: 1.6;
    color: var(--text-primary);
}

.code-block code {
    font-family: inherit;
}

/* === Code Tabs === */
.code-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 0;
}

.code-tab {
    padding: 8px 16px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--text-tertiary);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}

.code-tab:hover {
    color: var(--text-primary);
}

.code-tab.active {
    color: var(--accent);
    border-bottom-color: var(--accent);
}

.code-panel .code-block {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    margin-top: 0;
    border-top: none;
}

/* === Response Blocks === */
.response-block {
    margin-bottom: 16px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.response-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.response-status {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    font-family: monospace;
}

.status-success {
    background: var(--success-bg);
    color: var(--success);
}

.status-error {
    background: var(--error-bg);
    color: var(--error);
}

.response-message {
    font-size: 13px;
    color: var(--text-secondary);
}

.response-block .code-block {
    border: none;
    border-radius: 0;
    margin: 0;
}

/* === Try It === */
.try-it-section {
    border-top: 2px solid var(--border-color);
    padding-top: 24px;
}

.try-it-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    background: var(--method-get-bg);
    color: var(--method-get);
    border-radius: 10px;
    text-transform: uppercase;
}

.try-it-panel {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 20px;
}

.try-it-field {
    margin-bottom: 16px;
}

.try-it-field label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.try-it-input {
    width: 100%;
    padding: 8px 12px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 13px;
    font-family: monospace;
    outline: none;
    transition: border-color 0.2s;
}

.try-it-input:focus {
    border-color: var(--accent);
}

.try-it-url-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.try-it-url-row .try-it-input {
    flex: 1;
}

.try-it-auth-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.auth-prefix {
    padding: 8px 12px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 13px;
    color: var(--text-tertiary);
    font-family: monospace;
    white-space: nowrap;
}

.try-it-auth-row .try-it-input {
    flex: 1;
}

.try-it-param-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}

.param-label {
    min-width: 100px;
    font-size: 13px;
    font-family: monospace;
    color: var(--accent);
}

.try-it-param-row .try-it-input {
    flex: 1;
}

.try-it-body {
    width: 100%;
    padding: 12px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 13px;
    font-family: 'SF Mono', 'Fira Code', Consolas, monospace;
    line-height: 1.5;
    resize: vertical;
    outline: none;
    tab-size: 2;
}

.try-it-body:focus {
    border-color: var(--accent);
}

.try-it-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.btn-send {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-send:hover {
    background: var(--accent-hover);
}

.btn-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-clear {
    padding: 10px 16px;
    background: var(--bg-tertiary);
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-clear:hover {
    background: var(--bg-hover);
}

.try-it-response {
    margin-top: 16px;
}

.response-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.response-status-code {
    font-size: 14px;
    font-weight: 700;
    font-family: monospace;
    padding: 2px 10px;
    border-radius: var(--radius-sm);
}

.response-time {
    font-size: 12px;
    color: var(--text-tertiary);
}

.response-body {
    max-height: 400px;
    overflow-y: auto;
}

/* === Scrollbar === */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--text-tertiary);
}

/* === Responsive === */
@media (max-width: 768px) {
    .sidebar {
        width: 240px;
    }

    .endpoint-content {
        padding: 20px;
    }

    .search-wrapper {
        width: 200px;
    }

    .header-title {
        font-size: 14px;
    }
}

/* === No results === */
.no-results {
    padding: 20px;
    text-align: center;
    color: var(--text-tertiary);
    font-size: 13px;
}

/* === Dropdown === */
.dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    min-width: 200px;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    z-index: 200;
    padding: 4px;
    animation: dropdownIn 0.15s ease;
}

@keyframes dropdownIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown.open .dropdown-menu {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 13px;
    transition: all 0.15s;
    cursor: pointer;
}

.dropdown-item:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.dropdown-icon {
    font-size: 14px;
    width: 20px;
    text-align: center;
}
