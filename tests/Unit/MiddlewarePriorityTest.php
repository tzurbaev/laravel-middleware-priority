<?php

declare(strict_types=1);

use Illuminate\Foundation\Configuration\Middleware;
use Laniakea\MiddlewarePriority\DefaultMiddlewarePriority;
use Laniakea\MiddlewarePriority\MiddlewarePriorityManager;

it('should create manager with empty priority list', function () {
    $manager = new MiddlewarePriorityManager(new Middleware());

    expect($manager->getPriority())->toBeEmpty();
});

it('should create manager with default Laravel priority list', function () {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware());

    expect($manager->getPriority())->toBe(DefaultMiddlewarePriority::get());
});

it('should create manager with user-defined default priority list', function () {
    $defaults = [
        'FirstMiddleware',
        'SecondMiddleware',
        'ThirdMiddleware',
    ];

    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $defaults);

    expect($manager->getPriority())->toBe($defaults);
});

it('should prepend middleware to the priority list', function (array $default, string|array $middleware, array $expected) {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $default);
    $manager->prepend($middleware);

    expect($manager->getPriority())->toBe($expected);
})->with([
    [
        'default' => [],
        'middleware' => 'FirstMiddleware',
        'expected' => ['FirstMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware'],
        'middleware' => 'SecondMiddleware',
        'expected' => ['SecondMiddleware', 'FirstMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware'],
        'middleware' => ['ThirdMiddleware', 'FourthMiddleware'],
        'expected' => ['ThirdMiddleware', 'FourthMiddleware', 'FirstMiddleware', 'SecondMiddleware'],
    ],
]);

it('should append middleware to the priority list', function (array $default, string|array $middleware, array $expected) {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $default);
    $manager->append($middleware);

    expect($manager->getPriority())->toBe($expected);
})->with([
    [
        'default' => [],
        'middleware' => 'FirstMiddleware',
        'expected' => ['FirstMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware'],
        'middleware' => 'SecondMiddleware',
        'expected' => ['FirstMiddleware', 'SecondMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware'],
        'middleware' => ['ThirdMiddleware', 'FourthMiddleware'],
        'expected' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware', 'FourthMiddleware'],
    ],
]);

it('should add middleware before another middleware in the priority list', function (array $default, string $before, string|array $middleware, array $expected) {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $default);
    $manager->before($before, $middleware);

    expect($manager->getPriority())->toBe($expected);
})->with([
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware'],
        'before' => 'SecondMiddleware',
        'middleware' => 'ThirdMiddleware',
        'expected' => ['FirstMiddleware', 'ThirdMiddleware', 'SecondMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'before' => 'SecondMiddleware',
        'middleware' => ['FourthMiddleware', 'FifthMiddleware'],
        'expected' => ['FirstMiddleware', 'FourthMiddleware', 'FifthMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
    ],
]);

it('should add middleware after another middleware in the priority list', function (array $default, string $after, string|array $middleware, array $expected) {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $default);
    $manager->after($after, $middleware);

    expect($manager->getPriority())->toBe($expected);
})->with([
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware'],
        'after' => 'FirstMiddleware',
        'middleware' => 'ThirdMiddleware',
        'expected' => ['FirstMiddleware', 'ThirdMiddleware', 'SecondMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'after' => 'SecondMiddleware',
        'middleware' => ['FourthMiddleware', 'FifthMiddleware'],
        'expected' => ['FirstMiddleware', 'SecondMiddleware', 'FourthMiddleware', 'FifthMiddleware', 'ThirdMiddleware'],
    ],
]);

it('should swap positions of two middleware', function (array $default, string $what, string $with, array $expected) {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $default);
    $manager->swap($what, $with);

    expect($manager->getPriority())->toBe($expected);
})->with([
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'what' => 'FirstMiddleware',
        'with' => 'SecondMiddleware',
        'expected' => ['SecondMiddleware', 'FirstMiddleware', 'ThirdMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'what' => 'FirstMiddleware',
        'with' => 'ThirdMiddleware',
        'expected' => ['ThirdMiddleware', 'SecondMiddleware', 'FirstMiddleware'],
    ],
]);

it('should remove middleware from priority list', function (array $default, string|array $what, array $expected) {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $default);
    $manager->remove($what);

    expect($manager->getPriority())->toBe($expected);
})->with([
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'what' => 'FirstMiddleware',
        'expected' => ['SecondMiddleware', 'ThirdMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'what' => 'SecondMiddleware',
        'expected' => ['FirstMiddleware', 'ThirdMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'what' => 'ThirdMiddleware',
        'expected' => ['FirstMiddleware', 'SecondMiddleware'],
    ],
    [
        'default' => ['FirstMiddleware', 'SecondMiddleware', 'ThirdMiddleware'],
        'what' => ['FirstMiddleware', 'ThirdMiddleware'],
        'expected' => ['SecondMiddleware'],
    ],
]);

it('should throw exception while trying to insert middleware before non-existing middleware', function () {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), ['FirstMiddleware', 'SecondMiddleware']);

    $manager->before('NonExistingMiddleware', 'ThirdMiddleware');
})->throws(InvalidArgumentException::class);

it('should throw exception while trying to insert middleware after non-existing middleware', function () {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), ['FirstMiddleware', 'SecondMiddleware']);

    $manager->after('NonExistingMiddleware', 'ThirdMiddleware');
})->throws(InvalidArgumentException::class);

it('should throw exception while trying to swap non-existing middleware', function (array $defaults, string $what, string $with) {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), $defaults);

    $manager->swap($what, $with);
})->with([
    [
        'defaults' => ['FirstMiddleware', 'SecondMiddleware'],
        'what' => 'NonExistingMiddleware',
        'with' => 'SecondMiddleware',
    ],
    [
        'defaults' => ['FirstMiddleware', 'SecondMiddleware'],
        'what' => 'FirstMiddleware',
        'with' => 'NonExistingMiddleware',
    ],
])->throws(InvalidArgumentException::class);

it('should throw exception while trying to remove non-existing middleware', function () {
    $manager = MiddlewarePriorityManager::withDefaults(new Middleware(), ['FirstMiddleware', 'SecondMiddleware']);

    $manager->remove('NonExistingMiddleware');
})->throws(InvalidArgumentException::class);
