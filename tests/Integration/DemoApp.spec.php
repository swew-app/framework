<?php

declare(strict_types=1);


use Swew\Testing\Integration\DemoApp;
use Swew\Framework\AppTest\AppTest;

it('DemoApp: save content in stream', function () {
    $app = new AppTest(DemoApp::class);

    $res = $app->call('GET', '/')->getResponse();

    expect($res->getBody()->getContents())->toBe('Hello world!');
});

it('DemoApp: admin page content by Ajax', function () {
    $app = new AppTest(DemoApp::class);

    $res = $app->ajax('GET', '/admin')->getResponse();

    expect($res->getBody()->getContents())->toBe('{"message":"Hello from Admin page"}');

    expect($res->getHeader('Content-Type'))->toBe(['application/json']);
});

it('DemoApp: admin child', function () {
    $app = new AppTest(DemoApp::class);

    $res = $app->call('GET', '/admin/manager')->getResponse();

    expect($res->getBody()->getContents())->toBe('Hello from Dashboard');
});

it('DemoApp: CORS global middleware', function () {
    $app = new AppTest(DemoApp::class);

    $res = $app->call('GET', '/admin')->getResponse();

    expect($res->getHeader('Access-Control-Max-Age'))->toBe(['86400']);
});

it('DemoApp: Break middleware', function () {
    $app = new AppTest(DemoApp::class);

    $res = $app->call('GET', '/some-page')->getResponse();

    expect($res->getStatusCode())->toBe(402);
});
