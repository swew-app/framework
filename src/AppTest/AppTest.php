<?php

declare(strict_types=1);

namespace Swew\Framework\AppTest;

use Exception;
use LogicException;
use Swew\Framework\Http\Partials\Stream;
use Swew\Framework\Http\RequestWrapper;
use Swew\Framework\Http\ResponseWrapper;
use Swew\Framework\SwewApp;

class AppTest
{
    public SwewApp $app;

    public string $content = '';

    public function __construct(SwewApp|string|null $app = null)
    {
        $this->removeSingletons();

        if (is_string($app) && class_exists($app)) {

            /** @var SwewApp $instance */
            $instance = new $app();
            $this->app = $instance;
        } elseif ($app instanceof SwewApp) {
            $this->app = $app;
        } else {
            $this->app = new SwewApp();
        }
    }

    public function removeSingletons(): void
    {
        RequestWrapper::removeInstance();
        ResponseWrapper::removeInstance();
        Stream::removeInstance();
    }

    public function setApp(SwewApp $app): static
    {
        $this->app = $app;

        return $this;
    }

    public function run(): void
    {
        $this->app->run();
    }

    /**
     * @throws Exception
     */
    public function addRoute(array $route): static
    {
        if (is_null($this->app->router)) {
            throw  new LogicException('Router not initialized');
        }

        $this->app->router->addRoute($route);
        $this->app->router->validate();

        return $this;
    }


    public function call(string $method, string $uri, $post = [], $server = []): static
    {
        $old = json_encode([
            $_SERVER,
            $_POST,
            $_REQUEST,
        ]);

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

        return $this;
    }

    public function ajax(string $method, string $uri, $post = []): static
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
}
