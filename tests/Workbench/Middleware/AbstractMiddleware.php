<?php

declare(strict_types=1);

namespace Laniakea\Tests\Workbench\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AbstractMiddleware
{
    public function handle(Request $request, callable $next)
    {
        $response = $next($request);

        if (!($response instanceof JsonResponse)) {
            return $response;
        }

        $data = $response->getData();
        $data->stack[] = $this->getStackKey();

        $response->setData($data);

        return $response;
    }

    abstract protected function getStackKey(): string;
}
