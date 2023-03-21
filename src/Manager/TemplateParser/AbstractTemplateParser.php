<?php

declare(strict_types=1);

namespace Swew\Framework\Manager\TemplateParser;

abstract class AbstractTemplateParser
{
    abstract public function getExtension(): string;

    abstract public function render(array $viewFolders, string $filePath, array $data): string;
}
