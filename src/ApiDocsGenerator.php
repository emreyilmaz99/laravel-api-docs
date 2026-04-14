<?php

namespace LaravelApiDocs;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use LaravelApiDocs\Models\EndpointDoc;
use LaravelApiDocs\Parsers\FormRequestParser;
use LaravelApiDocs\Parsers\MiddlewareParser;
use LaravelApiDocs\Parsers\ResponseParser;
use LaravelApiDocs\Parsers\RouteParser;

class ApiDocsGenerator
{
    protected RouteParser $routeParser;
    protected FormRequestParser $formRequestParser;
    protected MiddlewareParser $middlewareParser;
    protected ResponseParser $responseParser;
    protected array $config;

    protected const CACHE_KEY = 'laravel-api-docs:generated';

    public function __construct(
        Router $router,
        array $config = []
    ) {
        $this->config = $config;
        $this->routeParser = new RouteParser($router, $config);
        $this->formRequestParser = new FormRequestParser();
        $this->middlewareParser = new MiddlewareParser();
        $this->responseParser = new ResponseParser($config);
    }

    /**
     * Tüm API dokümantasyonunu üret (cache destekli).
     *
     * @return array{endpoints: EndpointDoc[], groups: array}
     */
    public function generate(): array
    {
        $ttl = (int) ($this->config['cache_ttl'] ?? 0);

        if ($ttl > 0) {
            return Cache::remember(self::CACHE_KEY, $ttl * 60, fn () => $this->buildDocs());
        }

        return $this->buildDocs();
    }

    /**
     * Cache'i temizle.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Dokümantasyonu sıfırdan üret.
     */
    protected function buildDocs(): array
    {
        // 1. Route'ları parse et
        $endpoints = $this->routeParser->parse();

        // 2. Her endpoint için detaylı bilgi topla
        foreach ($endpoints as $endpoint) {
            $this->enrichEndpoint($endpoint);
        }

        // 3. Grupla
        $groups = $this->groupEndpoints($endpoints);

        return [
            'endpoints' => $endpoints,
            'groups' => $groups,
        ];
    }

    /**
     * Tek bir endpoint'i detaylı bilgilerle zenginleştir.
     */
    protected function enrichEndpoint(EndpointDoc $endpoint): void
    {
        // Middleware bilgisi
        $middlewareInfo = $this->middlewareParser->parse($endpoint->middleware);
        $endpoint->requiresAuth = $middlewareInfo['requires_auth'];
        $endpoint->authType = $middlewareInfo['auth_type'];
        $endpoint->permission = $middlewareInfo['permission'];

        // FormRequest parametreleri
        if ($endpoint->controller && $endpoint->controllerMethod) {
            $endpoint->parameters = $this->formRequestParser->parse(
                $endpoint->controller,
                $endpoint->controllerMethod
            );

            $endpoint->formRequestClass = $this->formRequestParser->getFormRequestClass(
                $endpoint->controller,
                $endpoint->controllerMethod
            );

            // Response bilgisi
            $endpoint->responses = $this->responseParser->parse(
                $endpoint->controller,
                $endpoint->controllerMethod
            );
        }

        // Açıklama üret (eğer yoksa)
        if (!$endpoint->description) {
            $endpoint->description = $this->generateDescription($endpoint);
        }
    }

    /**
     * Endpoint'leri gruplayarak döndür.
     *
     * @return array<string, array{name: string, prefix: string, endpoints: EndpointDoc[]}>
     */
    protected function groupEndpoints(array $endpoints): array
    {
        $groups = [];

        foreach ($endpoints as $endpoint) {
            $groupKey = $endpoint->groupPrefix ?: 'general';

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'name' => $endpoint->groupName ?: 'General',
                    'prefix' => $endpoint->groupPrefix ?: '',
                    'endpoints' => [],
                ];
            }

            $groups[$groupKey]['endpoints'][] = $endpoint;
        }

        // Alfabetik sırala
        ksort($groups);

        return $groups;
    }

    /**
     * Endpoint için otomatik açıklama üret.
     */
    protected function generateDescription(EndpointDoc $endpoint): string
    {
        $method = strtoupper($endpoint->method);
        $group = $endpoint->groupName ?: 'kaynak';

        // Controller method adından anlam çıkar
        $actionMap = [
            'index' => "{$group} listesini getirir",
            'show' => "{$group} detayını getirir",
            'store' => "Yeni {$group} oluşturur",
            'create' => "Yeni {$group} oluşturur",
            'update' => "{$group} günceller",
            'destroy' => "{$group} siler",
            'delete' => "{$group} siler",
        ];

        $controllerMethod = $endpoint->controllerMethod;

        if ($controllerMethod && isset($actionMap[strtolower($controllerMethod)])) {
            return $actionMap[strtolower($controllerMethod)];
        }

        // Method adından anlamlı açıklama üretmeye çalış
        if ($controllerMethod) {
            $methodLower = strtolower($controllerMethod);

            foreach ($actionMap as $key => $desc) {
                if (str_contains($methodLower, $key)) {
                    return $desc;
                }
            }

            // Genel açıklama
            $readableName = preg_replace('/([A-Z])/', ' $1', $controllerMethod);
            return trim($readableName) . " - {$group}";
        }

        return match ($method) {
            'GET' => "{$group} bilgilerini getirir",
            'POST' => "{$group} oluşturur",
            'PUT', 'PATCH' => "{$group} günceller",
            'DELETE' => "{$group} siler",
            default => "{$group} endpoint'i",
        };
    }

    /**
     * JSON formatında dokümantasyon çıktısı üret.
     */
    public function toJson(): string
    {
        $data = $this->generate();

        $output = [
            'info' => [
                'title' => config('app.name', 'API') . ' Documentation',
                'version' => '1.0.0',
                'base_url' => $this->config['base_url'] ?? 'http://localhost:8000',
            ],
            'groups' => [],
        ];

        foreach ($data['groups'] as $key => $group) {
            $groupData = [
                'name' => $group['name'],
                'prefix' => $group['prefix'],
                'endpoints' => [],
            ];

            foreach ($group['endpoints'] as $endpoint) {
                $groupData['endpoints'][] = $endpoint->toArray();
            }

            $output['groups'][$key] = $groupData;
        }

        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
