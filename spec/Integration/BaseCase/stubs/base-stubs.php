<?php


use Integration\BaseCase\stubs\ExampleApp;

function getBaseStub($type = 'route')
{
    if ($type === 'route')
        return [
            '/' => [
                'name' => 'MainPage',
                'controller' => '',
            ],
        ];

    if ($type === 'app')
        return new ExampleApp();

    throw new Error('Choose type from "route" | "app" ');
}
