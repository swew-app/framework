<?php

declare(strict_types=1);

namespace SWEW\Framework;

use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SWEW\Framework\Http\Request;
use SWEW\Framework\Http\Response;
use SWEW\Framework\Router\Router;
use SWEW\Framework\Traits\ContextTrait;
use SWEW\Framework\Traits\CreateControllerTrait;
use SWEW\Framework\Traits\CreateRequestTrait;
use SWEW\Framework\Traits\CreateResponseTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class SwewApplication
{
    use ContextTrait;
    use CreateRequestTrait;
    use CreateResponseTrait;
    use CreateControllerTrait;

    // [?]: Инициализировать errorHandler и exceptionHandler
    // [?]: Инициализировать логгер
    // [?]: Инициализировать Cache
    // [?]: создать объект Request
    // [?]: создать объект Response
    //      - [ ]: Response - в зависимости от типа Request - выбирает тип ответа
    //      - [ ]: Response - если необходимо, то создает viewRenderer
    // [x]: создать Router
    // [x]: Найти нужный роут
    // [ ]: Построить цепочку Middleware + Controller добавляя рефлексию методов

    // [ ]: Объекты DTO - методы для заполнения и валидации validate, getRules, setData, getData
    // [ ]: Events - синхронные и асинхронные, подписка на синхронные
    // [ ]: Поиск в текущей фиче "фабрик" и "view" если нет, то поиск в Common
    public Router $router;

    private bool $isInitialized = false;

    public function run(): Http\Response
    {
        $this->initialize();

        $routeItem = $this->router->getRoute(
            $this->req->getMethod(),
            $this->req->getRequestUri()
        );

        // if Not Found - return 404 page
        if (empty($routeItem['class']) || empty($routeItem['method'])) {
            return $this->res->send404(!$this->TEST);
        }

        // На основе пришедшего запроса, создаем конфиг с ответом html/json
        $this->res->setResponseConfig(
            $this->req->getAcceptableContentTypes()
        );

        // Создаем класс контроллера и вызываем его метод
        $this->runController(
            $routeItem['class'],
            $routeItem['method'],
            $routeItem['params'],
            $routeItem['middlewares'],
        );

        // Перед отправкой, проверить вызвался ли view, если нет, то делаем проверку хэдера ответа
        return $this->res->finalSendResponse(!$this->TEST);
    }

    public function initialize(): static
    {
        if ($this->isInitialized) {
            return $this;
        }

//        set_error_handler($this->errorHandler);
//        set_exception_handler($this->exceptionHandler);

        $this->logger = $this->initLogger();
        $this->cache = $this->initCache();

        $this->req = $this->createRequest();
        $this->res = $this->createResponse();

        $this->ctx = new ParameterBag([]);

        $this->router = new Router(
            Router::getRoutesFromPaths($this->routeFiles),
            $this->host
        );

        if ($this->DEV) {
            $this->router->validate();
        }

        $this->isInitialized = true;

        return $this;
    }

    public Request $req;

    public Response $res;

    public ParameterBag $ctx;

    # Parameters that must be changed during installation

    public bool $DEV = true; // DEV mode change before deploy

    public bool $TEST = false; // TEST mode

    public string $host = ''; // TODO: добавить в роутер и респонз // $_SERVER[HTTP_HOST]

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
    public array $routeFiles = [];

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
