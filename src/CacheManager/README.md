# CacheManager

CacheManager - класс для управления путями до кэшей.

Примеры использования

Сохранение пути файла кэша

```php
<?php

$cache = CacheManager::getInstance();
$cache->setCacheDir('../cache_dir');
$cache->setFile('router', 'file_name.cache');

$cache->getFile('router'); // null

// Включаем доступ

$cache->enable(); // For All
$cache->enable('router'); // For item

$cache->getFile('router'); // '../cache_dir/file_name.cache'

$cache->disable(); // For All
$cache->disable('router'); // For item

$cache->getFile('router'); // null
```

Сохранение стейта инстанса кэшей.

Будет восстанавливаться при загрузке. После выставления кэширования, больше добавить новое значение будет нельзя.

```php
<?php

$cache = CacheManager::getInstance();

if ($app->IS_DEV) { // Будт аккуратен!!!
    // Remove cache
    $cache->instanceCache(false);
}

if (! $cache->hasInstanceCache()) {
    $cache->enable(); // For All
    $cache->setCacheDir('../cache_dir');
    $cache->setFile('router', 'file_name.cache');

    // Enable cache
    $cache->instanceCache(true);
}
```
