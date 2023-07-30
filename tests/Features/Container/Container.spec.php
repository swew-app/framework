<?php

use Swew\Framework\Container\Container;
use Swew\Testing\Features\Container\TestAssets\AutoWiring;
use Swew\Testing\Features\Container\TestAssets\AutoWiringSimple;
use Swew\Testing\Features\Container\TestAssets\DummyData;
use Swew\Testing\Features\Container\TestAssets\DummyEmpty;
use Swew\Testing\Features\Container\TestAssets\DummyName;

it('SetAndGetScalarDefinition', function () {
    $container = new Container();

    $container->set($id = 'integer', $definition = 5);
    expect($definition)->toBe($container->get($id));

    $container->set($id = 'float', $definition = 3.7);
    expect($definition)->toBe($container->get($id));

    $container->set($id = 'boolean', $definition = false);
    expect($definition)->toBe($container->get($id));

    $container->set($id = 'string', $definition = 'string');
    expect($definition)->toBe($container->get($id));
});

it('SetAndGetArrayDefinition', function () {
    $container = new Container();

    $container->set($id = 'empty', $definition = []);
    expect($definition)->toBe($container->get($id));

    $container->set($id = 'array', $definition = ['array']);
    expect($definition)->toBe($container->get($id));

    $container->set($id = 'nested', $definition = [
        'nested' => [
            'scalar' => [
                'integer' => 5,
                'float' => 3.7,
                'boolean' => false,
                'string' => 'string',
            ],
            'not_scalar' => [
                'object' => new StdClass(),
                'array' => ['array'],
                'closure' => fn() => null,
            ],
        ],
    ]);
    expect($definition)->toBe($container->get($id));
});

it('SetAndGetArrayWithPath', function () {
    $container = new Container();

    $container->set($id = 'subData', $definition = [
        'nested' => [
            'scalar' => [
                'integer' => 5,
                'float' => 3.7,
                'boolean' => false,
                'string' => 'Leo',
            ],
            'not_scalar' => [
                'object' => new StdClass(),
                'array' => ['array'],
                'closure' => fn() => null,
            ],
        ],
    ]);

    expect($container->get('subData.nested.scalar.string'))->toBe('Leo');

    expect(fn() => $container->get('subData.nested.wrong path'))
        ->toThrow('`subData.nested.wrong path` is not set in container and is not a class name.');
});

it('SetAndGetObjectAndClosureDefinitionBasicUsage', function () {
    $container = new Container();

    $container->set($id = DummyData::class, $definition = new DummyData(new DummyName()));
    expect($definition)->toBe($container->get($id));

    $container->set(DummyData::class, DummyData::class);
    expect($container->get(DummyData::class))->toBeInstanceOf(DummyData::class);
});

it('GetSameObject', function () {
    $container = new Container();

    class InvokeFunction
    {
        public function __invoke()
        {
            return new DummyData((new DummyName('John')), microtime(true));
        }
    }

    $container->set(DummyData::class, InvokeFunction::class);

    expect($instance1 = $container->get(DummyData::class))->not()->toBeNull();
    expect($instance2 = $container->get(DummyData::class))->not()->toBeNull();

    expect($instance1->getName()->get())->toBe($instance2->getName()->get());
    expect($instance1->getName())->toBe($instance2->getName());
    expect($instance1->getTime())->toBe($instance2->getTime());
    expect($instance1)->toBe($instance2);
});

it('GetNewObject', function () {
    $container = new Container();

    $container->set(DummyData::class, function () {
        return new DummyData(new DummyName('John'), microtime(true));
    });

    expect($instance1 = $container->getNew(DummyData::class))->not()->toBeNull();
    expect($instance2 = $container->getNew(DummyData::class))->not()->toBeNull();

    expect($instance1)->not()->toBe($instance2);
    expect($instance1->getName())->not()->toBe($instance2->getName());
    expect($instance1->getTime())->not()->toBe($instance2->getTime());

    expect($instance1->getName()->get())->toBe($instance2->getName()->get());
});

