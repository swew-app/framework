<?php

declare(strict_types=1);

use Swew\Testing\Integration\stubs\Controllers\AdminController;
use Swew\Testing\Integration\stubs\Controllers\ExampleController;
use Swew\Testing\Integration\stubs\Controllers\ManagerController;

route('GET /', [ExampleController::class, 'getIndex']);

route('GET /post', [ExampleController::class, 'getPost']);

route('GET /admin', [AdminController::class, 'getIndex']);

route('GET /manager', [ManagerController::class, 'dashboard'], prefix: '/admin');

route('GET /some-page', [AdminController::class, 'getSomePage'], middlewares: ['break']);
