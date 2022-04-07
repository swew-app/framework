<?php

declare(strict_types=1);

namespace SWEW\Framework\Http;

use SWEW\Framework\SwewApplication;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    private string $featureViewPath = '';

    private string $commonViewPath = '';

    private SwewApplication $app;

    // []: в зависимости от типа Request - выбирает тип ответа
    // []: если необходимо, то создает viewRenderer
    public function init(string $featureViewPath, string $commonViewPath, SwewApplication $app)
    {
        $this->featureViewPath = $featureViewPath;
        $this->commonViewPath = $commonViewPath;
        $this->app = $app;
    }

    public function view(string $file, $data = []): string
    {
        $filePath = $this->featureViewPath . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePath)) {
            return $this->app->renderView($filePath, $data);
        }

        $filePath = $this->commonViewPath . DIRECTORY_SEPARATOR . $file;

        if (file_exists($filePath)) {
            return $this->app->renderView($filePath, $data);
        }

        throw new \Error("Not found file '{$filePath}'");
    }
}