it('ConstructorWithPassDefinitions', function () {
    $container = new Container([
        $integerId = 'integer' => $integerDefinition = 5,
        $floatId = 'float' => $floatDefinition = 3.7,
        $booleanId = 'boolean' => $booleanDefinition = false,
        $stringId = 'string' => $stringDefinition = 'string',
        $arrayId = 'array' => $arrayDefinition = ['array'],
        $objectId = 'object' => $objectDefinition = new StdClass(),
        $closureId = 'closure' => fn () => null,
    ]);

    expect($integerDefinition)->toBe($container->get($integerId));
    expect($floatDefinition)->toBe($container->get($floatId));
    expect($booleanDefinition)->toBe($container->get($booleanId));
    expect($stringDefinition)->toBe($container->get($stringId));
    expect($arrayDefinition)->toBe($container->get($arrayId));
    expect($objectDefinition)->toBe($container->get($objectId));

    expect(is_callable($container->get($closureId)))->toBe(true);
});

it('SetMultiple', function () {
    $container = new Container();
    $definitions = [
        $integerId = 'integer' => $integerDefinition = 5,
        $floatId = 'float' => $floatDefinition = 3.7,
        $booleanId = 'boolean' => $booleanDefinition = false,
        $stringId = 'string' => $stringDefinition = 'string',
        $arrayId = 'array' => $arrayDefinition = ['array'],
        $objectId = 'object' => $objectDefinition = new StdClass(),
        $closureId = 'closure' => fn () => null,
    ];

    $container->setMultiple($definitions);

    expect($integerDefinition)->toBe($container->get($integerId));
    expect($floatDefinition)->toBe($container->get($floatId));
    expect($booleanDefinition)->toBe($container->get($booleanId));
    expect($stringDefinition)->toBe($container->get($stringId));
    expect($arrayDefinition)->toBe($container->get($arrayId));
    expect($objectDefinition)->toBe($container->get($objectId));

    expect(is_callable($container->get($closureId)))->toBe(true);
});

it('Has', function () {
    $container = new Container();

    $container->set('definitionId', 'definition');

    expect($container->has('definitionId'))->toBeTrue();
    expect($container->has('definitionNotExist'))->toBeFalse();
});

it('AutoWiring', function () {
    $container = new Container();
    $autoWiring = $container->get(AutoWiring::class);


    expect($autoWiring->getDummyData())->toBeInstanceOf(DummyData::class);
    expect($autoWiring->getDummyData()->getName())->toBeInstanceOf(DummyName::class);

    expect('Test Name')->toBe($autoWiring->getDummyData()->getName()->get());

    expect($autoWiring->getDummyData()->getTime())->toBeNull();

    expect([])->toBe($autoWiring->getArray());
    expect(100)->toBe($autoWiring->getInt());
    expect('string')->toBe($autoWiring->getString());
});

it('Cache', function () {
    $container = new Container();
    $item1 = $container->get(AutoWiringSimple::class);
    $item2 = $container->get(AutoWiringSimple::class);


    expect($item1)->toBe($item2);

    expect($container->getCacheData())
        ->toMatchArray([
            DummyName::class => ['Test Name'],
            DummyEmpty::class => [],
            AutoWiringSimple::class => [DummyName::class, DummyEmpty::class],
        ]);
});

/*
# Test only for manual checking cache
it('Cache To File', function () {
    $container = new Container();

    $container->useCache(
        true,
        __DIR__ . DIRECTORY_SEPARATOR . 'cache_for_test.php'
    );

    $item1 = $container->get(AutoWiringSimple::class);
    $item2 = $container->get(AutoWiringSimple::class);

    expect($item1)->toBe($item2);

    expect($container->getCacheData())
        ->toMatchArray([
            DummyName::class => ['Test Name'],
            DummyEmpty::class => [],
            AutoWiringSimple::class => [DummyName::class, DummyEmpty::class]
        ]);
});
//*/
