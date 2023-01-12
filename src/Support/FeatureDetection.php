<?php

declare(strict_types=1);

namespace Swew\Framework\Support;

final class FeatureDetection
{
    private static ?string $controller = null;

    public static bool $isCheckExists = true;

    public static string $defaultCommonFeature = 'Common';

    public static function setController(string $controller)
    {
        self::$controller = $controller;
    }

    public static function getView(string $featurePath, string $viewFile, string $featureName = ''): string
    {
        if (is_null(self::$controller)) {
            throw new \LogicException('Call FeatureDetection::setController in App file');
        }

        $featDirPath = str_replace(['\\', '/', '||'], '/', $featurePath);

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
                return self::getView($featurePath, $viewFile, self::$defaultCommonFeature);
            }

            throw new \LogicException("Not found view:\n $featViewPath");
        }

        return $featViewPath;
    }
}
