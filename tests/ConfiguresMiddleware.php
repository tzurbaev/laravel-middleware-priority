<?php

declare(strict_types=1);

namespace Laniakea\Tests;

use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Kernel;

trait ConfiguresMiddleware
{
    public function configureMiddleware(callable $callback): void
    {
        $middleware = new Middleware();

        call_user_func_array($callback, [$middleware]);

        /** @var Kernel $kernel */
        $kernel = app(KernelContract::class);
        $kernel->setMiddlewareGroups($middleware->getMiddlewareGroups());

        if ($priority = $middleware->getMiddlewarePriority()) {
            $kernel->setMiddlewarePriority($priority);
        }
    }
}
