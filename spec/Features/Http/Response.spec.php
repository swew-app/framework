<?php

declare(strict_types=1);

use SWEW\Framework\Http\Response;

describe('Response', function () {
    it('setResponseConfig [html] rest default', function () {
        $res = new Response();
        $res->setResponseConfig(['text/html']);
        expect($res->responseType->type())->toBe('html');
    });

    it('setResponseConfig [json] 1', function () {
        $res = new Response();
        $res->setResponseConfig(['application/json']);
        expect($res->responseType->type())->toBe('json');
    });

    it('setResponseConfig [json] 2', function () {
        $res = new Response();
        $res->setResponseConfig(['application/javascript']);
        expect($res->responseType->type())->toBe('json');
    });
});
