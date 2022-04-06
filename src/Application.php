<?php

declare(strict_types=1);

namespace SWEW\Framework;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class Application
{
    public function run(): void
    {
        // [ ]: Инициализировать errorHandler
        // [ ]: Инициализировать логгер
        // [ ]: Инициализировать Cache
        // [ ]: создать объект Request
        // [ ]: создать объект Response
        //      - [ ]: Response - в зависимости от типа Request - выбирает тип ответа
        //      - [ ]: Response - если необходимо, то создает viewRenderer
        // [ ]: создать Router
        // [ ]: Найти нужный роут и построить цепочку Middleware + Controller добавляя рефлексию методов

        // [ ]: Объекты DTO - методы для заполнения и валидации validate, getRules, setData, getData
        // [ ]: Events - синхронные и асинхронные, подписка на синхронные
        // [ ]: Поиск в текущей фиче фабрик и view если нет, то поиск в Common
        // [ ]:
    }

    /**
     * Path to the features folder
     *
     * @example
     *  $features = __DIR__ . '../Features';
     */
    public string $features = '';

    public string $pageNotFound = '';

    public string $pageServerError = '';

    /**
     * Path to router files
     *
     * @example
     *  $routers = [
     *      __DIR__ . '/../router/router.php',
     * ];
     */
    public array $routers = [];

    /**
     * @example
     *  $middlewares = [
     *    'auth' => /Features/Common/Middleware/AuthMiddleware::class,
     *  ];
     */
    public array $middlewares = [];

    /**
     * List of Middleware names that apply to all routers
     *
     * @example
     *  $globalMiddlewares = [ 'auth' ];
     */
    public array $globalMiddlewares = [];

    /**
     * Array, for auto-import of singletons in controllers and middleswares methods
     *
     * @example
     *  $classBinding = [
     *    /Features/Common/DB/MyDB::class =>
     *                                  fn() => new MyDB($db_config);
     *  ];
     *
     */
    public array $classBinding = [];

    public function initLogger(): LoggerInterface
    {
        //
    }

    public function initCache(): CacheInterface
    {
        //
    }

    public function exceptionHandler(\Exception $e): void
    {
        //
    }

    public function renderView(string $filePath, array $data = []): string
    {
        return '';
    }
}
