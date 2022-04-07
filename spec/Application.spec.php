<?php

use SWEW\Framework\SwewApplication;

describe("SwewApplication", function () {
    given('app', fn() => new SwewApplication());

    it("[ 1 ]: No exception", function () {
        expect($this->app->run() instanceof SwewApplication)
            ->toBe(true);
    });
});
