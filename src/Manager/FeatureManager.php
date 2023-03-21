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

    public static function getView(string $viewFile, string $featureName = ''): string
    {
        if (is_null(self::$controller)) {
            throw new \LogicException('Call FeatureDetection::setController in App file');
        }

        $featDirPath = str_replace(['\\', '/', '||'], '/', self::$featurePath);

        if ($featureName === '') {
            $contrPath = str_replace(['\\', '/', '||'], '/', self::$controller);
            $contrArr = explode('/', $contrPath);
            $featDir = basename($featDirPath);
            $index = array_search($featDir, $contrArr);
            if (!is_int($index)) {
                throw new \LogicException("Wrong path for controller: '$contrPath'");
            }
            $feature = $contrArr[$index + 1];
        } else {
            $feature = $featureName;
        }

        $featViewPath = $featDirPath . DIRECTORY_SEPARATOR
            . $feature . DIRECTORY_SEPARATOR
            . 'view' . DIRECTORY_SEPARATOR . $viewFile;

        if (self::$isCheckExists) {
            if (file_exists($featViewPath)) {
                return $featViewPath;
            }

            if ($featureName === '') {
                return self::getView($viewFile, self::$defaultCommonFeature);
            }

            throw new \LogicException("Not found view:\n $featViewPath");
        }

        return $featViewPath;
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
            $filePath = self::getView(self::$featurePath, $viewName);

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
