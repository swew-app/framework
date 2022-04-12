<?php

return [
    [
        'name' => 'MainPage',
        'path' => '/',
        'controller' => 'SomeClass::class'
    ],
    [
        'name' => 'AboutPage',
        'path' => '/about',
        'controller' => ['SomeClass::class', 'about']
    ],
];
