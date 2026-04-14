<?php

namespace LaravelApiDocs\Tests;

use LaravelApiDocs\Parsers\ResponseParser;
use LaravelApiDocs\Models\ResponseDoc;

class ResponseParserTest extends TestCase
{
    protected ResponseParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new ResponseParser([]);
    }

    public function test_returns_default_responses_for_index(): void
    {
        $responses = $this->parser->parse('NonExistentClass', 'index');

        $this->assertCount(1, $responses);
        $this->assertEquals(200, $responses[0]->statusCode);
        $this->assertTrue($responses[0]->isSuccess);
    }

    public function test_returns_default_responses_for_store(): void
    {
        $responses = $this->parser->parse('NonExistentClass', 'store');

        $this->assertCount(2, $responses);
        $this->assertEquals(201, $responses[0]->statusCode);
        $this->assertEquals(422, $responses[1]->statusCode);
    }

    public function test_returns_default_responses_for_destroy(): void
    {
        $responses = $this->parser->parse('NonExistentClass', 'destroy');

        $this->assertCount(2, $responses);
        $this->assertEquals(200, $responses[0]->statusCode);
        $this->assertEquals(404, $responses[1]->statusCode);
    }

    public function test_returns_generic_response_for_unknown_method(): void
    {
        $responses = $this->parser->parse('NonExistentClass', 'customAction');

        $this->assertCount(1, $responses);
        $this->assertEquals(200, $responses[0]->statusCode);
    }
}
