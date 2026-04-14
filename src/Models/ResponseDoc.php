<?php

namespace LaravelApiDocs\Models;

class ResponseDoc
{
    public function __construct(
        public int $statusCode = 200,
        public bool $isSuccess = true,
        public ?string $message = null,
        public mixed $data = null,
        public ?string $description = null,
    ) {}

    public function toArray(): array
    {
        return [
            'statusCode' => $this->statusCode,
            'isSuccess' => $this->isSuccess,
            'message' => $this->message,
            'data' => $this->data,
            'description' => $this->description,
        ];
    }

    public function toResponseExample(?array $wrapper = null): array
    {
        $wrapper = $wrapper ?? config('api-docs.response_wrapper');

        if (empty($wrapper)) {
            // Wrapper yoksa ham response döndür
            return $this->data !== null ? (is_array($this->data) ? $this->data : ['data' => $this->data]) : [];
        }

        $example = [];
        foreach ($wrapper as $key => $type) {
            $example[$key] = match ($key) {
                'isSuccess', 'success', 'status' => $this->isSuccess,
                'message', 'msg' => $this->message ?? ($this->isSuccess ? 'İşlem başarılı' : 'İşlem başarısız'),
                'data', 'result', 'payload' => $this->data,
                'statusCode', 'status_code', 'code' => $this->statusCode,
                default => $this->guessValueByType($type),
            };
        }

        return $example;
    }

    protected function guessValueByType(string $type): mixed
    {
        return match ($type) {
            'boolean', 'bool' => true,
            'string' => '',
            'integer', 'int' => 0,
            'array' => [],
            'object' => new \stdClass(),
            default => null,
        };
    }
}
