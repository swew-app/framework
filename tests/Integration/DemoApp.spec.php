<?php

declare(strict_types=1);


use Swew\Testing\Integration\DemoApp;
use Swew\Framework\AppTest\AppTest;

it('DemoApp: save content in stream', function () {
    $app = new AppTest(DemoApp::class);

    $app->call('GET', '/');

    expect($app->getResponse()->getBody()->getContents())->toBe('Hello world!');
});
