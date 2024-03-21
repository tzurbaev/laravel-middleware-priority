<?php

declare(strict_types=1);

namespace Laniakea\Tests\Workbench\Middleware;

class FirstMiddleware extends AbstractMiddleware
{
    protected function getStackKey(): string
    {
        return 'first';
    }
}
