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
            [
                'name' => 'Main',
                'path' => '/',
                'controller' => ControllerStub::class,
            ],
            [
                'name' => 'About',
                'path' => '/about',
                'controller' => [ControllerStub::class, 'aboutPage'],
                'method' => 'GET',
            ],
            [
                'name' => 'Blog',
                'path' => '/blog/{id}',
                'controller' => [ControllerStub::class, 'blogListPage'],
                'middlewares' => ['auth', 'admin'],
            ],
            [
                'name' => 'AdminPage',
                'path' => '/admin',
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
            [
                'name' => 'AdminPage',
                'path' => '/admin',
                'controller' => 'SomeAdminClass::class',
                'method' => 'GET|HEAD',
                'middlewares' => ['auth', 'admin']
            ]
        ];
}
