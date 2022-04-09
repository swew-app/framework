<?php

declare(strict_types=1);

namespace SWEW\Framework\Traits;

use SWEW\Framework\Controller\BaseController;

trait CreateControllerTrait
{

    /**
     * @throws \Exception
     */
    public function runController(string $class, string $method, array $params, array $middlewares): void
    {
        $classInstance = new $class($params);

        if ($this->DEV) {
            if (!$classInstance instanceof BaseController) {
                throw new \Exception("'$class' not classInstance of BaseController");
            }
        }

        $classInstance->setApp($this);

        $classInstance->$method();
    }
}
