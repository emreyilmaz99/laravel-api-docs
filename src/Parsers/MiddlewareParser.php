<?php

namespace LaravelApiDocs\Parsers;

class MiddlewareParser
{
    /**
     * Middleware listesinden auth ve permission bilgilerini çıkar.
     */
    public function parse(array $middleware): array
    {
        return [
            'requires_auth' => $this->requiresAuth($middleware),
            'auth_type' => $this->getAuthType($middleware),
            'permission' => $this->getPermission($middleware),
        ];
    }

    /**
     * Auth middleware'i var mı kontrol et.
     */
    public function requiresAuth(array $middleware): bool
    {
        foreach ($middleware as $m) {
            $name = $this->getMiddlewareName($m);
            if (in_array($name, ['auth', 'auth:sanctum', 'auth:api', 'auth:web'])) {
                return true;
            }
            if (str_starts_with($name, 'auth:') || str_starts_with($name, 'auth.')) {
                return true;
            }

            // Class-based middleware desteği
            if ($this->isAuthMiddlewareClass($m)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Auth tipini belirle (sanctum, api, passport vs.).
     */
    public function getAuthType(array $middleware): ?string
    {
        foreach ($middleware as $m) {
            $name = $this->getMiddlewareName($m);
            if (str_starts_with($name, 'auth:')) {
                return str_replace('auth:', '', $name);
            }
            if ($name === 'auth') {
                return 'default';
            }
            if ($this->isAuthMiddlewareClass($m)) {
                return $this->detectAuthTypeFromClass($m);
            }
        }
        return null;
    }

    /**
     * Permission middleware'inden yetki bilgisini çıkar.
     */
    public function getPermission(array $middleware): ?string
    {
        foreach ($middleware as $m) {
            $name = $this->getMiddlewareName($m);

            // checkPermission:permission_name
            if (str_starts_with($name, 'checkPermission:')) {
                return str_replace('checkPermission:', '', $name);
            }

            // can:permission_name
            if (str_starts_with($name, 'can:')) {
                return str_replace('can:', '', $name);
            }

            // permission:permission_name
            if (str_starts_with($name, 'permission:')) {
                return str_replace('permission:', '', $name);
            }

            // role:role_name
            if (str_starts_with($name, 'role:')) {
                return 'role:' . str_replace('role:', '', $name);
            }
        }
        return null;
    }

    /**
     * Middleware string'inden adı çıkar (class path ise kısa adını al).
     */
    protected function getMiddlewareName(string $middleware): string
    {
        // "ClassName:param" formatını destekle
        if (str_contains($middleware, '\\')) {
            // App\Http\Middleware\Authenticate:sanctum → auth:sanctum
            $parts = explode(':', $middleware, 2);
            $className = class_basename($parts[0]);
            $param = $parts[1] ?? null;

            $alias = $this->resolveClassAlias($className);

            return $param ? "{$alias}:{$param}" : $alias;
        }

        return $middleware;
    }

    /**
     * Bilinen middleware class adlarını alias'a çevir.
     */
    protected function resolveClassAlias(string $className): string
    {
        $classAliasMap = [
            'Authenticate' => 'auth',
            'EnsureEmailIsVerified' => 'verified',
            'CheckPermission' => 'checkPermission',
            'Authorize' => 'can',
            'ThrottleRequests' => 'throttle',
            'RedirectIfAuthenticated' => 'guest',
        ];

        return $classAliasMap[$className] ?? strtolower($className);
    }

    /**
     * Class-based auth middleware mi kontrol et.
     */
    protected function isAuthMiddlewareClass(string $middleware): bool
    {
        if (!str_contains($middleware, '\\')) {
            return false;
        }

        $className = class_basename(explode(':', $middleware, 2)[0]);

        $authClasses = [
            'Authenticate',
            'EnsureTokenIsValid',
            'AuthenticateWithBasicAuth',
            'RequirePassword',
        ];

        return in_array($className, $authClasses);
    }

    /**
     * Class-based middleware'den auth tipini tespit et.
     */
    protected function detectAuthTypeFromClass(string $middleware): string
    {
        $parts = explode(':', $middleware, 2);
        return $parts[1] ?? 'default';
    }
}
