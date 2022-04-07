<?php

use Router\stub\ControllerStub;

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
                "",
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
