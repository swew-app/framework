<?php

declare(strict_types=1);

use Swew\Framework\Support\FeatureDetection;

it('FeatureDetection [Special feature]', function () {
    FeatureDetection::$isCheckExists = false;

    FeatureDetection::setController('App\\Features\\Auth\\Controllers\\MainController');

    $view = FeatureDetection::getView('/Users/Code/dev/swew/swew-fw/src/App/Features', 'pages/home.php');

    expect($view)->toBe('/Users/Code/dev/swew/swew-fw/src/App/Features/Auth/view/pages/home.php');
});

it('FeatureDetection [Default feature exception]', function () {
    FeatureDetection::$isCheckExists = true;

    FeatureDetection::setController('App\\Features\\Auth\\Controllers\\MainController');

    $callback = fn () =>
        FeatureDetection::getView('/Users/Code/dev/swew/swew-fw/src/App/Features', 'pages/home.php');

    expect($callback)->toThrow('', "Not found view:\n /Users/Code/dev/swew/swew-fw/src/App/Features/Common/view/pages/home.php");
});
