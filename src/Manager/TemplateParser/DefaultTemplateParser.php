<?php

declare(strict_types=1);

namespace Swew\Framework\Manager\TemplateParser;

class DefaultTemplateParser extends AbstractTemplateParser
{
    public function getExtension(): string
    {
        return 'php';
    }

    public function render(array $viewFolders, string $filePath, array $data = []): string
    {
        ob_start();
        require($filePath);
        return ob_get_clean();
    }
}
