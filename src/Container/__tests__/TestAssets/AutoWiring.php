<?php

namespace Swew\Framework\Container\__tests__\TestAssets;

class AutoWiring
{
    /**
     * @param  DummyData  $dummyData
     * @param  array  $array
     * @param  int  $int
     * @param  string  $string
     */
    public function __construct(private readonly DummyData $dummyData, private readonly array $array, private readonly int $int = 100, private readonly string $string = 'string')
    {
    }

    /**
     * @return DummyData
     */
    public function getDummyData(): DummyData
    {
        return $this->dummyData;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return $this->array;
    }

    /**
     * @return int
     */
    public function getInt(): int
    {
        return $this->int;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }
}
