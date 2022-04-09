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

    public function call(string $method, string $uri, $data = []): static
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';

        $_POST = array_merge($_POST, $data);
        $_REQUEST = array_merge($_REQUEST, $_POST);

        $this->app->run();

        return $this;
    }

    public function getResponse(): Response
    {
        return $this->app->response;
    }

    public function getRequest(): Request
    {
        return $this->app->request;
    }
}
