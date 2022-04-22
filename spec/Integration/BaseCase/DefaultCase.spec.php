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

        $json =  [
            "data" => [
                "saved" => true,
                "id" => 102,
                "text" => "Hello"
            ],
            "message" => "",
            "error" => "",
            "errors" => []
        ];

        expect(
            json_decode($res->getContent(), true)
        )->toBe($json);
    });

    it('DTO validate data [GOOD]', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->ajax('POST', '/admin', [
            'name' => 'Leo',
            'text' => 'Hello world',
        ]);

        $res = $appTest->getResponse();

        $json = [
            "data" => [
                'name' => 'Leo',
                'text' => 'Hello world',
            ],
            "message" => "",
            "error" => "",
            "errors" => []
        ];


        expect(
            json_decode($res->getContent(), true)
        )->toBe($json);
    });

    it('DTO validate data [BAD]', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->ajax('POST', '/admin', [
            'text' => 'Hello',
        ]);

        $res = $appTest->getResponse();

        $json = [
            "data" => [
                'name' => '',
                'text' => 'Hello',
            ],
            "message" => "",
            "error" => "",
            "errors" => [
                "name" => "Field Name is required!!!"
            ]
        ];

        expect(
            json_decode($res->getContent(), true)
        )->toBe($json);
    });
});

describe('Case with Middleware', function () {
    it('Middleware CORS', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->ajax('GET', '/admin');

        $res = $appTest->getResponse();

        expect($res->headers->all('Access-Control-Max-Age'))->toBe(['86400']);
        expect($res->headers->all('x-test-after'))->toBe(['true']);
    });

    it('Middleware BREAK', function () {
        $appTest = new SWEW\Framework\AppTest\AppTest();

        $appTest->setApp(getBaseStub('app'));

        $appTest->ajax('GET', '/admin/not-allowed');

        $res = $appTest->getResponse();

        expect($res->headers->all('Access-Control-Max-Age'))->not->toBe(['86400']);
    });
});
