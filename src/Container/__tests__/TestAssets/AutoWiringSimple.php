<?php

namespace Swew\Framework\Container\__tests__\TestAssets;

class AutoWiringSimple
{
    public function __construct(
        public DummyName $dummyData,
        public DummyEmpty $dummyEmpty,
    ) {}
}
