<?php

declare(strict_types=1);

namespace Swew\Framework\CacheManager;

beforeEach(function (): void {
    CacheManager::removeInstance();
});

afterAll(function (): void {
    $cache = CacheManager::getInstance();
    $cache->instanceCache(false); // clear self cache
});

it('Cache File Scenarios', function (): void {
    $cache = CacheManager::getInstance();

    $name = '_router.cache';
    $expected = __DIR__ . DIRECTORY_SEPARATOR . $name;

    $cache->setCacheDir(__DIR__);

    $cache->setFile('router', $name);
    $cache->getFile('router');

    $cache->setFile('view', $name);

    expect($cache->getFile('router'), 'No cache')->toBe(null);

    $cache->enable(); // For All

    expect($cache->getFile('router'), 'Has cache')->toBe($expected);

    $cache->disable(); // For All

    expect($cache->getFile('router'), 'No cache again')->toBe(null);
    expect($cache->getFile('view'), 'Disabled for other 1')->toBe(null);

    $cache->enable('router'); // For Single

    expect($cache->getFile('router'), 'Enabled for single')->toBe($expected);
    expect($cache->getFile('view'), 'Disabled for other 2')->toBe(null);

    $cache->enable(); // For All

    $cache->disable('view');
    expect($cache->getFile('router'), 'Enabled for all')->toBe($expected);
    expect($cache->getFile('view'), 'Disabled for Single')->toBe(null);
});

it('Check that the global cache is loaded and its values are not overwritten', function (): void {
    $cache = CacheManager::getInstance();

    // Remove cache file
    $cache->instanceCache(false);

    $cache->setFile('middleware', '1', true);

    expect($cache->getFile('middleware'))->toBeTruthy();

    $expectedPath = $cache->getFile('middleware');

    // Save cache
    $cache->instanceCache(true);

    $cache->setFile('middleware', '2', true);

    expect($cache->getFile('middleware'))->toBe($expectedPath);

    // Loading of instance cache
    CacheManager::removeInstance();

    $cacheSecond = CacheManager::getInstance();

    expect($cacheSecond->getFile('middleware'))->toBe($expectedPath);
});
