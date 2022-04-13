<?php

include_once 'ExampleApp.php';
include_once 'controllers/ExampleController.php';
include_once 'middlewares/CorsMiddleware.php';
include_once 'middlewares/BreakMiddleware.php';

function getBaseStub($type = 'route')
{
    if ($type === 'route')
        return [
            [
                'name' => 'MainPage',
                'path' => '/',
                'controller' => '',
            ],
        ];

    if ($type === 'app')
        return new Integration\BaseCase\stubs\ExampleApp();

    throw new Error('Choose type from "route" | "app" ');
}
