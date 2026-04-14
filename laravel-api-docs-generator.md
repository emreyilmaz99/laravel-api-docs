# Laravel API Docs Generator — Kutuphane Plani

Amac: Laravel projelerinde route, FormRequest ve controller'lardan otomatik olarak ClickUp API Docs benzeri interaktif dokumantasyon sayfasi ureten bir Composer paketi gelistirmek.

---

## Hedef Ozellikler

### 1. Otomatik Route Parsing
- `routes/api.php`'deki tum endpoint'leri tara
- HTTP method (GET, POST, PUT, DELETE)
- URL path ve prefix
- Middleware bilgisi (auth:sanctum, checkPermission vs.)
- Controller ve method eslesmesi
- Route group bilgileri (prefix, middleware)

### 2. FormRequest Parsing
- Controller method'undaki type-hint'ten FormRequest class'ini bul
- `rules()` metodundan parametre bilgilerini cek:
  - Alan adi
  - Tip (string, integer, boolean, array vs.)
  - Zorunlu/opsiyonel
  - Validation kurallari (max, min, in, exists vs.)
  - `in:` kuralindan enum degerleri
- `messages()` metodundan Turkce/Ingilizce aciklamalari cek
- Request body ornegi olustur (kurallara gore ornek JSON uret)

### 3. Response Format Parsing
- ServiceResponse pattern'ini tani:
  ```json
  {
      "isSuccess": true,
      "message": "...",
      "data": {},
      "statusCode": 200
  }
  ```
- Controller method'undan donen status code'lari tespit et (200, 201, 404, 422 vs.)
- Repository'deki ServiceResponse cagrilarindan basari/hata mesajlarini cek

### 4. Interaktif UI (ClickUp API Docs Benzeri)
- Sol sidebar: Endpoint gruplari (Company, Product, Version, Guide vs.)
- Orta alan: Endpoint detayi
  - Method rozeti (GET yesil, POST mavi, PUT turuncu, DELETE kirmizi)
  - URL
  - Aciklama
  - Auth bilgisi (hangi middleware gerekiyor)
  - Body Params tablosu (alan, tip, zorunlu, aciklama, ornek deger)
  - Response ornekleri (basari + hata)
- Sag alan: Ornek request kodu (cURL, JavaScript fetch, PHP Guzzle)
- "Try It" butonu: Docs sayfasindan direkt API cagrisi yapabilme

### 5. Ek Ozellikler
- Arama (endpoint, parametre, aciklama icinde)
- Dark/Light tema
- Dil destegi (TR/EN — mevcut lang dosyalarindan)
- Export (OpenAPI 3.0 JSON/YAML, Postman Collection)
- Markdown olarak da export edebilme
- Versiyon bazli docs (v1, v2 gibi)

---

## Teknik Mimari

### Paket Yapisi
```
laravel-api-docs/
├── src/
│   ├── ApiDocsServiceProvider.php      # Laravel service provider
│   ├── ApiDocsGenerator.php            # Ana generator class
│   ├── Parsers/
│   │   ├── RouteParser.php             # Route bilgilerini toplar
│   │   ├── FormRequestParser.php       # Validation kurallarini parse eder
│   │   ├── ResponseParser.php          # Response formatini cikarir
│   │   └── MiddlewareParser.php        # Auth/permission bilgisi cikarir
│   ├── Renderers/
│   │   ├── HtmlRenderer.php            # Blade ile HTML cikti
│   │   ├── JsonRenderer.php            # OpenAPI 3.0 JSON cikti
│   │   └── MarkdownRenderer.php        # Markdown cikti
│   ├── Models/
│   │   ├── EndpointDoc.php             # Tek endpoint bilgisi
│   │   ├── ParameterDoc.php            # Parametre bilgisi
│   │   └── ResponseDoc.php             # Response bilgisi
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ApiDocsController.php   # Docs sayfasi controller
│   │   └── Middleware/
│   │       └── ApiDocsAccess.php       # Docs erisim kontrolu
│   └── Console/
│       └── GenerateDocsCommand.php     # php artisan api-docs:generate
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php           # Ana layout
│   │   ├── components/
│   │   │   ├── sidebar.blade.php       # Sol menu
│   │   │   ├── endpoint.blade.php      # Endpoint detay
│   │   │   ├── params-table.blade.php  # Parametre tablosu
│   │   │   ├── code-sample.blade.php   # Kod ornekleri
│   │   │   └── try-it.blade.php        # Try It paneli
│   │   └── index.blade.php             # Ana sayfa
│   └── assets/
│       ├── css/
│       │   └── docs.css                # Stiller
│       └── js/
│           └── docs.js                 # Try It, arama, tema degistirme
├── config/
│   └── api-docs.php                    # Paket konfigurasyonu
├── routes/
│   └── web.php                         # Docs sayfasi route'lari
├── composer.json
├── README.md
└── tests/
```

