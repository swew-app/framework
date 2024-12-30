<?php

declare(strict_types=1);

use Swew\Framework\Router\RouteHelper;
use Swew\Framework\Router\Router;

include_once 'stub/router_stubs.php';

it('validate [GOOD]', function () {
    $router = new Router([
        [
            'path' => '/',
            'name' => 'MainPage',
            'middlewares' => [],
            'controller' => [],
        ]
    ]);


    expect($router->validate())->toBe(true);
});

it('validate [BAD 1]: Has wrong key', function () {
    $router = new Router([
        [
            'path' => '/',
            'name' => 'MainPage',
            'middlewares' => [],
            'controller' => [],
            'someKey' => '',
        ]
    ]);


    expect(fn () => $router->validate())->toThrow(Exception::class, "Not allowed key 'someKey' in router");
});

it('validate [BAD 2]: Same Name', function () {
    $router = new Router([
        [
            'path' => '/',
            'name' => 'MainPage',
            'controller' => [],
        ],
        [
            'path' => '/about',
            'name' => 'MainPage',
            'controller' => [],
        ],
    ]);

    expect(fn () => $router->validate())->toThrow(Exception::class, "Route name 'MainPage' already used");
});

it('validate [BAD 3]: Empty Controller', function () {
    $router = new Router([
        [
            'path' => '/',
            'controller' => [],
        ],
    ]);

    expect(fn () => $router->validate())->toThrow(Exception::class, "Route key 'name' is required");
});

it('validate [BAD 4]: Empty Name', function () {
    $router = new Router([
        [
            'path' => '/',
            'name' => 'Main',
        ],
    ]);

    expect(fn () => $router->validate())->toThrow(Exception::class, "Route key 'controller' is required");
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

    $item = $router->getRoute('GET', $uri);

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

it('url [name]', function () {
    $router = new Router(routerStub());
    $url = $router->url('Blog', ['id' => '101']);
    expect($url)->toBe('/blog/101');
});

it('url [name] with Host', function () {
    $router = new Router(routerStub(), 'https://example.com');
    $url = $router->url('Blog', ['id' => '102']);
    expect($url)->toBe('https://example.com/blog/102');
});

it('Route::getRoutesFromPaths [1]', function () {
    $list = Router::getRoutesFromPaths([
        __DIR__ . '/stub/routes_stub_1.php',
        __DIR__ . '/stub/routes_stub_2.php',
    ]);

    expect($list)->toBe(routerStub('mergedConfig'));
});

it('Route: add by Route class', function () {
    $router = new Router([]);
    $route = new RouteHelper();
    $route->name('single-route');
    $route->path('/single/page');
    $route->controller('DemoControllerStub');

    $router->addRoute($route);

    $item = $router->getRoute('GET', '/single/page');

    expect($item)->toBe([
        'class' => 'DemoControllerStub',
        'method' => 'getIndex',
        'params' => [],
        'middlewares' => [],
    ]);
});

it('Route: Get method path', function () {
    $router = new Router([]);
    $route = new RouteHelper();
    $route->name('single-route');
    $route->path('/single/page');
    $route->controller('DemoSecondControllerStub');

    $router->addRoute($route);

    //    $item = $router->getRoute('GET', '/single/page');
    $item = $router->getRoute('GET', '/single/page/load-data');

    expect($item)->toBe([
        'class' => 'DemoSecondControllerStub',
        'method' => 'getIndex',
        'params' => [],
        'middlewares' => [],
    ]);
});

it('Router with cache', function () {
    $router = new Router(routerStub());

    $cachePath = __DIR__ . '/route.cache';

    $router->useCache($cachePath);
    // initialize cache file
    $router->findRouteByFastRouter('GET', '/blog/2');

    // check if file $cachePath exists
    expect(file_exists($cachePath))->toBe(true);

    // remove file after tests
    unlink($cachePath);
});
