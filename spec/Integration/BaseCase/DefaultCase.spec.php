<?php

include_once 'stubs/base-stubs.php';

describe('Default Case', function () {
    it('addRoute', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->addRoute(getBaseStub());

        expect($appTest->app->router->routes)->toBe(getBaseStub());
    });

    it('Call Request "/about"', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->call('GET', '/about');

        $res = $appTest->getResponse();

        expect($res->getContent())->toBe('Hello world!');
    });

    it('Call Request "/blog/id"', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->ajax('GET', '/blog/101');

        $res = $appTest->getResponse();

        expect($res->getContent())->toBe('{"id":101}');
    });

    it('DTO Request "/blog"', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->ajax('POST', '/blog/102', [
            'text' => 'Hello',
        ]);

        $res = $appTest->getResponse();

        expect($res->getContent())->toBe('{"saved":true,"id":102,"text":"Hello"}');
    });
});
