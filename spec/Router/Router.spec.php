<?php

use SWEW\Framework\Router\Router;

include 'stub/router_stubs.php';

describe("SwewApplication", function () {
    //*/

    it('validate [GOOD]', function () {
        $router = new Router([
            '/' => [
                'name' => 'MainPage',
                'middlewares' => [],
                'controller' => [],
                'dev' => true,
            ]
        ]);


        expect($router->validate())->toBe(true);
    });

    it('validate [BAD 1]: Has wrong key', function () {
        $router = new Router([
            '/' => [
                'name' => 'MainPage',
                'middlewares' => [],
                'controller' => [],
                'dev' => true,
                'someKey' => '',
            ]
        ]);


        expect(fn () => $router->validate())->toThrow(new Error("Not allowed key 'someKey' in router"));
    });

    it('validate [BAD 2]: Same Name', function () {
        $router = new Router([
            '/' => [
                'name' => 'MainPage',
                'controller' => [],
            ],
            '/about' => [
                'name' => 'MainPage',
                'controller' => [],
            ],
        ]);

        expect(fn () => $router->validate())->toThrow(new Error("Route name 'MainPage' already used"));
    });

    it('validate [BAD 3]: Empty Controller', function () {
        $router = new Router([
            '/' => [
                'controller' => [],
            ],
        ]);

        expect(fn () => $router->validate())->toThrow(new Error("Route key 'name' is required"));
    });

    it('validate [BAD 4]: Empty Name', function () {
        $router = new Router([
            '/' => [
                'name' => 'Main',
            ],
        ]);

        expect(fn () => $router->validate())->toThrow(new Error("Route key 'controller' is required"));
    });

    it('getInfoList', function () {
        $router = new Router(routerStub());

        expect($router->getInfoList())->toBe(routerStub('infoList'));
    });

    it('getRoute', function () {
        $router = new Router(routerStub());

        expect($router->getRoute('GET', '/'))->toBe([
            'class' => 'Router\\stub\\ControllerStub',
            'method' => 'getIndex',
            'params' => [],
        ]);
    });

    //*/
});

describe('Route Search', function () {
/*
    it('findRouteByFastRouter [GOOD]', function () {
        $router = new Router(routerStub());

        $res = $router->findRouteByFastRouter('GET', '/blog/2');

        expect($res)->toBe([
            FastRoute\Dispatcher::FOUND,
            'Router\\stub\\ControllerStub@blogListPage',
            ['id' => '2'],
        ]);
    });

    it('findRouteByFastRouter [NOT_FOUND]: path', function () {
        $router = new Router(routerStub());

        $res = $router->findRouteByFastRouter('GET', '/wrong');

        expect($res)->toBe([
            FastRoute\Dispatcher::NOT_FOUND,
        ]);
    });

    it('findRouteByFastRouter [METHOD_NOT_ALLOWED]: method', function () {
        $router = new Router(routerStub());

        $res = $router->findRouteByFastRouter('POST', '/about');

        expect($res)->toBe([
            FastRoute\Dispatcher::METHOD_NOT_ALLOWED,
            ['GET']
        ]);
    });

    it('toRouteFromFastRoute [GOOD]', function () {
        $router = new Router(routerStub());
        $uri = '/blog/101';

        $r = $router->findRouteByFastRouter('POST', $uri);

        $item = $router->toRouteFromFastRoute($r, 'GET', $uri);

        expect($item)->toBe([
            'class' => 'Router\\stub\\ControllerStub',
            'method' => 'blogListPage',
            'params' => ['id' => '101'],
        ]);
    });
//*/

//*
    it('toRouteFromFastRoute [GOOD] resourceMethod', function () {
        $router = new Router(routerStub());
        $uri = '/';

        $r = $router->findRouteByFastRouter('POST', $uri);

        $item = $router->toRouteFromFastRoute($r, 'POST', $uri);

        expect($item)->toBe([
            'class' => 'Router\\stub\\ControllerStub',
            'method' => 'postIndex',
            'params' => [],
        ]);
    });
// */
});