### Konfigurasyon Dosyasi (config/api-docs.php)
```php
return [
    'enabled' => env('API_DOCS_ENABLED', true),

    // Docs sayfasi URL'i
    'path' => 'api-docs',

    // Docs erisim kontrolu
    'middleware' => ['web'],  // veya ['web', 'auth'] sadece giris yapmis kullanicilar icin

    // Taranacak route prefix'leri
    'route_prefix' => ['api/v1'],

    // Haric tutulacak route'lar
    'exclude_routes' => [
        'api/v1/auth/login',  // ornek
    ],

    // Haric tutulacak middleware'ler (bu middleware'e sahip route'lar gosterilmez)
    'exclude_middlewares' => [],

    // Gruplama yontemi: 'prefix' veya 'controller'
    'group_by' => 'prefix',

    // API base URL (Try It icin)
    'base_url' => env('API_DOCS_BASE_URL', 'http://localhost:8000'),

    // Default auth header (Try It icin)
    'default_auth_header' => 'Authorization',
    'default_auth_prefix' => 'Bearer',

    // Varsayilan dil
    'locale' => 'tr',

    // Tema
    'theme' => 'dark',  // 'dark' veya 'light'

    // Response format
    'response_wrapper' => [
        'isSuccess' => 'boolean',
        'message' => 'string',
        'data' => 'mixed',
        'statusCode' => 'integer',
    ],
];
```

### Route Parser Mantigi
```php
// 1. Laravel Route::getRoutes() ile tum route'lari al
// 2. Konfigurasyondaki prefix'e gore filtrele
// 3. Her route icin:
//    - URI, method, name, middleware
//    - Controller class + method
//    - FormRequest class (method parametresinden)
//    - Route group prefix (gruplama icin)
```

### FormRequest Parser Mantigi
```php
// 1. Controller method'unun type-hint'lerini oku (ReflectionMethod)
// 2. FormRequest extend eden class'i bul
// 3. rules() metodunu cagir
// 4. Her kural icin:
//    - 'required' → zorunlu
//    - 'string|integer|boolean|array' → tip
//    - 'max:255' → max uzunluk
//    - 'in:a,b,c' → enum degerleri
//    - 'exists:table,column' → foreign key referansi
//    - 'nullable' → opsiyonel
// 5. messages() metodundan aciklamalari es
// 6. Ornek JSON body olustur
```

### Ornek Cikti Gorunumu
```
┌─────────────────────────────────────────────────────────────────┐
│ ◀ API Documentation                              🔍 Search...  │
├──────────┬──────────────────────────────────────────────────────┤
│          │                                                      │
│ Auth     │  POST  /api/v1/customer-request/create              │
│  Login   │                                                      │
│  Logout  │  Yeni musteri talebi olusturur.                     │
│          │                                                      │
│ Company  │  Auth: Bearer Token (Sanctum)                       │
│  GetAll  │                                                      │
│  Create  │  ── Body Params ──────────────────────────────────  │
│  Update  │  title        string   required  max:255            │
│  Delete  │  description  string   optional                     │
│          │  request_type_id  int  required  exists:request_types│
│ Product  │  priority     string   optional  low|medium|high    │
│  ...     │  customer_name string  required  max:255            │
│          │  source_channel string optional  email|phone|...    │
│ Version  │                                                      │
│  ...     │  ── Response (201) ────────────────────────────────  │
│          │  {                                                    │
│ Guide    │    "isSuccess": true,                                │
│  ...     │    "message": "Talep basariyla olusturuldu",        │
│          │    "data": { ... },                                  │
│ Customer │    "statusCode": 201                                 │
│ Request  │  }                                                    │
│  ● GetAll│                                                      │
│  ● Create│  ── Example Request ──────────────────────────────  │
│  ● Update│  curl -X POST .../api/v1/customer-request/create \  │
│  ● Delete│    -H "Authorization: Bearer {token}" \             │
│  ...     │    -d '{"title":"Bug","customer_name":"Ali"}'       │
│          │                                                      │
│ Dashboard│  [ Try It ]                                          │
│  ...     │                                                      │
└──────────┴──────────────────────────────────────────────────────┘
```

---

## Gelistirme Adimlari

### Faz 1: Temel Altyapi
1. Composer paketi olustur (`composer init`)
2. ServiceProvider yaz
3. RouteParser — route bilgilerini topla
4. FormRequestParser — validation kurallarini parse et
5. EndpointDoc model — parse edilen bilgileri tut
6. Basit Blade view ile listeleme

### Faz 2: Detayli Parsing
7. MiddlewareParser — auth/permission bilgisi
8. ResponseParser — ServiceResponse pattern'inden response ornekleri
9. Ornek request body olusturma (validation kurallarindan)
10. Ornek kod bloklari (cURL, JavaScript, PHP)

### Faz 3: UI
11. ClickUp benzeri layout (sidebar + detay + kod ornegi)
12. Dark/Light tema
13. Arama fonksiyonu
14. Method rozeti renkleri

### Faz 4: Try It
15. Try It paneli — docs sayfasindan API cagrisi
16. Auth token girisi
17. Request body editoru
18. Response goruntuleyici

### Faz 5: Export & Ekstralar
19. OpenAPI 3.0 JSON/YAML export
20. Postman Collection export
21. Markdown export
22. `php artisan api-docs:generate` komutu (statik HTML uretimi)

---

## Kurulum (Hedef Kullanim)

```bash
composer require senin-paket-adin/laravel-api-docs
php artisan vendor:publish --tag=api-docs-config
```

Sonra `http://localhost:8000/api-docs` adresine gidince dokumantasyon sayfasi gorunecek.

---

## Notlar
- Bu bagimsiz bir Composer paketi olacak, versiyon-backend projesinden ayri gelistirilecek
- Ilk hedef: Kendi projenle (versiyon-backend) calisir hale getirmek
- Ikinci hedef: Herhangi bir Laravel projesine kurulabilir hale getirmek
- UI icin Blade + Tailwind CSS (veya saf CSS) kullanilabilir
- Try It icin frontend JS yeterli (framework gerektirmez)
