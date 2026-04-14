<?php

namespace LaravelApiDocs\Models;

class EndpointDoc
{
    public function __construct(
        public string $method = 'GET',
        public string $uri = '',
        public ?string $name = null,
        public ?string $groupName = null,
        public ?string $groupPrefix = null,
        public ?string $controller = null,
        public ?string $controllerMethod = null,
        public ?string $description = null,
        public array $middleware = [],
        public bool $requiresAuth = false,
        public ?string $authType = null,
        public ?string $permission = null,

        /** @var ParameterDoc[] */
        public array $parameters = [],

        /** @var ParameterDoc[] */
        public array $urlParameters = [],

        /** @var ResponseDoc[] */
        public array $responses = [],

        public ?string $formRequestClass = null,
    ) {}

    public function getMethodColor(): string
    {
        return match (strtoupper($this->method)) {
            'GET' => '#22c55e',
            'POST' => '#3b82f6',
            'PUT', 'PATCH' => '#f97316',
            'DELETE' => '#ef4444',
            default => '#6b7280',
        };
    }

    public function getMethodBadgeClass(): string
    {
        return match (strtoupper($this->method)) {
            'GET' => 'method-get',
            'POST' => 'method-post',
            'PUT', 'PATCH' => 'method-put',
            'DELETE' => 'method-delete',
            default => 'method-default',
        };
    }

    public function getExampleRequestBody(): ?array
    {
        if (empty($this->parameters)) {
            return null;
        }

        $body = [];
        foreach ($this->parameters as $param) {
            if ($param->example !== null) {
                $body[$param->name] = $param->example;
            } else {
                $body[$param->name] = $this->generateExampleValue($param);
            }
        }

        return $body;
    }

    private function generateExampleValue(ParameterDoc $param): mixed
    {
        if (!empty($param->enumValues)) {
            return $param->enumValues[0];
        }

        return match ($param->type) {
            'integer', 'int', 'numeric' => 1,
            'boolean', 'bool' => true,
            'array' => [],
            'date' => '2025-01-15',
            'email' => 'example@mail.com',
            'url' => 'https://example.com',
            'file', 'image' => '(binary)',
            default => $param->name . '_value',
        };
    }

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'uri' => $this->uri,
            'name' => $this->name,
            'group_name' => $this->groupName,
            'group_prefix' => $this->groupPrefix,
            'controller' => $this->controller,
            'controller_method' => $this->controllerMethod,
            'description' => $this->description,
            'middleware' => $this->middleware,
            'requires_auth' => $this->requiresAuth,
            'auth_type' => $this->authType,
            'permission' => $this->permission,
            'parameters' => array_map(fn(ParameterDoc $p) => $p->toArray(), $this->parameters),
            'url_parameters' => array_map(fn(ParameterDoc $p) => $p->toArray(), $this->urlParameters),
            'responses' => array_map(fn(ResponseDoc $r) => $r->toArray(), $this->responses),
            'form_request_class' => $this->formRequestClass,
        ];
    }
}
