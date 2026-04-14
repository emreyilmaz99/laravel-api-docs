<?php

namespace LaravelApiDocs\Renderers;

use LaravelApiDocs\Models\EndpointDoc;
use LaravelApiDocs\Models\ParameterDoc;
use LaravelApiDocs\Models\ResponseDoc;

class JsonRenderer
{
    /**
     * OpenAPI 3.0 spesifikasyonuna uygun JSON üret.
     */
    public function render(array $groups, array $config = []): string
    {
        $openApi = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('app.name', 'API') . ' Documentation',
                'description' => 'Auto-generated API documentation',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => $config['base_url'] ?? 'http://localhost:8000',
                    'description' => 'API Server',
                ],
            ],
            'paths' => $this->buildPaths($groups),
            'components' => $this->buildComponents($groups, $config),
            'tags' => $this->buildTags($groups),
        ];

        return json_encode($openApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Paths objesi oluştur.
     */
    protected function buildPaths(array $groups): array
    {
        $paths = [];

        foreach ($groups as $group) {
            foreach ($group['endpoints'] as $endpoint) {
                /** @var EndpointDoc $endpoint */
                $path = $this->convertUriToOpenApiPath($endpoint->uri);
                $method = strtolower($endpoint->method);

                if (!isset($paths[$path])) {
                    $paths[$path] = [];
                }

                $operation = [
                    'tags' => [$endpoint->groupName ?? 'General'],
                    'summary' => $endpoint->description ?? '',
                    'operationId' => $this->generateOperationId($endpoint),
                ];

                // Path parameters
                $pathParams = $this->buildPathParameters($endpoint);
                if (!empty($pathParams)) {
                    $operation['parameters'] = $pathParams;
                }

                // Request body
                if (!empty($endpoint->parameters) && in_array($method, ['post', 'put', 'patch'])) {
                    $operation['requestBody'] = $this->buildRequestBody($endpoint);
                }

                // Responses
                $operation['responses'] = $this->buildResponses($endpoint);

                // Security
                if ($endpoint->requiresAuth) {
                    $operation['security'] = [
                        ['bearerAuth' => []],
                    ];
                }

                $paths[$path][$method] = $operation;
            }
        }

        return $paths;
    }

    /**
     * Laravel URI'sini OpenAPI path formatına çevir.
     * /api/product/{id} → /api/product/{id} (zaten aynı format)
     */
    protected function convertUriToOpenApiPath(string $uri): string
    {
        // {param?} → {param} (optional parametreleri de dahil et)
        return preg_replace('/\{(\w+)\?\}/', '{$1}', $uri);
    }

    /**
     * Benzersiz operationId üret.
     */
    protected function generateOperationId(EndpointDoc $endpoint): string
    {
        $parts = [];
        $parts[] = strtolower($endpoint->method);

        if ($endpoint->controllerMethod) {
            $parts[] = $endpoint->controllerMethod;
        }

        if ($endpoint->groupPrefix) {
            $parts[] = $endpoint->groupPrefix;
        }

        return implode('_', $parts);
    }

    /**
     * Path parameter'leri oluştur.
     */
    protected function buildPathParameters(EndpointDoc $endpoint): array
    {
        $params = [];

        foreach ($endpoint->urlParameters as $param) {
            $params[] = [
                'name' => $param->name,
                'in' => 'path',
                'required' => $param->required,
                'description' => $param->description ?? '',
                'schema' => [
                    'type' => $this->mapType($param->type),
                ],
            ];
        }

        return $params;
    }

    /**
     * Request body şemasını oluştur.
     */
    protected function buildRequestBody(EndpointDoc $endpoint): array
    {
        $properties = [];
        $required = [];

        foreach ($endpoint->parameters as $param) {
            $property = $this->buildPropertySchema($param);
            $properties[$param->name] = $property;

            if ($param->required) {
                $required[] = $param->name;
            }
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
        ];

        if (!empty($required)) {
            $schema['required'] = $required;
        }

        return [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => $schema,
                    'example' => $endpoint->getExampleRequestBody(),
                ],
            ],
        ];
    }

    /**
     * Tek bir property şeması oluştur.
     */
    protected function buildPropertySchema(ParameterDoc $param): array
    {
        $schema = [
            'type' => $this->mapType($param->type),
        ];

        if ($param->description) {
            $schema['description'] = $param->description;
        }

        if ($param->max !== null) {
            if ($schema['type'] === 'string') {
                $schema['maxLength'] = $param->max;
            } else {
                $schema['maximum'] = $param->max;
            }
        }

        if ($param->min !== null) {
            if ($schema['type'] === 'string') {
                $schema['minLength'] = $param->min;
            } else {
                $schema['minimum'] = $param->min;
            }
        }

        if (!empty($param->enumValues)) {
            $schema['enum'] = $param->enumValues;
        }

        if ($param->nullable) {
            $schema['nullable'] = true;
        }

        if ($param->example !== null) {
            $schema['example'] = $param->example;
        }

        return $schema;
    }

    /**
     * Response objeleri oluştur.
     */
    protected function buildResponses(EndpointDoc $endpoint): array
    {
        $responses = [];

        if (!empty($endpoint->responses)) {
            foreach ($endpoint->responses as $response) {
                $statusCode = (string) $response->statusCode;
                $responses[$statusCode] = [
                    'description' => $response->message ?? ($response->isSuccess ? 'Başarılı' : 'Hata'),
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'isSuccess' => ['type' => 'boolean'],
                                    'message' => ['type' => 'string'],
                                    'data' => ['type' => 'object'],
                                    'statusCode' => ['type' => 'integer'],
                                ],
                            ],
                            'example' => $response->toResponseExample(),
                        ],
                    ],
                ];
            }
        } else {
            $responses['200'] = [
                'description' => 'Başarılı',
            ];
        }

        return $responses;
    }

    /**
     * Components bölümünü oluştur.
     */
    protected function buildComponents(array $groups, array $config): array
    {
        return [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => ucfirst($config['default_auth_prefix'] ?? 'Bearer'),
                    'description' => 'API token ile kimlik doğrulama',
                ],
            ],
        ];
    }

    /**
     * Tag listesi oluştur.
     */
    protected function buildTags(array $groups): array
    {
        $tags = [];
        foreach ($groups as $group) {
            $tags[] = [
                'name' => $group['name'],
                'description' => $group['name'] . ' endpoint\'leri',
            ];
        }
        return $tags;
    }

    /**
     * Dahili tip adını OpenAPI tipine eşle.
     */
    protected function mapType(string $type): string
    {
        return match ($type) {
            'integer', 'int', 'numeric' => 'integer',
            'boolean', 'bool' => 'boolean',
            'array' => 'array',
            'file', 'image' => 'string',
            'date' => 'string',
            'json' => 'object',
            default => 'string',
        };
    }
}
