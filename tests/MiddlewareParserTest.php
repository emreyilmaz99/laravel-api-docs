<?php

namespace LaravelApiDocs\Tests;

use LaravelApiDocs\Parsers\MiddlewareParser;

class MiddlewareParserTest extends TestCase
{
    protected MiddlewareParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new MiddlewareParser();
    }

    public function test_detects_auth_middleware(): void
    {
        $result = $this->parser->parse(['auth:sanctum']);

        $this->assertTrue($result['requires_auth']);
        $this->assertEquals('sanctum', $result['auth_type']);
    }

    public function test_detects_plain_auth_middleware(): void
    {
        $result = $this->parser->parse(['auth']);

        $this->assertTrue($result['requires_auth']);
        $this->assertEquals('default', $result['auth_type']);
    }

    public function test_no_auth_when_empty(): void
    {
        $result = $this->parser->parse(['throttle:60,1']);

        $this->assertFalse($result['requires_auth']);
        $this->assertNull($result['auth_type']);
    }

    public function test_detects_class_based_auth_middleware(): void
    {
        $result = $this->parser->parse(['App\\Http\\Middleware\\Authenticate:sanctum']);

        $this->assertTrue($result['requires_auth']);
        $this->assertEquals('sanctum', $result['auth_type']);
    }

    public function test_detects_class_based_auth_without_param(): void
    {
        $result = $this->parser->parse(['App\\Http\\Middleware\\Authenticate']);

        $this->assertTrue($result['requires_auth']);
        $this->assertEquals('default', $result['auth_type']);
    }

    public function test_detects_permission(): void
    {
        $result = $this->parser->parse(['auth:sanctum', 'can:manage-products']);

        $this->assertEquals('manage-products', $result['permission']);
    }

    public function test_detects_class_based_permission(): void
    {
        $result = $this->parser->parse(['App\\Http\\Middleware\\CheckPermission:admin']);

        $this->assertEquals('admin', $result['permission']);
    }

    public function test_no_permission_when_absent(): void
    {
        $result = $this->parser->parse(['auth:sanctum']);

        $this->assertNull($result['permission']);
    }
}
