<?php

declare(strict_types=1);

namespace Laniakea\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function defineRoutes($router): void
    {
        $router->group([], __DIR__.'/Workbench/routes.php');
    }
}
