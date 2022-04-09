<?php

use Integration\BaseCase\stubs\controllers\ExampleController;

return [
    '/' => [
        'name' => 'MainExamplePage',
        'controller' => ExampleController::class,
    ],

    '/about' => [
        'name' => 'AboutExamplePage',
        'controller' => [ExampleController::class, 'about'],
    ],
];
