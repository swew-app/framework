<?php


use SWEW\Framework\Support\Str;

describe('Str', function () {
    it('camelCase', function () {
        $list = [
            'index index' => 'indexIndex',
            'index-index' => 'indexIndex',
            'index_index' => 'indexIndex',
            'index/index' => 'indexIndex',
            'zero-one-two' => 'zeroOneTwo',
        ];

        foreach ($list as $from => $to) {
            expect(Str::camelCase($from))->toBe($to);
        }
    });
});
