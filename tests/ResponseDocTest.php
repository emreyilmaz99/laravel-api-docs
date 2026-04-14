<?php

namespace LaravelApiDocs\Tests;

use LaravelApiDocs\Models\ResponseDoc;

class ResponseDocTest extends TestCase
{
    public function test_to_response_example_uses_default_wrapper(): void
    {
        $this->app['config']->set('api-docs.response_wrapper', [
            'isSuccess' => 'boolean',
            'message' => 'string',
            'data' => 'mixed',
            'statusCode' => 'integer',
        ]);

        $response = new ResponseDoc(200, true, 'OK', ['id' => 1]);
        $example = $response->toResponseExample();

        $this->assertTrue($example['isSuccess']);
        $this->assertEquals('OK', $example['message']);
        $this->assertEquals(['id' => 1], $example['data']);
        $this->assertEquals(200, $example['statusCode']);
    }

    public function test_to_response_example_uses_custom_wrapper(): void
    {
        $wrapper = [
            'success' => 'boolean',
            'msg' => 'string',
            'result' => 'mixed',
            'code' => 'integer',
        ];

        $response = new ResponseDoc(201, true, 'Created', ['id' => 5]);
        $example = $response->toResponseExample($wrapper);

        $this->assertTrue($example['success']);
        $this->assertEquals('Created', $example['msg']);
        $this->assertEquals(['id' => 5], $example['result']);
        $this->assertEquals(201, $example['code']);
    }

    public function test_to_response_example_without_wrapper(): void
    {
        $response = new ResponseDoc(200, true, 'OK', ['id' => 1]);
        $example = $response->toResponseExample([]);

        $this->assertEquals(['id' => 1], $example);
    }

    public function test_to_array(): void
    {
        $response = new ResponseDoc(404, false, 'Not found', null, 'Resource not found');

        $arr = $response->toArray();
        $this->assertEquals(404, $arr['statusCode']);
        $this->assertFalse($arr['isSuccess']);
        $this->assertEquals('Not found', $arr['message']);
        $this->assertEquals('Resource not found', $arr['description']);
    }
}
