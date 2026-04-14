<?php

namespace LaravelApiDocs\Renderers;

use LaravelApiDocs\Models\EndpointDoc;
use LaravelApiDocs\Models\ParameterDoc;

class MarkdownRenderer
{
    /**
     * Markdown formatında dokümantasyon üret.
     */
    public function render(array $groups, array $config = []): string
    {
        $appName = config('app.name', 'API');
        $baseUrl = $config['base_url'] ?? 'http://localhost:8000';

        $md = "# {$appName} — API Dokümantasyonu\n\n";
        $md .= "> Otomatik üretilmiş API dokümantasyonu\n\n";
        $md .= "**Base URL:** `{$baseUrl}`\n\n";

        // İçindekiler
        $md .= "## İçindekiler\n\n";
        foreach ($groups as $group) {
            $anchor = $this->slugify($group['name']);
            $md .= "- [{$group['name']}](#{$anchor}) ({$this->countEndpoints($group)} endpoint)\n";
        }
        $md .= "\n---\n\n";

        // Gruplar
        foreach ($groups as $group) {
            $md .= $this->renderGroup($group, $baseUrl, $config);
        }

        return $md;
    }

    /**
     * Tek bir grubu render et.
     */
    protected function renderGroup(array $group, string $baseUrl, array $config): string
    {
        $md = "## {$group['name']}\n\n";

        foreach ($group['endpoints'] as $endpoint) {
            $md .= $this->renderEndpoint($endpoint, $baseUrl, $config);
        }

        return $md;
    }

    /**
     * Tek bir endpoint'i render et.
     */
    protected function renderEndpoint(EndpointDoc $endpoint, string $baseUrl, array $config): string
    {
        $md = "### `{$endpoint->method}` {$endpoint->uri}\n\n";

        if ($endpoint->description) {
            $md .= "{$endpoint->description}\n\n";
        }

        // Meta bilgiler
        $meta = [];
        if ($endpoint->requiresAuth) {
            $meta[] = "🔒 **Auth:** " . ucfirst($endpoint->authType ?? 'Required');
        }
        if ($endpoint->permission) {
            $meta[] = "🛡️ **Permission:** `{$endpoint->permission}`";
        }
        if (!empty($endpoint->middleware)) {
            $meta[] = "⚙️ **Middleware:** " . implode(', ', array_map(fn($m) => "`{$m}`", $endpoint->middleware));
        }

        if (!empty($meta)) {
            $md .= implode(" | ", $meta) . "\n\n";
        }

        // URL Parameters
        if (!empty($endpoint->urlParameters)) {
            $md .= "#### URL Parameters\n\n";
            $md .= $this->renderParamsTable($endpoint->urlParameters);
        }

        // Body Parameters
        if (!empty($endpoint->parameters)) {
            $md .= "#### Body Parameters\n\n";
            $md .= $this->renderParamsTable($endpoint->parameters);

            // Örnek body
            $exampleBody = $endpoint->getExampleRequestBody();
            if ($exampleBody) {
                $md .= "**Örnek Request Body:**\n\n";
                $md .= "```json\n";
                $md .= json_encode($exampleBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $md .= "\n```\n\n";
            }
        }

        // Responses
        if (!empty($endpoint->responses)) {
            $md .= "#### Responses\n\n";
            foreach ($endpoint->responses as $response) {
                $icon = $response->isSuccess ? '✅' : '❌';
                $md .= "**{$icon} {$response->statusCode}** — {$response->message}\n\n";
                $md .= "```json\n";
                $md .= json_encode($response->toResponseExample(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $md .= "\n```\n\n";
            }
        }

        // cURL örneği
        $md .= "#### Örnek Request (cURL)\n\n";
        $md .= "```bash\n";
        $md .= $this->buildCurlExample($endpoint, $baseUrl, $config);
        $md .= "\n```\n\n";

        $md .= "---\n\n";

        return $md;
    }

    /**
     * Parametre tablosu Markdown'ı oluştur.
     */
    protected function renderParamsTable(array $parameters): string
    {
        $md = "| Alan | Tip | Zorunlu | Açıklama |\n";
        $md .= "|------|-----|---------|----------|\n";

        foreach ($parameters as $param) {
            /** @var ParameterDoc $param */
            $required = $param->required ? '✓ Evet' : 'Hayır';
            $desc = $param->description ?? '';

            $extras = [];
            if (!empty($param->enumValues)) {
                $extras[] = 'Değerler: `' . implode('`, `', $param->enumValues) . '`';
            }
            if ($param->max !== null) {
                $extras[] = "max: {$param->max}";
            }
            if ($param->min !== null) {
                $extras[] = "min: {$param->min}";
            }
            if ($param->foreignKey) {
                $extras[] = "ref: `{$param->foreignKey}`";
            }
            if ($param->nullable) {
                $extras[] = "nullable";
            }

            if (!empty($extras)) {
                $desc .= ($desc ? ' — ' : '') . implode(', ', $extras);
            }

            $md .= "| `{$param->name}` | `{$param->type}` | {$required} | {$desc} |\n";
        }

        $md .= "\n";
        return $md;
    }

    /**
     * cURL örneği oluştur.
     */
    protected function buildCurlExample(EndpointDoc $endpoint, string $baseUrl, array $config): string
    {
        $fullUrl = rtrim($baseUrl, '/') . $endpoint->uri;
        $curl = "curl -X {$endpoint->method} \\\n  '{$fullUrl}'";

        if ($endpoint->requiresAuth) {
            $header = $config['default_auth_header'] ?? 'Authorization';
            $prefix = $config['default_auth_prefix'] ?? 'Bearer';
            $curl .= " \\\n  -H '{$header}: {$prefix} {token}'";
        }

        $curl .= " \\\n  -H 'Content-Type: application/json'";
        $curl .= " \\\n  -H 'Accept: application/json'";

        $exampleBody = $endpoint->getExampleRequestBody();
        if ($exampleBody && in_array($endpoint->method, ['POST', 'PUT', 'PATCH'])) {
            $json = json_encode($exampleBody, JSON_UNESCAPED_UNICODE);
            $curl .= " \\\n  -d '{$json}'";
        }

        return $curl;
    }

    /**
     * String'i slug'a çevir.
     */
    protected function slugify(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Gruptaki endpoint sayısını döndür.
     */
    protected function countEndpoints(array $group): int
    {
        return count($group['endpoints'] ?? []);
    }
}
