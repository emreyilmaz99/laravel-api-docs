<?php

namespace LaravelApiDocs\Renderers;

use LaravelApiDocs\Models\EndpointDoc;
use LaravelApiDocs\Models\ParameterDoc;

class PostmanRenderer
{
    /**
     * Postman Collection v2.1 formatında JSON üret.
     */
    public function render(array $groups, array $config = []): string
    {
        $baseUrl = $config['base_url'] ?? 'http://localhost:8000';

        $collection = [
            'info' => [
                'name' => config('app.name', 'API') . ' Collection',
                '_postman_id' => $this->generateUuid(),
                'description' => 'Auto-generated Postman collection',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => $this->buildFolders($groups, $baseUrl, $config),
            'auth' => [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{auth_token}}',
                        'type' => 'string',
                    ],
                ],
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $baseUrl,
                    'type' => 'string',
                ],
                [
                    'key' => 'auth_token',
                    'value' => '',
                    'type' => 'string',
                ],
            ],
        ];

        return json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Grup klasörlerini oluştur.
     */
    protected function buildFolders(array $groups, string $baseUrl, array $config): array
    {
        $folders = [];

        foreach ($groups as $group) {
            $folder = [
                'name' => $group['name'],
                'item' => [],
            ];

            foreach ($group['endpoints'] as $endpoint) {
                $folder['item'][] = $this->buildRequest($endpoint, $baseUrl, $config);
            }

            $folders[] = $folder;
        }

        return $folders;
    }

    /**
     * Tek bir Postman request objesi oluştur.
     */
    protected function buildRequest(EndpointDoc $endpoint, string $baseUrl, array $config): array
    {
        $parsedUrl = parse_url($baseUrl);
        $host = $parsedUrl['host'] ?? 'localhost';
        $port = isset($parsedUrl['port']) ? (string) $parsedUrl['port'] : null;
        $protocol = $parsedUrl['scheme'] ?? 'http';

        $pathSegments = array_values(array_filter(
            explode('/', ltrim($endpoint->uri, '/'))
        ));

        // URL path'teki {param} → :param dönüşümü (Postman formatı)
        $pathSegments = array_map(function ($segment) {
            if (preg_match('/^\{(\w+)\??\}$/', $segment, $m)) {
                return ':' . $m[1];
            }
            return $segment;
        }, $pathSegments);

        $request = [
            'name' => $endpoint->description ?? ($endpoint->method . ' ' . $endpoint->uri),
            'request' => [
                'method' => $endpoint->method,
                'header' => $this->buildHeaders($endpoint, $config),
                'url' => [
                    'raw' => '{{base_url}}' . $endpoint->uri,
                    'protocol' => $protocol,
                    'host' => ['{{base_url}}'],
                    'path' => $pathSegments,
                ],
            ],
            'response' => $this->buildExampleResponses($endpoint),
        ];

        // Auth
        if ($endpoint->requiresAuth) {
            $request['request']['auth'] = [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{auth_token}}',
                        'type' => 'string',
                    ],
                ],
            ];
        } else {
            $request['request']['auth'] = ['type' => 'noauth'];
        }

        // Body
        $exampleBody = $endpoint->getExampleRequestBody();
        if ($exampleBody && in_array($endpoint->method, ['POST', 'PUT', 'PATCH'])) {
            $request['request']['body'] = [
                'mode' => 'raw',
                'raw' => json_encode($exampleBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'options' => [
                    'raw' => [
                        'language' => 'json',
                    ],
                ],
            ];
        }

        // URL variables (path params)
        if (!empty($endpoint->urlParameters)) {
            $request['request']['url']['variable'] = array_map(function (ParameterDoc $param) {
                return [
                    'key' => $param->name,
                    'value' => (string) ($param->example ?? '1'),
                    'description' => $param->description ?? '',
                ];
            }, $endpoint->urlParameters);
        }

        return $request;
    }

    /**
     * Request header'larını oluştur.
     */
    protected function buildHeaders(EndpointDoc $endpoint, array $config): array
    {
        $headers = [
            [
                'key' => 'Accept',
                'value' => 'application/json',
                'type' => 'text',
            ],
            [
                'key' => 'Content-Type',
                'value' => 'application/json',
                'type' => 'text',
            ],
        ];

        return $headers;
    }

    /**
     * Örnek response objeleri oluştur.
     */
    protected function buildExampleResponses(EndpointDoc $endpoint): array
    {
        $responses = [];

        foreach ($endpoint->responses as $response) {
            $responses[] = [
                'name' => $response->message ?? ($response->isSuccess ? 'Başarılı' : 'Hata'),
                'originalRequest' => [],
                'status' => $response->isSuccess ? 'OK' : 'Error',
                'code' => $response->statusCode,
                'header' => [
                    [
                        'key' => 'Content-Type',
                        'value' => 'application/json',
                    ],
                ],
                'body' => json_encode($response->toResponseExample(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ];
        }

        return $responses;
    }

    /**
     * Basit UUID üretici.
     */
    protected function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
