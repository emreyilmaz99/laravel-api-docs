<?php

namespace LaravelApiDocs\Parsers;

use LaravelApiDocs\Models\ResponseDoc;
use ReflectionClass;
use ReflectionMethod;

class ResponseParser
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Controller method'undan response bilgilerini parse et.
     *
     * @return ResponseDoc[]
     */
    public function parse(string $controllerClass, string $methodName): array
    {
        $responses = [];

        // Kaynak koddan analiz
        $sourceResponses = $this->parseFromSource($controllerClass, $methodName);
        if (!empty($sourceResponses)) {
            return $sourceResponses;
        }

        // HTTP method'a göre varsayılan response'lar üret
        return $this->getDefaultResponses($methodName);
    }

    /**
     * Controller kaynak kodundan response bilgilerini çıkar.
     */
    protected function parseFromSource(string $controllerClass, string $methodName): array
    {
        if (!class_exists($controllerClass)) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($controllerClass);
            $fileName = $reflection->getFileName();

            if (!$fileName || !file_exists($fileName)) {
                return [];
            }

            $content = file_get_contents($fileName);
            $methodSource = $this->extractMethodSource($content, $methodName);

            if (!$methodSource) {
                return [];
            }

            return $this->analyzeMethodSource($methodSource);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Kaynak koddan belirli bir method'un gövdesini çıkar.
     */
    protected function extractMethodSource(string $content, string $methodName): ?string
    {
        // Method başlangıcını bul
        $pattern = '/(?:public|protected|private)\s+function\s+' . preg_quote($methodName, '/') . '\s*\([^)]*\)/';
        if (!preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $startPos = $matches[0][1];
        $braceCount = 0;
        $methodStart = null;
        $length = strlen($content);

        for ($i = $startPos; $i < $length; $i++) {
            if ($content[$i] === '{') {
                if ($methodStart === null) {
                    $methodStart = $i;
                }
                $braceCount++;
            } elseif ($content[$i] === '}') {
                $braceCount--;
                if ($braceCount === 0 && $methodStart !== null) {
                    return substr($content, $methodStart, $i - $methodStart + 1);
                }
            }
        }

        return null;
    }

    /**
     * Method kaynak kodunu analiz ederek response bilgilerini çıkar.
     */
    protected function analyzeMethodSource(string $source): array
    {
        $responses = [];

        // ServiceResponse pattern'i ara
        // ServiceResponse::success($data, 'mesaj', 201) veya benzeri
        preg_match_all(
            '/ServiceResponse::(\w+)\s*\(([^)]*)\)/s',
            $source,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $methodType = $match[1]; // success, error, vs.
            $args = $match[2];

            $isSuccess = in_array($methodType, ['success', 'ok', 'created']);
            $statusCode = $this->extractStatusCodeFromArgs($args, $isSuccess);
            $message = $this->extractMessageFromArgs($args);

            $responses[] = new ResponseDoc(
                statusCode: $statusCode,
                isSuccess: $isSuccess,
                message: $message,
                data: $isSuccess ? new \stdClass() : null,
            );
        }

        // return response()->json(...) pattern'i — çok satırlı array destekli
        preg_match_all(
            '/response\(\)\s*->\s*json\s*\(\s*(\[[\s\S]*?\])\s*(?:,\s*(\d{3}))?\s*\)/',
            $source,
            $jsonMatches,
            PREG_SET_ORDER
        );

        foreach ($jsonMatches as $match) {
            $statusCode = isset($match[2]) ? (int) $match[2] : 200;
            $arraySource = $match[1];

            // message alanını çıkar
            $message = null;
            if (preg_match("/['\"]message['\"]\s*=>\s*['\"]([^'\"]+)['\"]/", $arraySource, $msgMatch)) {
                $message = $msgMatch[1];
            }

            // data alanını çıkar
            $data = null;
            if (preg_match("/['\"]data['\"]\s*=>\s*\[\s*\]/", $arraySource)) {
                $data = [];
            } elseif (preg_match("/['\"]data['\"]\s*=>/", $arraySource)) {
                $data = new \stdClass();
            }

            // isSuccess alanını çıkar
            $isSuccess = $statusCode >= 200 && $statusCode < 300;
            if (preg_match("/['\"]isSuccess['\"]\s*=>\s*(true|false)/", $arraySource, $successMatch)) {
                $isSuccess = $successMatch[1] === 'true';
            }

            $responses[] = new ResponseDoc(
                statusCode: $statusCode,
                isSuccess: $isSuccess,
                message: $message,
                data: $data,
            );
        }

        // Status code'ları ara (sadece response()->json ile yakalanamayan durumlar)
        preg_match_all('/->(?:status|setStatusCode)\s*\(\s*(\d{3})\s*\)/', $source, $codeMatches);
        // Zaten bulunan kodları hariç tut
        $existingCodes = array_map(fn($r) => $r->statusCode, $responses);

        foreach ($codeMatches[1] ?? [] as $code) {
            $code = (int) $code;
            if (!in_array($code, $existingCodes)) {
                $responses[] = new ResponseDoc(
                    statusCode: $code,
                    isSuccess: $code >= 200 && $code < 300,
                );
            }
        }

        return $responses;
    }

    /**
     * Argümanlardan status code çıkar.
     */
    protected function extractStatusCodeFromArgs(string $args, bool $isSuccess): int
    {
        if (preg_match('/(\d{3})/', $args, $match)) {
            $code = (int) $match[1];
            if ($code >= 100 && $code <= 599) {
                return $code;
            }
        }
        return $isSuccess ? 200 : 400;
    }

    /**
     * Argümanlardan mesaj string'i çıkar.
     */
    protected function extractMessageFromArgs(string $args): ?string
    {
        if (preg_match("/['\"]([^'\"]+)['\"]/", $args, $match)) {
            return $match[1];
        }
        return null;
    }

    /**
     * Method adına göre varsayılan response'lar döndür.
     */
    protected function getDefaultResponses(string $methodName): array
    {
        $methodLower = strtolower($methodName);

        $defaults = [
            'index' => [new ResponseDoc(200, true, 'Listeleme başarılı', [])],
            'show' => [new ResponseDoc(200, true, 'Detay başarılı', new \stdClass())],
            'store' => [
                new ResponseDoc(201, true, 'Başarıyla oluşturuldu', new \stdClass()),
                new ResponseDoc(422, false, 'Validation hatası'),
            ],
            'create' => [
                new ResponseDoc(201, true, 'Başarıyla oluşturuldu', new \stdClass()),
                new ResponseDoc(422, false, 'Validation hatası'),
            ],
            'update' => [
                new ResponseDoc(200, true, 'Başarıyla güncellendi', new \stdClass()),
                new ResponseDoc(404, false, 'Kayıt bulunamadı'),
                new ResponseDoc(422, false, 'Validation hatası'),
            ],
            'destroy' => [
                new ResponseDoc(200, true, 'Başarıyla silindi'),
                new ResponseDoc(404, false, 'Kayıt bulunamadı'),
            ],
            'delete' => [
                new ResponseDoc(200, true, 'Başarıyla silindi'),
                new ResponseDoc(404, false, 'Kayıt bulunamadı'),
            ],
        ];

        // Tam eşleşme
        if (isset($defaults[$methodLower])) {
            return $defaults[$methodLower];
        }

        // Kısmi eşleşme (getAll, getAllCompanies vs.)
        foreach ($defaults as $key => $responses) {
            if (str_contains($methodLower, $key)) {
                return $responses;
            }
        }

        // Varsayılan
        return [
            new ResponseDoc(200, true, 'İşlem başarılı', new \stdClass()),
        ];
    }
}
