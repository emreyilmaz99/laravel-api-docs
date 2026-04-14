<?php

namespace LaravelApiDocs\Parsers;

use Illuminate\Foundation\Http\FormRequest;
use LaravelApiDocs\Models\ParameterDoc;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class FormRequestParser
{
    /**
     * Controller method'undan FormRequest class'ını bul ve parametreleri parse et.
     *
     * @return ParameterDoc[]
     */
    public function parse(string $controllerClass, string $methodName): array
    {
        $formRequestClass = $this->findFormRequestClass($controllerClass, $methodName);

        if (!$formRequestClass) {
            return [];
        }

        return $this->parseFormRequest($formRequestClass);
    }

    /**
     * Controller method'unun FormRequest class adını döndür.
     */
    public function getFormRequestClass(string $controllerClass, string $methodName): ?string
    {
        return $this->findFormRequestClass($controllerClass, $methodName);
    }

    /**
     * Controller method'undaki type-hint'ten FormRequest class'ını bul.
     */
    protected function findFormRequestClass(string $controllerClass, string $methodName): ?string
    {
        if (!class_exists($controllerClass)) {
            return null;
        }

        try {
            $reflection = new ReflectionMethod($controllerClass, $methodName);
        } catch (\ReflectionException) {
            return null;
        }

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $typeName = $type->getName();

            if (class_exists($typeName) && is_subclass_of($typeName, FormRequest::class)) {
                return $typeName;
            }
        }

        return null;
    }

    /**
     * FormRequest class'ından validation kurallarını parse et.
     *
     * @return ParameterDoc[]
     */
    protected function parseFormRequest(string $formRequestClass): array
    {
        if (!class_exists($formRequestClass)) {
            return [];
        }

        $rules = $this->extractRules($formRequestClass);
        $messages = $this->extractMessages($formRequestClass);

        $parameters = [];
        foreach ($rules as $fieldName => $fieldRules) {
            $parameters[] = $this->buildParameterDoc($fieldName, $fieldRules, $messages);
        }

        return $parameters;
    }

    /**
     * FormRequest'ten rules() çıktısını al.
     */
    protected function extractRules(string $formRequestClass): array
    {
        try {
            $reflection = new ReflectionClass($formRequestClass);

            if (!$reflection->hasMethod('rules')) {
                return [];
            }

            // FormRequest instance oluşturmadan rules() metodunu çağırmaya çalış
            $instance = $reflection->newInstanceWithoutConstructor();
            $rulesMethod = $reflection->getMethod('rules');
            $rulesMethod->setAccessible(true);

            $rules = $rulesMethod->invoke($instance);

            return is_array($rules) ? $rules : [];
        } catch (\Throwable) {
            // rules() çağrılamazsa, dosyadan statik analiz yap
            return $this->extractRulesFromSource($formRequestClass);
        }
    }

    /**
     * Kaynak koddan rules() parse et (fallback).
     */
    protected function extractRulesFromSource(string $formRequestClass): array
    {
        try {
            $reflection = new ReflectionClass($formRequestClass);
            $fileName = $reflection->getFileName();

            if (!$fileName || !file_exists($fileName)) {
                return [];
            }

            $content = file_get_contents($fileName);

            // rules() metodundaki return array'i bul
            if (preg_match('/function\s+rules\s*\(\s*\).*?return\s*\[(.*?)\]\s*;/s', $content, $matches)) {
                return $this->parseRulesArrayString($matches[1]);
            }
        } catch (\Throwable) {
            // Sessizce geç
        }

        return [];
    }

    /**
     * Statik rules array string'ini parse et.
     */
    protected function parseRulesArrayString(string $arrayContent): array
    {
        $rules = [];

        // 'field_name' => 'required|string|max:255' veya ['required', 'string'] formatlarını yakala
        preg_match_all(
            "/['\"](\w[\w.*]*)['\"]\\s*=>\\s*(?:'([^']+)'|\"([^\"]+)\"|\[([^\]]+)\])/",
            $arrayContent,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $fieldName = $match[1];
            $ruleString = $match[2] ?: ($match[3] ?: $match[4]);

            if (isset($match[4]) && $match[4]) {
                // Array format: ['required', 'string']
                preg_match_all("/['\"]([^'\"]+)['\"]/", $match[4], $ruleMatches);
                $rules[$fieldName] = $ruleMatches[1] ?? [];
            } else {
                $rules[$fieldName] = $ruleString;
            }
        }

        return $rules;
    }

    /**
     * FormRequest'ten messages() çıktısını al.
     */
    protected function extractMessages(string $formRequestClass): array
    {
        try {
            $reflection = new ReflectionClass($formRequestClass);

            if (!$reflection->hasMethod('messages')) {
                return [];
            }

            $instance = $reflection->newInstanceWithoutConstructor();
            $messagesMethod = $reflection->getMethod('messages');
            $messagesMethod->setAccessible(true);

            $messages = $messagesMethod->invoke($instance);

            return is_array($messages) ? $messages : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Tek bir alan için ParameterDoc oluştur.
     */
    protected function buildParameterDoc(string $fieldName, mixed $fieldRules, array $messages): ParameterDoc
    {
        $rules = $this->normalizeRules($fieldRules);

        $param = new ParameterDoc(
            name: $fieldName,
            type: $this->extractType($rules),
            required: $this->isRequired($rules),
            nullable: in_array('nullable', $rules),
            description: $this->extractDescription($fieldName, $messages),
            rules: $rules,
        );

        $param->max = $this->extractNumericRule($rules, 'max');
        $param->min = $this->extractNumericRule($rules, 'min');
        $param->enumValues = $this->extractEnumValues($rules);
        $param->foreignKey = $this->extractForeignKey($rules);
        $param->example = $this->generateExample($param);

        return $param;
    }

    /**
     * Kuralları array'e normalize et.
     */
    protected function normalizeRules(mixed $rules): array
    {
        if (is_string($rules)) {
            return explode('|', $rules);
        }

        if (is_array($rules)) {
            $normalized = [];
            foreach ($rules as $rule) {
                if (is_string($rule)) {
                    $normalized[] = $rule;
                } elseif (is_object($rule)) {
                    $normalized[] = get_class($rule) . ' (object rule)';
                }
            }
            return $normalized;
        }

        return [];
    }

    /**
     * Kurallardan tip bilgisini çıkar.
     */
    protected function extractType(array $rules): string
    {
        $typeMap = [
            'string' => 'string',
            'integer' => 'integer',
            'int' => 'integer',
            'numeric' => 'numeric',
            'boolean' => 'boolean',
            'bool' => 'boolean',
            'array' => 'array',
            'file' => 'file',
            'image' => 'image',
            'date' => 'date',
            'email' => 'string (email)',
            'url' => 'string (url)',
            'json' => 'json',
        ];

        foreach ($rules as $rule) {
            $ruleName = explode(':', $rule)[0];
            if (isset($typeMap[$ruleName])) {
                return $typeMap[$ruleName];
            }
        }

        // exists kuralı varsa integer olma ihtimali yüksek (foreign key)
        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'exists:')) {
                return 'integer';
            }
        }

        return 'string';
    }

    /**
     * Zorunlu mu kontrol et.
     */
    protected function isRequired(array $rules): bool
    {
        foreach ($rules as $rule) {
            if ($rule === 'required' || str_starts_with($rule, 'required_')) {
                return true;
            }
        }
        return false;
    }

    /**
     * messages() çıktısından alan açıklamasını bul.
     */
    protected function extractDescription(string $fieldName, array $messages): ?string
    {
        // Önce field.required, field.string gibi anahtarları dene
        $descriptionKeys = [
            $fieldName . '.required',
            $fieldName . '.string',
            $fieldName . '.integer',
            $fieldName . '.max',
        ];

        foreach ($descriptionKeys as $key) {
            if (isset($messages[$key])) {
                return $messages[$key];
            }
        }

        return null;
    }

    /**
     * max:255, min:1 gibi kurallardan sayısal değeri çıkar.
     */
    protected function extractNumericRule(array $rules, string $ruleName): ?int
    {
        foreach ($rules as $rule) {
            if (str_starts_with($rule, $ruleName . ':')) {
                $value = substr($rule, strlen($ruleName) + 1);
                return is_numeric($value) ? (int) $value : null;
            }
        }
        return null;
    }

    /**
     * in:a,b,c kuralından enum değerlerini çıkar.
     */
    protected function extractEnumValues(array $rules): array
    {
        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'in:')) {
                return explode(',', substr($rule, 3));
            }
        }
        return [];
    }

    /**
     * exists:table,column kuralından foreign key bilgisini çıkar.
     */
    protected function extractForeignKey(array $rules): ?string
    {
        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'exists:')) {
                return substr($rule, 7);
            }
        }
        return null;
    }

    /**
     * Parametreye göre örnek değer üret.
     */
    protected function generateExample(ParameterDoc $param): mixed
    {
        if (!empty($param->enumValues)) {
            return $param->enumValues[0];
        }

        return match ($param->type) {
            'integer' => $param->min ?? 1,
            'numeric' => $param->min ?? 1.5,
            'boolean' => true,
            'array' => [],
            'date' => '2025-01-15',
            'string (email)' => 'user@example.com',
            'string (url)' => 'https://example.com',
            'file', 'image' => '(binary)',
            'json' => '{}',
            default => $this->generateStringExample($param),
        };
    }

    /**
     * String tipi için örnek değer üret.
     */
    protected function generateStringExample(ParameterDoc $param): string
    {
        $name = $param->name;

        // Alan adına göre anlamlı örnek üret
        $examples = [
            'title' => 'Örnek Başlık',
            'name' => 'Örnek İsim',
            'description' => 'Örnek açıklama metni',
            'email' => 'user@example.com',
            'phone' => '+905551234567',
            'address' => 'Örnek Mah. Test Sok. No:1',
            'password' => '********',
            'note' => 'Örnek not',
            'content' => 'Örnek içerik',
            'message' => 'Örnek mesaj',
        ];

        foreach ($examples as $key => $value) {
            if (str_contains(strtolower($name), $key)) {
                return $value;
            }
        }

        return 'Örnek ' . str_replace('_', ' ', $name);
    }
}
