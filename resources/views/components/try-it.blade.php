{{-- Try It Component --}}
@php
    $fullUrl = rtrim($baseUrl, '/') . $endpoint->uri;
    $exampleBody = $endpoint->getExampleRequestBody();
@endphp

<div class="section try-it-section">
    <h3 class="section-title">
        @lang('api-docs::api-docs.try_it')
        <span class="try-it-badge">Live</span>
    </h3>

    <div class="try-it-panel" id="tryit-{{ $id }}">
        {{-- URL --}}
        <div class="try-it-field">
            <label>URL</label>
            <div class="try-it-url-row">
                <span class="method-badge method-badge-sm {{ $endpoint->getMethodBadgeClass() }}">{{ $endpoint->method }}</span>
                <input type="text" class="try-it-input try-it-url" value="{{ $fullUrl }}" data-original-url="{{ $fullUrl }}">
            </div>
        </div>

        {{-- Auth Token --}}
        @if($endpoint->requiresAuth)
            <div class="try-it-field">
                <label>{{ $config['default_auth_header'] ?? 'Authorization' }}</label>
                <div class="try-it-auth-row">
                    <span class="auth-prefix">{{ $config['default_auth_prefix'] ?? 'Bearer' }}</span>
                <input type="text" class="try-it-input try-it-token" placeholder="@lang('api-docs::api-docs.token_placeholder')">
                </div>
            </div>
        @endif

        {{-- URL Parameters --}}
        @if(!empty($endpoint->urlParameters))
            <div class="try-it-field">
                <label>URL Parameters</label>
                @foreach($endpoint->urlParameters as $param)
                    <div class="try-it-param-row">
                        <span class="param-label">{{ '{' . $param->name . '}' }}</span>
                        <input type="text"
                               class="try-it-input try-it-url-param"
                               data-param-name="{{ $param->name }}"
                               placeholder="{{ $param->name }}">
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Request Body --}}
        @if($exampleBody)
            <div class="try-it-field">
                <label>Request Body (JSON)</label>
                <textarea class="try-it-body" rows="10">{{ json_encode($exampleBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
            </div>
        @endif

        {{-- Send Button --}}
        <div class="try-it-actions">
            <button class="btn-send" onclick="sendRequest('{{ $id }}', '{{ $endpoint->method }}', {{ $endpoint->requiresAuth ? 'true' : 'false' }})">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m22 2-7 20-4-9-9-4z"></path>
                    <path d="m22 2-10 10"></path>
                </svg>
                @lang('api-docs::api-docs.send')
            </button>
            <button class="btn-clear" onclick="clearResponse('{{ $id }}')">@lang('api-docs::api-docs.clear')</button>
        </div>

        {{-- Response --}}
        <div class="try-it-response" id="response-{{ $id }}" style="display: none;">
            <div class="response-info">
                <span class="response-status-code" id="response-status-{{ $id }}"></span>
                <span class="response-time" id="response-time-{{ $id }}"></span>
            </div>
            <pre class="code-block response-body" id="response-body-{{ $id }}"><code></code></pre>
        </div>
    </div>
</div>
