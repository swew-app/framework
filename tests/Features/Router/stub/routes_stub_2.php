<?php

return [
    [
        'name' => 'AdminPage',
        'path' => '/admin',
        'controller' => 'SomeAdminClass::class',
        'method' => 'GET|HEAD',
        'middlewares' => ['auth', 'admin'],
    ],
];
