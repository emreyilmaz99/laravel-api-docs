<?php

namespace LaravelApiDocs\Console;

use Illuminate\Console\Command;
use LaravelApiDocs\ApiDocsGenerator;
use LaravelApiDocs\Renderers\JsonRenderer;
use LaravelApiDocs\Renderers\MarkdownRenderer;
use LaravelApiDocs\Renderers\PostmanRenderer;

class GenerateDocsCommand extends Command
{
    protected $signature = 'api-docs:generate
                            {--format=html : Çıktı formatı (html, json, openapi, postman, markdown)}
                            {--output= : Çıktı dosya yolu}';

    protected $description = 'API dokümantasyonunu statik dosya olarak üretir';

    public function handle(ApiDocsGenerator $generator): int
    {
        $format = $this->option('format');
        $output = $this->option('output');

        $this->info('API dokümantasyonu üretiliyor...');

        $data = $generator->generate();
        $config = config('api-docs', []);
        $endpointCount = count($data['endpoints']);
        $groupCount = count($data['groups']);

        $this->info("Bulunan: {$endpointCount} endpoint, {$groupCount} grup");

        switch ($format) {
            case 'json':
                $content = $generator->toJson();
                $defaultPath = base_path('api-docs.json');
                break;

            case 'openapi':
                $renderer = new JsonRenderer();
                $content = $renderer->render($data['groups'], $config);
                $defaultPath = base_path('openapi.json');
                break;

            case 'postman':
                $renderer = new PostmanRenderer();
                $content = $renderer->render($data['groups'], $config);
                $defaultPath = base_path('postman-collection.json');
                break;

            case 'markdown':
            case 'md':
                $renderer = new MarkdownRenderer();
                $content = $renderer->render($data['groups'], $config);
                $defaultPath = base_path('api-docs.md');
                break;

            case 'html':
            default:
                $assetsPath = dirname(__DIR__, 2) . '/resources/assets';
                $content = view('api-docs::index', [
                    'groups' => $data['groups'],
                    'endpoints' => $data['endpoints'],
                    'config' => $config,
                    'theme' => $config['theme'] ?? 'dark',
                    'baseUrl' => $config['base_url'] ?? 'http://localhost:8000',
                    'inlineCss' => file_get_contents($assetsPath . '/css/docs.css'),
                    'inlineJs' => file_get_contents($assetsPath . '/js/docs.js'),
                ])->render();
                $defaultPath = base_path('api-docs.html');
                break;
        }

        $filePath = $output ?: $defaultPath;
        file_put_contents($filePath, $content);

        $this->info("✓ Dokümantasyon oluşturuldu: {$filePath}");
        $this->newLine();
        $this->line("Kullanılabilir formatlar:");
        $this->line("  --format=html      Statik HTML sayfası");
        $this->line("  --format=json      Ham JSON çıktısı");
        $this->line("  --format=openapi   OpenAPI 3.0 spesifikasyonu");
        $this->line("  --format=postman   Postman Collection v2.1");
        $this->line("  --format=markdown  Markdown dokümantasyon");

        return self::SUCCESS;
    }
}
