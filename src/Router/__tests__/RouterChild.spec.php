<?php

declare(strict_types=1);

use Swew\Framework\Router\Router;

include_once 'stub/router_stubs.php';

it('Router: child', function () {
    $router = new Router(routerStub('child'));

    $res = $router->getRoutes();

    expect($res)->toMatchArray([
        [
            'name' => 'MainPage',
            'path' => '/',
            'controller' => 'SomeClass::class',
        ],
        [
            'name' => 'AboutPage',
            'path' => '/about',
            'controller' => 'SomeClass::class',
        ],
        [
            'name' => 'AddressPage',
            'path' => '/about/address',
            'controller' => 'SomeClass::class',
        ],
    ]);
});


it('Router: method from request', function () {
    $router = new Router(routerStub('methodRoute'));

    $item = $router->getRoute('GET', '/about');

    expect($item['method'])->toBe('getIndex');
});
