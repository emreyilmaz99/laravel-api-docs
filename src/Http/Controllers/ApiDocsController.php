<?php

namespace LaravelApiDocs\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelApiDocs\ApiDocsGenerator;
use LaravelApiDocs\Renderers\JsonRenderer;
use LaravelApiDocs\Renderers\MarkdownRenderer;
use LaravelApiDocs\Renderers\PostmanRenderer;

class ApiDocsController extends Controller
{
    public function index(Request $request, ApiDocsGenerator $generator)
    {
        $data = $generator->generate();
        $config = config('api-docs');

        // Locale ayarla
        $locale = $config['locale'] ?? 'tr';
        app()->setLocale($locale);

        $assetsPath = dirname(__DIR__, 3) . '/resources/assets';
        $inlineCss = file_get_contents($assetsPath . '/css/docs.css');
        $inlineJs = file_get_contents($assetsPath . '/js/docs.js');

        return view('api-docs::index', [
            'groups' => $data['groups'],
            'endpoints' => $data['endpoints'],
            'config' => $config,
            'theme' => $config['theme'] ?? 'dark',
            'baseUrl' => $config['base_url'] ?? url('/'),
            'inlineCss' => $inlineCss,
            'inlineJs' => $inlineJs,
        ]);
    }

    public function json(ApiDocsGenerator $generator)
    {
        return response($generator->toJson(), 200, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function openapi(ApiDocsGenerator $generator)
    {
        $data = $generator->generate();
        $config = config('api-docs', []);
        $renderer = new JsonRenderer();

        return response($renderer->render($data['groups'], $config), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="openapi.json"',
        ]);
    }

    public function postman(ApiDocsGenerator $generator)
    {
        $data = $generator->generate();
        $config = config('api-docs', []);
        $renderer = new PostmanRenderer();

        return response($renderer->render($data['groups'], $config), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="postman-collection.json"',
        ]);
    }

    public function markdown(ApiDocsGenerator $generator)
    {
        $data = $generator->generate();
        $config = config('api-docs', []);
        $renderer = new MarkdownRenderer();

        return response($renderer->render($data['groups'], $config), 200, [
            'Content-Type' => 'text/markdown',
            'Content-Disposition' => 'attachment; filename="api-docs.md"',
        ]);
    }
}
