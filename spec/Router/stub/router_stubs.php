<?php

use Router\stub\ControllerStub;

/**
 * @param {'config'|'infoList'|'mergedConfig'} $wantType
 * @return array|void
 */
function routerStub($wantType = 'config')
{
    if ($wantType === 'config')
        return [
            '/' => [
                'name' => 'Main',
                'controller' => ControllerStub::class,
            ],
            '/about' => [
                'name' => 'About',
                'controller' => [ControllerStub::class, 'aboutPage'],
                'method' => 'GET',
            ],
            '/blog/{id}' => [
                'name' => 'Blog',
                'controller' => [ControllerStub::class, 'blogListPage'],
                'middlewares' => ['auth', 'admin'],
            ],
            '/admin' => [
                'name' => 'AdminPage',
                'middlewares' => [],
                'controller' => [],
                'dev' => true,
            ],
        ];

    if ($wantType === 'infoList')
        return [
            [
                "Name",
                "Path",
                "Controller",
                "Middlewares",
                "DEV"
            ],

            [
                "Main",
                "/",
                "Router\\stub\\ControllerStub",
                "",
                "FALSE"
            ],
            [
                "About",
                "/about",
                ["Router\\stub\\ControllerStub", "aboutPage"],
                "",
                "FALSE"
            ],
            [
                "Blog",
                "/blog/{id}",
                ["Router\\stub\\ControllerStub", "blogListPage"],
                "auth,admin",
                "FALSE"
            ],
            [
                "AdminPage",
                "/admin",
                [],
                "",
                "TRUE"
            ]
        ];

    // Merged from: stub/routes_stub_1.php & stub/routes_stub_2.php
    if ($wantType === 'mergedConfig')
        return [
            '/' => [
                'name' => 'MainPage',
                'controller' => 'SomeClass::class'
            ],
            '/about' => [
                'name' => 'AboutPage',
                'controller' => ['SomeClass::class', 'about']
            ],
            '/admin' => [
                'name' => 'AdminPage',
                'controller' => 'SomeAdminClass::class',
                'method' => 'GET|HEAD',
                'middlewares' => ['auth', 'admin']
            ]
        ];
}
