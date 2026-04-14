<?php

namespace LaravelApiDocs\Console;

use Illuminate\Console\Command;
use LaravelApiDocs\ApiDocsGenerator;

class ClearCacheCommand extends Command
{
    protected $signature = 'api-docs:clear';

    protected $description = 'API dokümantasyon cache\'ini temizler';

    public function handle(): int
    {
        ApiDocsGenerator::clearCache();
        $this->info('API Docs cache temizlendi.');

        return self::SUCCESS;
    }
}
