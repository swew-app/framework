<?php

namespace Swew\Testing\Features\Container\TestAssets;

class AutoWiringSimple
{
    public function __construct(
        public DummyName $dummyData,
        public DummyEmpty $dummyEmpty
    ) {
    }
}
