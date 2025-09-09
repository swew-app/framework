<?php

declare(strict_types=1);

namespace Swew\Framework\Manager;

use Swew\Framework\Http\Response;
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
            $filePathDefault = $path . DIRECTORY_SEPARATOR . $file;

            $filePaths = [
                $filePathDefault,
            ];

            $extensions = array_keys(self::$templateParser);

            foreach ($extensions as $ext) {
                $filePaths[] = $filePathDefault . '.' . $ext;
            }

            foreach ($filePaths as $filePath) {
                if (file_exists($filePath)) {
                    return $filePath;
                }
            }
        }

        throw new \LogicException("File '{$file}'\nnot found in:\n- " . implode("\n- ", $paths));
    }

    // Templates for response

    public static function getPreparedResponse(mixed $dataFromRoute): string
    {
        $raw = res()->getRaw();

        if (is_string($raw)) {
            return $raw;
        }

        $data = res()->getStoredData() ?? $dataFromRoute;

        $viewName = res()->getViewFileName();

        if (is_null($data) && empty($viewName)) {
            if (res()->getStatusCode() >= 300) {
                $data = '';
            } else {
                throw new \LogicException('Empty response');
            }
        }

        if (req()->isAjax() || empty($viewName)) {
            res()->withHeader('Content-Type', 'application/json');

            if ($data === null) {
                $data = res()->getViewData();
            }

            if (is_array($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            }
        } else {
            $filePath = self::getView($viewName);

            $data = self::render($filePath, array_merge(res()->getViewData(), ['data' => $data]));
        }

        if (env('__TEST__') && $data === null) {
            throw new \LogicException('Empty response.');
        }

        return (string) ($data ?? '');
    }

    /**
     * @var array<string, AbstractTemplateParser>
     */
    private static array $templateParser = [];

    public static function setTemplateParser(AbstractTemplateParser $parser): void
    {
        self::$templateParser[$parser->getExtension()] = $parser;
    }

    public static function render(string $filepath, string|array|null $data): string
    {
        if (is_string($data)) {
            $data = [$data];
        } elseif (is_null($data)) {
            $data = [];
        }

        $extension = self::findMatchingExtension($filepath);

        if (is_null($extension)) {
            throw new \LogicException("Can't parse extension from file '{$filepath}'");
        }

        if (count(self::$templateParser) === 0) {
            throw new \LogicException('Empty renderer. Please set with "FeatureManager::setTemplateParser(...)"');
        }

        if (isset(self::$templateParser[$extension])) {
            return self::$templateParser[$extension]->render(self::getFeaturesViewPaths(), $filepath, $data);
        }

        return self::$templateParser['*']->render(self::getFeaturesViewPaths(), $filepath, $data);
    }

    public static function findMatchingExtension(string $filename): ?string
    {
        $extensions = array_keys(self::$templateParser);

        foreach ($extensions as $extension) {
            // Check if the extension is present at the end of the filename
            if (str_ends_with($filename, $extension)) {
                return $extension;
            }
        }

        // Return null if no match is found
        return null;
    }

    #[\Deprecated]
    public static function getFeaturesViewPaths(): array
    {
        $controller = self::$controller;
        $featDir = basename(self::$featurePath);
        $commonView = self::$featurePath . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'view';

        if ($controller !== null) {
            $start = (strpos($controller, $featDir . '\\') ?: 0) + strlen($featDir . '\\');
            $end = strpos($controller, '\\Controllers');

            if (is_bool($end)) {
                $end = $start;
            }

            $currentFeatureView = self::$featurePath . '/' . str_replace('\\', '/', substr($controller, $start, $end - $start)) . '/view';

            return array_unique([$currentFeatureView, $commonView]);
        }

        return [$commonView];
    }
}
