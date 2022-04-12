<?php

use SWEW\Framework\Router\Router;

include 'stub/router_stubs.php';

describe("Router create", function () {
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


        expect(fn () => $router->validate())->toThrow(new Exception("Not allowed key 'someKey' in router"));
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

        expect(fn () => $router->validate())->toThrow(new Exception("Route name 'MainPage' already used"));
    });

    it('validate [BAD 3]: Empty Controller', function () {
        $router = new Router([
            '/' => [
                'controller' => [],
            ],
        ]);

        expect(fn () => $router->validate())->toThrow(new Exception("Route key 'name' is required"));
    });

    it('validate [BAD 4]: Empty Name', function () {
        $router = new Router([
            '/' => [
                'name' => 'Main',
            ],
        ]);

        expect(fn () => $router->validate())->toThrow(new Exception("Route key 'controller' is required"));
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
            'middlewares' => [],
        ]);
    });
});

describe('Route Search', function () {
    it('findRouteByFastRouter [GOOD]', function () {
        $router = new Router(routerStub());

        $res = $router->findRouteByFastRouter('GET', '/blog/2');

        expect($res)->toBe([
            "class" => "Router\\stub\\ControllerStub",
            "method" => "blogListPage",
            "params" => [
                "id" => "2"
            ],
            "middlewares" => [
                "auth",
                "admin"
            ]
        ]);
    });

    it('findRouteByFastRouter [NOT_FOUND]: path', function () {
        $router = new Router(routerStub());

        $res = $router->findRouteByFastRouter('GET', '/wrong');

        expect($res)->toBe([]);
    });

    it('findRouteByFastRouter [METHOD_NOT_ALLOWED]: method', function () {
        $router = new Router(routerStub());

        $res = $router->findRouteByFastRouter('POST', '/about');

        expect($res)->toBe([]);
    });

    it('toRouteFromFastRoute [GOOD]', function () {
        $router = new Router(routerStub());
        $uri = '/blog/101';

        $item = $router->getRoute( 'GET', $uri);

        expect($item)->toBe([
            'class' => 'Router\\stub\\ControllerStub',
            'method' => 'blogListPage',
            'params' => ['id' => '101'],
            'middlewares' => ['auth', 'admin'],
        ]);
    });

    it('toRouteFromFastRoute [GOOD] resourceMethod', function () {
        $router = new Router(routerStub());
        $uri = '/';

        $item = $router->getRoute('GET', $uri);

        expect($item)->toBe([
            'class' => 'Router\\stub\\ControllerStub',
            'method' => 'getIndex',
            'params' => [],
            'middlewares' => [],
        ]);
    });
});

describe('Router utils', function () {
    it('url [name]', function () {
        $router = new Router(routerStub());
        $url = $router->url('Blog', ['id'=>'101']);
        expect($url)->toBe('/blog/101');
    });

    it('url [name] with Host', function () {
        $router = new Router(routerStub(), 'https://example.com');
        $url = $router->url('Blog', ['id'=>'102']);
        expect($url)->toBe('https://example.com/blog/102');
    });
});

describe('Route::getRoutesFromPaths', function () {
    it('[1]', function () {
        $list = Router::getRoutesFromPaths([
            __DIR__ . '/stub/routes_stub_1.php',
            __DIR__ . '/stub/routes_stub_2.php',
        ]);

        expect($list)->toBe(routerStub('mergedConfig'));
    });
});
