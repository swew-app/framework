<?php

declare(strict_types=1);

namespace Swew\Framework\Manager;

use Swew\Framework\Base\BaseDTO;
use Swew\Framework\Manager\TemplateParser\AbstractTemplateParser;

final class FeatureManager
{
    private static ?string $controller = null;

    private static string $featurePath = '';

    public static bool $isCheckExists = true;

    public static string $defaultCommonFeature = 'Common';

    public static function setFeaturePath(string $featurePath): void
    {
        self::$featurePath = $featurePath;
    }

    public static function setController(string $controller): void
    {
        self::$controller = $controller;
    }

    public static function getView(string $file): string
    {
        $paths = self::getFeaturesViewPaths();

        foreach ($paths as $path) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        throw new \LogicException("File '$file'\nnot found in:\n- " . implode("\n- ", $paths));
    }

    // Templates for response

    public static function getPreparedResponse(): mixed
    {
        $data = responseState();

        if ($data instanceof BaseDTO) {
            $dto = $data;
            $data = $dto->getData();

            if (!$dto->isValid()) {
                $data['errors'] = [
                    'message' => $dto->getErrorMessage(),
                    'items' => $dto->getErrors(),
                ];
            }
        }

        $viewName = res()->getViewFileName();

        if (is_null($data) && empty($viewName)) {
            throw new \LogicException('Empty response');
        }

        if (req()->isAjax() || empty($viewName)) {
            res()->withHeader('Content-Type', 'application/json');

            if (is_array($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            }
        } else {
            $filePath = self::getView($viewName);

            /** @var array $data */
            $data = self::render($filePath, $data);
        }


        return $data;
    }

    /**
     * @var array<string, AbstractTemplateParser>
     */
    private static array $templateParser = [];

    public static function setTemplateParser(AbstractTemplateParser $parser): void
    {
        self::$templateParser[$parser->getExtension()] = $parser;
    }

    public static function render(string $filepath, array $data): string
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);

        if (count(self::$templateParser) === 0) {
            throw new \LogicException('Empty renrderer. Please set with "FeatureManager::setTemplateParser(...)"');
        }

        if (isset(self::$templateParser[$extension])) {
            return self::$templateParser[$extension]->render(
                self::getFeaturesViewPaths(),
                $filepath,
                $data
            );
        }

        return self::$templateParser['*']->render(
            self::getFeaturesViewPaths(),
            $filepath,
            $data
        );
    }

    public static function getFeaturesViewPaths(): array
    {
        $controller = self::$controller;
        $featDir = basename(self::$featurePath);
        $commonView = self::$featurePath . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'view';

        if (!empty($controller)) {
            $start = strpos($controller, $featDir . '\\') + strlen($featDir . '\\');
            $end = strpos($controller, '\\Controllers');

            /** @var int $end */
            $currentFeatureView = self::$featurePath . '/'
                . str_replace('\\', '/', substr($controller, $start, $end - $start))
                . '/view';

            return [
                $currentFeatureView,
                $commonView
            ];
        }

        return [
            $commonView,
        ];
    }
}
