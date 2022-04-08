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
            FastRoute\Dispatcher::FOUND,
            'Router\\stub\\ControllerStub@blogListPage|auth|admin',
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
            'middlewares' => ['auth', 'admin'],
        ]);
    });

    it('toRouteFromFastRoute [GOOD] resourceMethod', function () {
        $router = new Router(routerStub());
        $uri = '/';

        $r = $router->findRouteByFastRouter('POST', $uri);

        $item = $router->toRouteFromFastRoute($r, 'POST', $uri);

        expect($item)->toBe([
            'class' => 'Router\\stub\\ControllerStub',
            'method' => 'postIndex',
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
