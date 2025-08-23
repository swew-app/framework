<?php

declare(strict_types=1);

namespace Swew\Framework\Manager\TemplateParser;

class DefaultTemplateParser extends AbstractTemplateParser
{
    #[\Override]
    public function getExtension(): string
    {
        return 'php';
    }

    #[\Override]
    /**
     * @return string
     */
    public function render(array $viewFolders, string $filePath, array $data = []): string
    {
        ob_start();
        /** @psalm-suppress UnresolvableInclude */
        require $filePath;
        $result = ob_get_clean();

        return $result === false ? '' : $result;
    }
}
