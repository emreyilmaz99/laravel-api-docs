<?php

use Illuminate\Support\Facades\Route;
use LaravelApiDocs\Http\Controllers\ApiDocsController;
use LaravelApiDocs\Http\Middleware\ApiDocsAccess;

$path = config('api-docs.path', 'api-docs');
$middleware = config('api-docs.middleware', ['web']);

Route::group([
    'prefix' => $path,
    'middleware' => array_merge($middleware, [ApiDocsAccess::class]),
], function () {
    Route::get('/', [ApiDocsController::class, 'index'])->name('api-docs.index');
    Route::get('/json', [ApiDocsController::class, 'json'])->name('api-docs.json');
    Route::get('/export/openapi', [ApiDocsController::class, 'openapi'])->name('api-docs.openapi');
    Route::get('/export/postman', [ApiDocsController::class, 'postman'])->name('api-docs.postman');
    Route::get('/export/markdown', [ApiDocsController::class, 'markdown'])->name('api-docs.markdown');
});
