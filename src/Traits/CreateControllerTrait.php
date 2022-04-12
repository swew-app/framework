<?php

declare(strict_types=1);

namespace SWEW\Framework\Traits;

use Exception;
use SWEW\Framework\Base\BaseController;

trait CreateControllerTrait
{
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
        $classInstance = new $class($params);

        if ($this->DEV) {
            if (!$classInstance instanceof BaseController) {
                throw new Exception("'$class' not classInstance of BaseController");
            }
        }

        $this->req->setParams($params);
        $classInstance->setApp($this);

        $classInstance->$method();
    }

    private function loadMiddlewares(array $middlewareNames)
    {
        foreach ($middlewareNames as $key) {
            $instance = new $this->middlewares[$key];

        }
    }
}
