<?php

declare(strict_types=1);

use Swew\Framework\Http\RequestWrapper;

it('RequestWrapper mapTo from Object', function () {

    class ModelStub
    {
        public string $name;
        public int $age;
    }

    RequestWrapper::removeInstance();

    $req = RequestWrapper::getInstance('POST', '/');

    $req->withParsedBody(['name' => 'Leo', 'age' => 37]);

    /** @var ModelStub $model */
    $model = $req->mapTo(new ModelStub());

    expect($model->name)->toBe('Leo');
    expect($model->age)->toBe(37);
});

it('RequestWrapper mapTo from className', function () {

    class ModelStub2
    {
        public string $name;
        public int $age;
    }

    RequestWrapper::removeInstance();

    $req = RequestWrapper::getInstance('POST', '/');

    $req->withParsedBody(['name' => 'Leo', 'age' => 37]);

    /** @var ModelStub2 $model */
    $model = $req->mapTo(ModelStub2::class);

    expect($model->name)->toBe('Leo');
    expect($model->age)->toBe(37);
});
