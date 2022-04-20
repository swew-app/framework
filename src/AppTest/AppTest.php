<?php

declare(strict_types=1);

namespace SWEW\Framework\AppTest;

use Exception;
use SWEW\Framework\Http\Request;
use SWEW\Framework\Http\Response;
use SWEW\Framework\SwewApplication;

class AppTest
{
    public SwewApplication $app;
    public string $content = '';

    /**
     * @throws Exception
     */
    public function __construct(SwewApplication $app = null)
    {
        if (!empty($app)) {
            $this->app = $app;
        } else {
            $this->app = new SwewApplication();
        }
    }

    public function run()
    {
        $this->app->run();

        return $this;
    }

    public function setApp(SwewApplication $app): static
    {
        $this->app = $app;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function addRoute(array $route): static
    {
        $this->app->initialize();

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

        $this->app->initialize();
        $this->app->TEST = true;
        $this->app->run();

        $data = json_decode($old, true);
        $_SERVER = $data[0];
        $_POST = $data[1];
        $_REQUEST = $data[2];

        return $this;
    }

    public function ajax(string $method, string $uri, $post = [])
    {
        return $this->call($method, $uri, $post, [
            'CONTENT_TYPE' => 'application/json;charset=UTF-8'
        ]);
    }

    public function getResponse(): Response
    {
        return $this->app->res;
    }

    public function getRequest(): Request
    {
        return $this->app->req;
    }
}
