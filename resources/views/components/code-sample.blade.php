{{-- Code Sample Component --}}
@php
    $fullUrl = rtrim($baseUrl, '/') . $endpoint->uri;
    $exampleBody = $endpoint->getExampleRequestBody();
    $bodyJson = $exampleBody ? json_encode($exampleBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null;
@endphp

<div class="section">
    <h3 class="section-title">@lang('api-docs::api-docs.code_samples')</h3>

    <div class="code-tabs">
        <button class="code-tab active" onclick="switchCodeTab(this, 'curl')">cURL</button>
        <button class="code-tab" onclick="switchCodeTab(this, 'javascript')">JavaScript</button>
        <button class="code-tab" onclick="switchCodeTab(this, 'php')">PHP</button>
    </div>

    {{-- cURL --}}
    <div class="code-panel active" data-lang="curl">
        <pre class="code-block"><code>curl -X {{ $endpoint->method }} \
  '{{ $fullUrl }}' \
@if($endpoint->requiresAuth)
  -H '{{ $config['default_auth_header'] ?? 'Authorization' }}: {{ $config['default_auth_prefix'] ?? 'Bearer' }} &#123;token&#125;' \
@endif
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json'@if($bodyJson) \
  -d '{{ $bodyJson }}'@endif</code></pre>
    </div>

    {{-- JavaScript --}}
    <div class="code-panel" data-lang="javascript" style="display:none;">
        <pre class="code-block"><code>const response = await fetch('{{ $fullUrl }}', {
    method: '{{ $endpoint->method }}',
    headers: {
@if($endpoint->requiresAuth)
        '{{ $config['default_auth_header'] ?? 'Authorization' }}': '{{ $config['default_auth_prefix'] ?? 'Bearer' }} &#123;token&#125;',
@endif
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
@if($bodyJson)
    body: JSON.stringify({!! $bodyJson !!}),
@endif
});

const data = await response.json();
console.log(data);</code></pre>
    </div>

    {{-- PHP Guzzle --}}
    <div class="code-panel" data-lang="php" style="display:none;">
        <pre class="code-block"><code>$client = new \GuzzleHttp\Client();

$response = $client->request('{{ $endpoint->method }}', '{{ $fullUrl }}', [
    'headers' => [
@if($endpoint->requiresAuth)
        '{{ $config['default_auth_header'] ?? 'Authorization' }}' => '{{ $config['default_auth_prefix'] ?? 'Bearer' }} {token}',
@endif
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],
@if($bodyJson)
    'json' => {!! str_replace(["\n", '    '], ["\n        ", '        '], $bodyJson) !!},
@endif
]);

$data = json_decode($response->getBody(), true);</code></pre>
    </div>
</div>
