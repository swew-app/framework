<?php

declare(strict_types=1);

namespace Swew\Framework\AppTest;

use LogicException;
use Swew\Framework\Env\EnvContainer;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\ResponseWrapper;
use Swew\Framework\SwewApp;

class AppTest
{
    public SwewApp $app;

    public string $content = '';

    /**
     * Сохраняем путь до класса что бы при тестировании можно было указать App только один раз
     */
    private static string $appClassPath = '';

    public function __construct(SwewApp|string|null $app = null)
    {
        $_COOKIE = [];

        $this->removeSingletons();

        putenv('APP_IS_TEST=true');
        putenv('__TEST__=true');

        if (($app === null || $app === '') && self::$appClassPath !== '') {
            $app = self::$appClassPath;
        }

        if (is_string($app) && class_exists($app)) {
            /** @var SwewApp $instance */
            $instance = new $app();
            $this->app = $instance;
        } elseif ($app instanceof SwewApp) {
            $this->app = $app;
        } else {
            throw new LogicException('Add the application class to the constructor "new AppTest(App::class);"');
        }

        self::$appClassPath = $this->app::class;

        $env = EnvContainer::getInstance();
        $env->set('__TEST__', true);

        $this->app->load();
    }

    public function removeSingletons(): void
    {
        RequestWrapper::removeInstance();
        ResponseWrapper::removeInstance();
        EnvContainer::removeInstance();
    }

    private function run(): void
    {
        $this->app->run();
    }

    /**
     * @param array<string, scalar|array|null> $post
     * @param array{
     *     CONTENT_TYPE?: 'application/json;charset=UTF-8',
     *     HTTP_ACCEPT?: 'application/json;charset=UTF-8'
     * } $server
     */
    public function call(string $method, string $uri, array $post = [], array $server = []): static
    {
        if ($this->app->router === null) {
            throw new LogicException('Router not initialized: need use route(...) function');
        }

        res()->setTestEnv(true);

        $old = json_encode([
            $_SERVER,
            $_POST,
            $_REQUEST,
        ]);

        if ($old === false) {
            throw new LogicException('Failed to encode server data');
        }

        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';

        $_SERVER = array_merge_recursive($_SERVER, $server);

        $_POST = array_merge($_POST, $post);
        $_REQUEST = array_merge($_REQUEST, $_POST);

        $this->run();

        $data = json_decode($old, true);
        $_SERVER = $data[0];
        $_POST = $data[1];
        $_REQUEST = $data[2];

        res()->send();

        return $this;
    }

    public function ajax(string $method, string $uri, array $post = []): static
    {
        return $this->call($method, $uri, $post, [
            'CONTENT_TYPE' => 'application/json;charset=UTF-8',
            'HTTP_ACCEPT' => 'application/json;charset=UTF-8',
        ]);
    }

    public function getResponse(): ResponseWrapper
    {
        return res();
    }

    public function getRequest(): RequestWrapper
    {
        return req();
    }

    public function getApp(): SwewApp
    {
        return $this->app;
    }

    public function getMethcedRoute(): ?array
    {
        return $this->app->router?->matchedRoute;
    }
}
