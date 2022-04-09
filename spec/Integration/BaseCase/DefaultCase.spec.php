<?php

include_once 'stubs/base-stubs.php';

describe('Default Case', function () {
    it('addRoute', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->addRoute(getBaseStub());

        expect($appTest->app->router->routes)->toBe(getBaseStub());
    });

    it('Call Request', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->call('GET', '/about');

        $res = $appTest->getResponse();

        expect($res->getContent())->toBe('Hello world!');
    });
});
