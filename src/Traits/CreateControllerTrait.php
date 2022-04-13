<?php

declare(strict_types=1);

namespace SWEW\Framework\Traits;

use Exception;
use SWEW\Framework\Base\BaseController;

trait CreateControllerTrait
{
    private array $middlewareInstances = [];

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
     * @throws Exception
     */
    public function runController(string $class, string $method, array $params, array $middlewareNames): void
    {
        $this->req->setParams($params);

        if ($this->runMiddlewares($middlewareNames) === false) {
            return;
        }

        $classInstance = new $class($params);

        if ($this->DEV) {
            if (!$classInstance instanceof BaseController) {
                throw new Exception("'$class' not classInstance of BaseController");
            }
        }

        $classInstance->setApp($this);

        $classInstance->$method();

        $this->runPostMiddlewares();
    }

    private function runMiddlewares(array $middlewareNames): bool
    {
        foreach ($middlewareNames as $key) {
            $this->middlewareInstances[$key] = new $this->middlewares[$key];

            $this->middlewareInstances[$key]->setApp($this);

            if ($this->middlewareInstances[$key]->handle() === false) {
                return false;
            }
        }

        return  true;
    }

    private function runPostMiddlewares(): void
    {
        foreach ($this->middlewareInstances as $middleware) {
            $middleware->beforeResponse();
        }
    }
}
