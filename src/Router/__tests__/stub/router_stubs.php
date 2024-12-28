<?php

require_once 'ControllerStub.php';

use Router\stub\ControllerStub;

/**
 * @param string $wantType
 * @return array
 */
function routerStub(string $wantType = 'config'): array
{
    if ($wantType === 'config') {
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
            ],
        ];
    }

    if ($wantType === 'infoList') {
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
    }

    // Merged from: stub/routes_stub_1.php & stub/routes_stub_2.php
    if ($wantType === 'mergedConfig') {
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

    if ($wantType === 'child') {
        return [
            [
                'name' => 'MainPage',
                'path' => '/',
                'controller' => 'SomeClass::class',
                'children' => [
                    [
                        'name' => 'AboutPage',
                        'path' => '/about',
                        'controller' => 'SomeClass::class',
                        'children' => [
                            [
                                'name' => 'AddressPage',
                                'path' => '/address',
                                'controller' => 'SomeClass::class',
                            ],
                        ]
                    ],
                ],
            ],

        ];
    }

    if ($wantType === 'methodRoute') {
        return [
            [
                'name' => 'MainPage',
                'path' => '/{_method_}',
                'controller' => 'SomeClass::class',
            ]
        ];
    }

    return [];
}
