<?php

return [
    '/admin' => [
        'name' => 'AdminPage',
        'controller' => 'SomeAdminClass::class',
        'method' => 'GET|HEAD',
        'middlewares' => ['auth', 'admin'],
    ],
];
