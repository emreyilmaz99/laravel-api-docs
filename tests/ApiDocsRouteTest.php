<?php

namespace LaravelApiDocs\Tests;

class ApiDocsRouteTest extends TestCase
{
    public function test_docs_page_loads(): void
    {
        $response = $this->get('/api-docs');
        $response->assertStatus(200);
        $response->assertSee('API');
    }

    public function test_json_endpoint_returns_json(): void
    {
        $response = $this->get('/api-docs/json');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_docs_disabled_returns_404(): void
    {
        $this->app['config']->set('api-docs.enabled', false);

        // Need to re-register routes with new config
        $this->refreshApplication();
        $this->app['config']->set('api-docs.enabled', false);

        $response = $this->get('/api-docs');
        $response->assertStatus(404);
    }
}
