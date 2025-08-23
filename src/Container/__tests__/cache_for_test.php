<?php

declare(strict_types=1);

return [
    'Swew\\Framework\\Container\\tests\\TestAssets\\DummyName' => [
        0 => 'Test Name',
    ],
    'Swew\\Framework\\Container\\tests\\TestAssets\\DummyEmpty' => [],
    'Swew\\Framework\\Container\\tests\\TestAssets\\AutoWiringSimple' => [
        0 => 'Swew\\Framework\\Container\\tests\\TestAssets\\DummyName',
        1 => 'Swew\\Framework\\Container\\tests\\TestAssets\\DummyEmpty',
    ],
];
