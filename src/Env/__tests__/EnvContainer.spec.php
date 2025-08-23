<?php

declare(strict_types=1);

use Swew\Framework\Env\EnvContainer;

beforeEach(function (): void {
    EnvContainer::removeInstance();
});

it('Env Parse', function (string $text, array $vars): void {
    $env = EnvContainer::getInstance();
    $env->parse($text);

    expect($env->get('*'))->toMatchArray($vars);
})->with([
    ["\nID=1\n", ['ID' => 1]],
    ['ID=2.1', ['ID' => 2.1]],
    ["ID='3'", ['ID' => '3']],
    ['ID="4"', ['ID' => '4']],
    ['ID = no_space', ['ID' => 'no_space']],
    ['ID=null', ['ID' => null]],
    ["# comment 1 \nID=no_comment # comment 2", ['ID' => 'no_comment']],
]);

it('Evn Load Global envs', function (): void {
    $env = EnvContainer::getInstance(true);

    expect($env->get('HOME'))->toBeTruthy();
});

/*/
 * # Test only for manual checking
 * it('Env load from file', function () {
 * $env = EnvContainer::getInstance();
 * $env->loadFromFile(__DIR__ . DIRECTORY_SEPARATOR . '.env.test');
 *
 * expect($env->get('TEST_VAR_1'))->toBe(true);
 * });
 * //*/

/*/
 * # Test only for manual checking cache
 * it('Env Cache', function () {
 * $env = EnvContainer::getInstance();
 *
 * $env->useCache(
 * true,
 * __DIR__ . DIRECTORY_SEPARATOR . 'cache_for_test.php'
 * );
 *
 * //    $env->set('TEST_CACHE', 'WORK'); // Uncomment for create cache file and rerun test
 *
 * expect($env->get('TEST_CACHE'))->toBe('WORK');
 * });
 * //*/
