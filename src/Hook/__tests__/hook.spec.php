<?php

declare(strict_types=1);

use Swew\Framework\Hook\HK;
use Swew\Framework\Hook\Hook;

it('Hook', function (): void {
    $value = '0';

    Hook::on(
        HK::beforeInit,
        function () use (&$value): void {
            $value = 'good';
        },
    );

    Hook::on(
        HK::beforeRun,
        function () use (&$value): void {
            $value = 'bad';
        },
    );

    Hook::call(HK::beforeInit);

    expect($value)->toBe('good');
});
