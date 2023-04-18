<?php

declare(strict_types=1);

use Swew\Framework\Manager\FeatureManager;
use Swew\Framework\Manager\TemplateParser\DefaultTemplateParser;
//
//it('FeatureDetection [Special feature]', function () {
//    FeatureManager::$isCheckExists = false;
//
//    FeatureManager::setController('App\\Features\\Auth\\Controllers\\MainController');
//    FeatureManager::setFeaturePath('/Users/Code/dev/swew/swew-fw/src/App/Features');
//
//    $view = FeatureManager::getView('pages/home.php');
//
//    expect($view)->toBe('/Users/Code/dev/swew/swew-fw/src/App/Features/Auth/view/pages/home.php');
//});
//
//it('FeatureDetection [Default feature exception]', function () {
//    FeatureManager::$isCheckExists = true;
//
//    FeatureManager::setController('App\\Features\\Auth\\Controllers\\MainController');
//    FeatureManager::setFeaturePath('/Users/Code/dev/swew/swew-fw/src/App/Features');
//
//    $callback = fn() => FeatureManager::getView('pages/home.php');
//
//    expect($callback)->toThrow();
//});
//
//it('FeatureDetection [template parser]', function () {
//    FeatureManager::setTemplateParser(new DefaultTemplateParser());
//
//    $result = FeatureManager::render(__DIR__ . '/stubs/Feature/AdminPanel/view/pages/main-page.html', []);
//
//    expect($result)->toContain('<h1>Hello main test page in admin panel</h1>');
//});

it('FeatureDetection [getView]', function () {
    FeatureManager::setController('');
    $featDir = __DIR__ . '/stubs/Feature';
    FeatureManager::setFeaturePath($featDir);

    $path = FeatureManager::getView('errors/404.html');

    expect($path)
        ->toBe($featDir . '/Common/view/errors/404.html');
});

it('FeatureDetection [getFeaturesViewPaths]', function () {
    FeatureManager::setController('App\\Features\\Auth\\Controllers\\MainController');
    FeatureManager::setFeaturePath('/Users/Code/dev/swew/swew-fw/src/App/Features');

    $res = FeatureManager::getFeaturesViewPaths();

    expect($res)->toBe([
        '/Users/Code/dev/swew/swew-fw/src/App/Features/Auth/view',
        '/Users/Code/dev/swew/swew-fw/src/App/Features/Common/view',
    ]);
});

it('FeatureDetection [getFeaturesViewPaths]', function () {
    FeatureManager::setController('');
    FeatureManager::setFeaturePath('/Users/Code/dev/swew/swew-fw/src/App/Features');

    $res = FeatureManager::getFeaturesViewPaths();

    expect($res)->toBe([
        '/Users/Code/dev/swew/swew-fw/src/App/Features/Common/view',
    ]);
});
