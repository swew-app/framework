<?php

declare(strict_types=1);


use Swew\Testing\Integration\DemoApp;
use Swew\Framework\AppTest\AppTest;

it('DemoApp: save content in stream', function () {
    $app = new AppTest(DemoApp::class);

    $res = $app->call('GET', '/')->getResponse();

    expect($res->getBody()->getContents())->toBe('Hello world!');
});

it('DemoApp: admin page content', function () {
    $app = new AppTest(DemoApp::class);

    $res = $app->call('GET', '/admin')->getResponse();

    expect($res->getBody()->getContents())->toBe('Hello from Admin page');
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
