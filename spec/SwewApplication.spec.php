<?php

declare(strict_types=1);

use SWEW\Framework\SwewApplication;

describe("SwewApplication", function () {
    given('app', fn() => new SwewApplication());

    it("App->run [No exception]", function () {
        expect(
            fn() => $this->app->run()
        )->not->toThrow();
    });
});
