<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    // ->withCache('./.cache/rector', FileCacheStorage::class)
    ->withPaths([
        __DIR__ . '/src',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(php84: true)
    ->withTypeCoverageLevel(40)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
