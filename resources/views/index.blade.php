<!DOCTYPE html>
<html lang="{{ $config['locale'] ?? 'tr' }}" data-theme="{{ $theme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'API') }} - @lang('api-docs::api-docs.title')</title>
    <style>{!! $inlineCss !!}</style>
</head>
<body>
    <div class="app-container">
        {{-- Header --}}
        <header class="app-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <h1 class="header-title">{{ config('app.name', 'API') }} - @lang('api-docs::api-docs.title')</h1>
            </div>
            <div class="header-right">
                <div class="search-wrapper">
                    <svg class="search-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="@lang('api-docs::api-docs.search_placeholder')">
                </div>

                {{-- Export Dropdown --}}
                <div class="dropdown" id="exportDropdown">
                    <button class="btn-icon" onclick="toggleDropdown('exportDropdown')" title="Export">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </button>
                    <div class="dropdown-menu">
                        <a href="{{ route('api-docs.openapi') }}" class="dropdown-item">
                            <span class="dropdown-icon">📋</span> OpenAPI 3.0
                        </a>
                        <a href="{{ route('api-docs.postman') }}" class="dropdown-item">
                            <span class="dropdown-icon">📮</span> Postman Collection
                        </a>
                        <a href="{{ route('api-docs.markdown') }}" class="dropdown-item">
                            <span class="dropdown-icon">📝</span> Markdown
                        </a>
                        <a href="{{ route('api-docs.json') }}" class="dropdown-item">
                            <span class="dropdown-icon">{ }</span> Raw JSON
                        </a>
                    </div>
                </div>

                <button id="themeToggle" class="btn-icon" title="Tema değiştir">
                    <svg class="icon-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
                    </svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
            </div>
        </header>

        <div class="app-body">
            {{-- Mobile Overlay --}}
            <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

            {{-- Sidebar --}}
            @include('api-docs::components.sidebar', ['groups' => $groups])

            {{-- Main Content --}}
            <main class="main-content" id="mainContent">
                <div class="welcome-screen" id="welcomeScreen">
                    <div class="welcome-icon">📖</div>
                    <h2>@lang('api-docs::api-docs.welcome_title')</h2>
                    <p>@lang('api-docs::api-docs.welcome_desc')</p>
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-number">{{ count($endpoints) }}</span>
                            <span class="stat-label">Endpoint</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">{{ count($groups) }}</span>
                            <span class="stat-label">Grup</span>
                        </div>
                    </div>
                </div>

                {{-- Endpoint detay alanları (gizli, JS ile gösterilecek) --}}
                @foreach($groups as $groupKey => $group)
                    @foreach($group['endpoints'] as $index => $endpoint)
                        @include('api-docs::components.endpoint', [
                            'endpoint' => $endpoint,
                            'id' => $groupKey . '-' . $index,
                            'baseUrl' => $baseUrl,
                            'config' => $config,
                        ])
                    @endforeach
                @endforeach
            </main>
        </div>
    </div>

    <script>
        const API_BASE_URL = @json($baseUrl);
        const AUTH_HEADER = @json($config['default_auth_header'] ?? 'Authorization');
        const AUTH_PREFIX = @json($config['default_auth_prefix'] ?? 'Bearer');
    </script>
    <script>{!! $inlineJs !!}</script>
</body>
</html>
