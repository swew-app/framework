<?php

declare(strict_types=1);

use Swew\Framework\Router\Route;

it('Route: simple', function () {
    $route = new Route();
    $route->name('Blog');
    $route->path('/blog');

    expect($route->toArray())->toEqual([
        'name' => 'Blog',
        'path' => '/blog',
        'method' => 'GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD|CONNECT|TRACE',
        'middlewares' => [],
        'controller' => '',
    ]);
});


it('Route: with prefix', function () {
    $route = new Route();
    $route->name('Blog');
    $route->prefix('/recipe');
    $route->path('/desert');

    expect($route->toArray())->toEqual([
        'name' => 'Blog',
        'path' => '/recipe/desert',
        'method' => 'GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD|CONNECT|TRACE',
        'middlewares' => [],
        'controller' => '',
    ]);
});


it('Route: key filter', function () {
    $route = new Route();
    $route->name('Blog');
    $route->prefix('/recipe');
    $route->path('/desert');

    expect($route->toArray('name', 'path'))->toEqual([
        'name' => 'Blog',
        'path' => '/recipe/desert',
    ]);
});

it('Route: slug', function () {
    $route = new Route();
    $route->prefix('/recipe');
    $route->path('/desert');

    expect($route->toArray('name', 'path'))->toEqual([
        'name' => 'recipe-desert',
        'path' => '/recipe/desert',
    ]);
});
