<?php

namespace LaravelApiDocs\Parsers;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use LaravelApiDocs\Models\EndpointDoc;

class RouteParser
{
    protected Router $router;
    protected array $config;

    public function __construct(Router $router, array $config = [])
    {
        $this->router = $router;
        $this->config = $config;
    }

    /**
     * Tüm API route'larını parse et ve EndpointDoc listesi döndür.
     *
     * @return EndpointDoc[]
     */
    public function parse(): array
    {
        $routes = $this->router->getRoutes();
        $endpoints = [];

        foreach ($routes as $route) {
            if (!$this->shouldIncludeRoute($route)) {
                continue;
            }

            $methods = $this->getRouteMethods($route);

            foreach ($methods as $method) {
                $endpoint = $this->buildEndpointDoc($route, $method);
                if ($endpoint) {
                    $endpoints[] = $endpoint;
                }
            }
        }

        return $this->sortEndpoints($endpoints);
    }

    /**
     * Route'un dahil edilip edilmeyeceğini kontrol et.
     */
    protected function shouldIncludeRoute(Route $route): bool
    {
        $uri = $route->uri();

        // Prefix kontrolü
        $prefixes = $this->config['route_prefix'] ?? ['api'];
        $matchesPrefix = false;
        foreach ($prefixes as $prefix) {
            if (Str::startsWith($uri, $prefix)) {
                $matchesPrefix = true;
                break;
            }
        }

        if (!$matchesPrefix) {
            return false;
        }

        // Paketin kendi route'larını hariç tut
        $docsPath = $this->config['path'] ?? 'api-docs';
        if (Str::startsWith($uri, $docsPath) || Str::contains($uri, $docsPath)) {
            return false;
        }

        // Route adı api-docs ile başlıyorsa hariç tut
        $routeName = $route->getName();
        if ($routeName && Str::startsWith($routeName, 'api-docs')) {
            return false;
        }

        // Hariç tutulan route kontrolü
        $excludeRoutes = $this->config['exclude_routes'] ?? [];
        foreach ($excludeRoutes as $excludeRoute) {
            if ($uri === $excludeRoute || Str::is($excludeRoute, $uri)) {
                return false;
            }
        }

        // Hariç tutulan middleware kontrolü
        $excludeMiddlewares = $this->config['exclude_middlewares'] ?? [];
        $routeMiddleware = $this->getRouteMiddleware($route);
        foreach ($excludeMiddlewares as $excludeMiddleware) {
            if (in_array($excludeMiddleware, $routeMiddleware)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Route'un HTTP method'larını al (HEAD hariç).
     */
    protected function getRouteMethods(Route $route): array
    {
        return array_filter($route->methods(), fn(string $method) => $method !== 'HEAD');
    }

    /**
     * Route'tan EndpointDoc oluştur.
     */
    protected function buildEndpointDoc(Route $route, string $method): ?EndpointDoc
    {
        $action = $route->getAction();
        $controllerInfo = $this->parseControllerAction($action);

        if (!$controllerInfo) {
            return null;
        }

        $uri = $route->uri();
        $groupInfo = $this->extractGroupInfo($uri);

        $endpoint = new EndpointDoc(
            method: strtoupper($method),
            uri: '/' . ltrim($uri, '/'),
            name: $route->getName(),
            groupName: $groupInfo['name'],
            groupPrefix: $groupInfo['prefix'],
            controller: $controllerInfo['class'],
            controllerMethod: $controllerInfo['method'],
            middleware: $this->getRouteMiddleware($route),
        );

        // URL parametrelerini parse et
        $endpoint->urlParameters = $this->extractUrlParameters($uri);

        return $endpoint;
    }

    /**
     * Controller ve method bilgisini parse et.
     */
    protected function parseControllerAction(array $action): ?array
    {
        if (isset($action['uses']) && is_string($action['uses'])) {
            $parts = explode('@', $action['uses']);
            if (count($parts) === 2) {
                return [
                    'class' => $parts[0],
                    'method' => $parts[1],
                ];
            }

            // Invokable controller
            if (class_exists($action['uses'])) {
                return [
                    'class' => $action['uses'],
                    'method' => '__invoke',
                ];
            }
        }

        if (isset($action['controller'])) {
            $parts = explode('@', $action['controller']);
            if (count($parts) === 2) {
                return [
                    'class' => $parts[0],
                    'method' => $parts[1],
                ];
            }
        }

        return null;
    }

    /**
     * URI'dan grup bilgisini çıkar.
     */
    protected function extractGroupInfo(string $uri): array
    {
        $groupBy = $this->config['group_by'] ?? 'prefix';
        $segments = explode('/', trim($uri, '/'));

        // api/v1/company/create → group: company
        // İlk iki segment genelde api/v1 olur, sonraki segment grup adı
        $prefixes = $this->config['route_prefix'] ?? ['api'];
        $prefixSegmentCount = 0;

        foreach ($prefixes as $prefix) {
            if (Str::startsWith($uri, $prefix)) {
                $prefixSegmentCount = count(explode('/', trim($prefix, '/')));
                break;
            }
        }

        $groupSegment = $segments[$prefixSegmentCount] ?? null;

        if ($groupSegment && !Str::startsWith($groupSegment, '{')) {
            return [
                'name' => Str::title(str_replace(['-', '_'], ' ', $groupSegment)),
                'prefix' => $groupSegment,
            ];
        }

        return [
            'name' => 'General',
            'prefix' => '',
        ];
    }

    /**
     * URL'deki parametreleri çıkar ({id}, {slug} gibi).
     */
    protected function extractUrlParameters(string $uri): array
    {
        $params = [];
        preg_match_all('/\{(\w+?)\??}/', $uri, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $paramName) {
                $isOptional = Str::endsWith($matches[0][$index], '?}');
                $params[] = new \LaravelApiDocs\Models\ParameterDoc(
                    name: $paramName,
                    type: 'string',
                    required: !$isOptional,
                    description: 'URL parametresi',
                );
            }
        }

        return $params;
    }

    /**
     * Route middleware'lerini al.
     */
    protected function getRouteMiddleware(Route $route): array
    {
        $middleware = $route->gatherMiddleware();
        return array_values(array_unique($middleware));
    }

    /**
     * Endpoint'leri grup ve URI'ya göre sırala.
     */
    protected function sortEndpoints(array $endpoints): array
    {
        usort($endpoints, function (EndpointDoc $a, EndpointDoc $b) {
            $groupCompare = strcmp($a->groupName ?? '', $b->groupName ?? '');
            if ($groupCompare !== 0) {
                return $groupCompare;
            }
            return strcmp($a->uri, $b->uri);
        });

        return $endpoints;
    }
}
