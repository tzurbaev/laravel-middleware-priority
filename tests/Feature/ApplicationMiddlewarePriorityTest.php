<?php

declare(strict_types=1);

use Illuminate\Foundation\Configuration\Middleware;
use Laniakea\MiddlewarePriority\MiddlewarePriorityManager;
use Laniakea\Tests\ConfiguresMiddleware;
use Laniakea\Tests\Workbench\Middleware\FirstMiddleware;
use Laniakea\Tests\Workbench\Middleware\SecondMiddleware;
use Laniakea\Tests\Workbench\Middleware\ThirdMiddleware;

uses(ConfiguresMiddleware::class);

it('should not have any middleware by default', function () {
    $this->getJson('/__testing')->assertJson([
        'stack' => [
            'route',
        ],
    ]);
});

it('should apply default middleware priority', function () {
    $this->configureMiddleware(function (Middleware $middleware) {
        /*
         * Default order:
         * 1. FirstMiddleware – called first in order, waits for SecondMiddleware, pushes to stack third in order
         * 2. SecondMiddleware – called second in order, waits for ThirdMiddleware, pushes to stack second in order
         * 3. ThirdMiddleware – called last in order, pushes to stack first in order
         *
         * Final stack: ['route', 'third', 'second', 'first']
         */
        $middleware->appendToGroup('api', [
            FirstMiddleware::class,
            SecondMiddleware::class,
            ThirdMiddleware::class,
        ]);
    });

    $this->getJson('/__testing')->assertJson([
        'stack' => [
            'route',
            'third',
            'second',
            'first',
        ],
    ]);
});

it('should apply middleware priority', function () {
    $this->configureMiddleware(function (Middleware $middleware) {
        /*
         * Default order:
         * 1. FirstMiddleware – called first in order, waits for SecondMiddleware, pushes to stack third in order
         * 2. SecondMiddleware – called second in order, waits for ThirdMiddleware, pushes to stack second in order
         * 3. ThirdMiddleware – called last in order, pushes to stack first in order
         *
         * Final stack: ['route', 'third', 'second', 'first']
         */
        $middleware->appendToGroup('api', [
            FirstMiddleware::class,
            SecondMiddleware::class,
            ThirdMiddleware::class,
        ]);

        $manager = MiddlewarePriorityManager::withDefaults($middleware); // []
        $manager->append(FirstMiddleware::class); // [FirstMiddleware]
        $manager->before(FirstMiddleware::class, ThirdMiddleware::class); // [ThirdMiddleware, FirstMiddleare]
        $manager->before(ThirdMiddleware::class, SecondMiddleware::class); // [SecondMiddleware, ThirdMiddleware, FirstMiddleare]

        /*
         * Priority order:
         * 1. SecondMiddleware – called first in order, waits for ThirdMiddleware, pushes to stack third in order
         * 2. ThirdMiddleware – called second in order, waits for FirstMiddleware, pushes to stack second in order
         * 3. FirstMiddleware – called last in order, pushes to stack first in order
         *
         * Final stack: ['route', 'first', 'third', 'second']
         */
    });

    $this->getJson('/__testing')->assertJson([
        'stack' => [
            'route',
            'first',
            'third',
            'second',
        ],
    ]);
});
