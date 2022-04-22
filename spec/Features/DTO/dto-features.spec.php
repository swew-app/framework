<?php

use SWEW\Framework\Base\BaseDTO;

include_once 'dto_stubs.php';

describe('DTO', function () {
    it('instanceOf BasDTO', function () {
        $dto = dto_stub('message');

        expect($dto instanceof BaseDTO)->toBe(true);
    });

    it('getData', function () {
        $dto = dto_stub('message');

        expect($dto->getData())->toBe([
            'message' => 'Hello DTO'
        ]);
    });


    describe('VALIDATE:', function () {
        it('setData [NO VALIDATE]', function () {
            $dto = dto_stub('message');

            $dto->setData(['message' => 'Hi SWEW']);

            expect($dto->getData())->toBe([
                'message' => 'Hi SWEW'
            ]);
        });

        it('setData [VALIDATE - ERROR]', function () {
            $dto = dto_stub('validate');

            //  Invalid value
            $dto->setData(['message' => 'Hi']);

            expect($dto->isValid())->toBe(false);
            expect($dto->getErrors())->toBe(['message' => 'The Message minimum is 5']);
            expect($dto->getData())->toBe(['message' => 'Hello DTO']);

            //  Valid value
            $dto->setData(['message' => 'Hi SWEW']);

            expect($dto->isValid())->toBe(true);
            expect($dto->getErrors())->toBe([]);
            expect($dto->getData())->toBe(['message' => 'Hi SWEW']);
        });
    });
});
