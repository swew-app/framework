<?php

declare(strict_types=1);

namespace SWEW\Framework;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SWEW\Framework\Traits\ContextTrait;
use SWEW\Framework\Traits\CreateRequestTrait;
use SWEW\Framework\Traits\CreateResponseTrait;

class SwewApplication
{
    use ContextTrait;
    use CreateRequestTrait;
    use CreateResponseTrait;

    // [?]: Инициализировать errorHandler и exceptionHandler
    // [?]: Инициализировать логгер
    // [?]: Инициализировать Cache
    // [?]: создать объект Request
    // [?]: создать объект Response
    //      - [ ]: Response - в зависимости от типа Request - выбирает тип ответа
    //      - [ ]: Response - если необходимо, то создает viewRenderer
    // [ ]: создать Router
    // [ ]: Найти нужный роут и построить цепочку Middleware + Controller добавляя рефлексию методов

    // [ ]: Объекты DTO - методы для заполнения и валидации validate, getRules, setData, getData
    // [ ]: Events - синхронные и асинхронные, подписка на синхронные
    // [ ]: Поиск в текущей фиче фабрик и view если нет, то поиск в Common
    public function run(): Http\Response
    {
//        set_error_handler($this->errorHandler);
//        set_exception_handler($this->exceptionHandler);

        $this->logger = $this->initLogger();
        $this->cache = $this->initCache();

        $this->request = $this->createRequest();
        $this->response = $this->createResponse();


//        $this->response->init('', '', $this);

        return $this->response->send();

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

    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext): void
    {
        //
    }

    public function exceptionHandler(Throwable $ex): void
    {
        //
    }

    public function initLogger(): ?LoggerInterface
    {
        return null;
    }

    public function initCache(): ?CacheInterface
    {
        return null;
    }

    public function renderView(string $filePath, array $data = []): string
    {
        return include($filePath);
    }
}
