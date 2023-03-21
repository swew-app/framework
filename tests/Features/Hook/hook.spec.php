<?php

declare(strict_types=1);

use Swew\Framework\Hook\HK;
use Swew\Framework\Hook\Hook;

it('Hook', function () {
    $value = '0';

    Hook::on(
        HK::beforeInit,
        function () use (&$value) {
            $value = 'good';
        }
    );

    Hook::on(
        HK::beforeRun,
        function () use (&$value) {
            $value = 'bad';
        }
    );


    Hook::call(HK::beforeInit);

    expect($value)->toBe('good');
});
