<?php

namespace LaravelApiDocs\Models;

class ParameterDoc
{
    public function __construct(
        public string $name,
        public string $type = 'string',
        public bool $required = false,
        public bool $nullable = false,
        public ?string $description = null,
        public array $rules = [],
        public ?int $max = null,
        public ?int $min = null,
        public array $enumValues = [],
        public ?string $foreignKey = null,
        public mixed $example = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'required' => $this->required,
            'nullable' => $this->nullable,
            'description' => $this->description,
            'rules' => $this->rules,
            'max' => $this->max,
            'min' => $this->min,
            'enum_values' => $this->enumValues,
            'foreign_key' => $this->foreignKey,
            'example' => $this->example,
        ];
    }
}
