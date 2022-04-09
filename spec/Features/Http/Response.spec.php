<?php


use SWEW\Framework\Http\Response;
use SWEW\Framework\Http\RespType;

describe('Response', function () {
    it('setResponseConfig [html] rest default', function () {
        $res = new Response();
        $res->setResponseConfig('text/html');
        expect($res->responseType->type())->toBe('html');
    });

    it('setResponseConfig [json]', function () {
        $res = new Response();
        $res->setResponseConfig('application/json');
        expect($res->responseType->type())->toBe('json');
    });

    it('setResponseConfig [json]', function () {
        $res = new Response();
        $res->setResponseConfig('application/javascript');
        expect($res->responseType->type())->toBe('json');
    });
});
