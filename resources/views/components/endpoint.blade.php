{{-- Endpoint Detail Component --}}
<div class="endpoint-detail" id="endpoint-{{ $id }}" style="display: none;">
    <div class="endpoint-content">
        {{-- Header --}}
        <div class="endpoint-header">
            <div class="endpoint-method-url">
                <span class="method-badge {{ $endpoint->getMethodBadgeClass() }}">
                    {{ $endpoint->method }}
                </span>
                <code class="endpoint-url">{{ $endpoint->uri }}</code>
            </div>
            @if($endpoint->name)
                <span class="endpoint-name">{{ $endpoint->name }}</span>
            @endif
        </div>

        {{-- Description --}}
        @if($endpoint->description)
            <p class="endpoint-description">{{ $endpoint->description }}</p>
        @endif

        {{-- Auth Info --}}
        <div class="endpoint-meta">
            @if($endpoint->requiresAuth)
                <div class="meta-badge meta-auth">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    {{ ucfirst($endpoint->authType ?? 'Auth') }} @lang('api-docs::api-docs.required')
                </div>
            @else
                <div class="meta-badge meta-public">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="m2 12 10 10 10-10M2 12l10-10 10 10"></path>
                    </svg>
                    @lang('api-docs::api-docs.public')
                </div>
            @endif

            @if($endpoint->permission)
                <div class="meta-badge meta-permission">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    {{ $endpoint->permission }}
                </div>
            @endif

            @if(!empty($endpoint->middleware))
                <div class="meta-badge meta-middleware" title="{{ implode(', ', $endpoint->middleware) }}">
                    Middleware: {{ count($endpoint->middleware) }}
                </div>
            @endif
        </div>

        {{-- URL Parameters --}}
        @if(!empty($endpoint->urlParameters))
            <div class="section">
                <h3 class="section-title">@lang('api-docs::api-docs.url_parameters')</h3>
                @include('api-docs::components.params-table', ['parameters' => $endpoint->urlParameters])
            </div>
        @endif

        {{-- Body Parameters --}}
        @if(!empty($endpoint->parameters))
            <div class="section">
                <h3 class="section-title">@lang('api-docs::api-docs.body_parameters')</h3>
                @include('api-docs::components.params-table', ['parameters' => $endpoint->parameters])
            </div>
        @endif

        {{-- Request Body Example --}}
        @php $exampleBody = $endpoint->getExampleRequestBody(); @endphp
        @if($exampleBody)
            <div class="section">
                <h3 class="section-title">@lang('api-docs::api-docs.request_body_example')</h3>
                <pre class="code-block"><code>{{ json_encode($exampleBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
            </div>
        @endif

        {{-- Responses --}}
        @if(!empty($endpoint->responses))
            <div class="section">
                <h3 class="section-title">@lang('api-docs::api-docs.responses')</h3>
                @foreach($endpoint->responses as $response)
                    <div class="response-block {{ $response->isSuccess ? 'response-success' : 'response-error' }}">
                        <div class="response-header">
                            <span class="response-status {{ $response->isSuccess ? 'status-success' : 'status-error' }}">
                                {{ $response->statusCode }}
                            </span>
                            <span class="response-message">{{ $response->message ?? ($response->isSuccess ? 'Başarılı' : 'Hata') }}</span>
                        </div>
                        <pre class="code-block"><code>{{ json_encode($response->toResponseExample(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Code Samples --}}
        @include('api-docs::components.code-sample', [
            'endpoint' => $endpoint,
            'baseUrl' => $baseUrl,
            'config' => $config,
        ])

        {{-- Try It --}}
        @include('api-docs::components.try-it', [
            'endpoint' => $endpoint,
            'id' => $id,
            'baseUrl' => $baseUrl,
            'config' => $config,
        ])
    </div>
</div>
