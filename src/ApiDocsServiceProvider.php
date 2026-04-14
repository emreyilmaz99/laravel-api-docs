<?php

namespace LaravelApiDocs;

use Illuminate\Support\ServiceProvider;

class ApiDocsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/api-docs.php', 'api-docs');

        $this->app->singleton(ApiDocsGenerator::class, function ($app) {
            return new ApiDocsGenerator(
                $app['router'],
                $app['config']->get('api-docs', [])
            );
        });
    }

    public function boot(): void
    {
        if (!$this->app['config']->get('api-docs.enabled', true)) {
            return;
        }

        // Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'api-docs');

        // Translations
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'api-docs');

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Publishable
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__ . '/../config/api-docs.php' => config_path('api-docs.php'),
            ], 'api-docs-config');

            // Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/api-docs'),
            ], 'api-docs-views');

            // Assets
            $this->publishes([
                __DIR__ . '/../resources/assets' => public_path('vendor/api-docs'),
            ], 'api-docs-assets');

            // Translations
            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/api-docs'),
            ], 'api-docs-lang');

            // Commands
            $this->commands([
                Console\GenerateDocsCommand::class,
                Console\ClearCacheCommand::class,
            ]);
        }
    }
}
