<?php

return [
    '/' => [
        'name' => 'MainPage',
        'controller' => 'SomeClass::class'
    ],
    '/about' => [
        'name' => 'AboutPage',
        'controller' => ['SomeClass::class', 'about']
    ],
];
