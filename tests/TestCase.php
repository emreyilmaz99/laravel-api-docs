<?php

namespace LaravelApiDocs\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use LaravelApiDocs\ApiDocsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ApiDocsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('api-docs.enabled', true);
        $app['config']->set('api-docs.route_prefix', ['api']);
    }
}
