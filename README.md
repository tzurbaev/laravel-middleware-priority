# Middleware priority manager for Laravel 11+

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laniakea/middleware-priority.svg?style=flat-square)](https://packagist.org/packages/laniakea/middleware-priority)
[![Tests](https://img.shields.io/github/actions/workflow/status/tzurbaev/laravel-middleware-priority/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tzurbaev/laravel-middleware-priority/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/laniakea/middleware-priority.svg?style=flat-square)](https://packagist.org/packages/laniakea/middleware-priority)

This package provides more flexible way to manage HTTP middleware priority in Laravel 11+ applications.

> Please note that this package only compatible with fresh Laravel 11+ applications
> (or applications that have been upgraded to Laravel 11+ with new directory structure / application bootstrapping).

You can simply append/prepend your middleware to the priority list or do more complex things, such as:

- Add your middleware before or after specific middleware;
- Swap positions of two middleware;
- Remove middleware from the priority list.

This package provides default Laravel's priority list (latest update: Laravel 11.0.7; check `DefaultMiddlewarePriority` class),
so you can use it as a base for your custom priority list.

## Installation

You can install the package via composer:

```bash
composer require laniakea/middleware-priority
```

## Usage

This package is intended to be used with Laravel's `Application::configure()` builder (located in `bootstrap/app.php` file).

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Manage priority list here.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### Create priority manager

Create new instance of `Laniakea\MiddlewarePriority\MiddlewarePriorityManager` inside `withMiddleware` callback
to start using middleware priority manager. Please note that the priority list will be empty by default
(unless you mutated it before creating manager instance).

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laniakea\MiddlewarePriority\MiddlewarePriorityManager;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $manager = new MiddlewarePriorityManager($middleware);
        
        // Manage priority list here.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### Use Laravel's default priority

If you want to use Laravel's default priority list, you can use static `MiddlewarePriorityManager::withDefaults` method
to create manager instance with default priority list.

Second argument to the `withDefaults()` method accepts user-defined list of default middleware priority (if it's `null`
or was not passed, default Laravel's priority list will be used).

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laniakea\MiddlewarePriority\MiddlewarePriorityManager;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $manager = MiddlewarePriorityManager::withDefaults($middleware); // create manager with default Laravel's priority list
        // $manager = MiddlewarePriorityManager::withDefaults($middleware, ['App\\SomeMiddleware']); // create manager with custom priority list
        
        // Manage priority list here.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### Appending middleware

> Here and below it's assumed that manager created & used inside the `withMiddleware` callback.

Use `append(string|array $middleware)` method to append middleware to the priority list.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;
use App\Http\Middleware\ThirdMiddleware;

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->append(FirstMiddleware::class);
// priority list: [..., FirstMiddleware::class]

$manager->append([SecondMiddleware::class, ThirdMiddleware::class]);
// priority list: [..., FirstMiddleware::class, SecondMiddleware::class, ThirdMiddleware::class]
```

### Prepending middleware

Use `prepend(string|array $middleware)` method to prepend middleware to the priority list.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;
use App\Http\Middleware\ThirdMiddleware;

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->prepend(FirstMiddleware::class);
// priority list: [FirstMiddleware::class, ...]

$manager->prepend([SecondMiddleware::class, ThirdMiddleware::class]);
// priority list: [SecondMiddleware::class, ThirdMiddleware::class, FirstMiddleware::class, ...]
```

### Add middleware before specific middleware

Use `before(string $middleware, string|array $newMiddleware)` method to add middleware before specific middleware.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->before(SubstituteBindings::class, FirstMiddleware::class);
// priority list: [..., FirstMiddleware::class, SubstituteBindings::class, ...]
```

Also you can use array of middleware as a second argument. In this case new middleware will be added according to the order in the array.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->before(SubstituteBindings::class, [FirstMiddleware::class, SecondMiddleware::class]);
// priority list: [..., FirstMiddleware::class, SecondMiddleware::class, SubstituteBindings::class, ...]
```

### Add middleware after specific middleware

Use `after(string $middleware, string|array $newMiddleware)` method to add middleware after specific middleware.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->after(SubstituteBindings::class, FirstMiddleware::class);
// priority list: [..., SubstituteBindings::class, FirstMiddleware::class, ...]
```

Also you can use array of middleware as a second argument. In this case new middleware will be added according to the order in the array.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->after(SubstituteBindings::class, [FirstMiddleware::class, SecondMiddleware::class]);
// priority list: [..., SubstituteBindings::class, FirstMiddleware::class, SecondMiddleware::class, ...]
```

### Swap middleware positions

Use `swap(string $what, string $with)` method to swap positions of two middleware.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;

$manager = MiddlewarePriorityManager::withDefaults($middleware, [FirstMiddleware::class, SecondMiddleware::class]);
// priority list: [FirstMiddleware::class, SecondMiddleware::class]

$manager->swap(FirstMiddleware::class, SecondMiddleware::class);
// priority list: [SecondMiddleware::class, FirstMiddleware::class]
```

### Remove middleware

Use `remove(string|array $what)` method to remove middleware from the priority list.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;

$manager = MiddlewarePriorityManager::withDefaults($middleware, [FirstMiddleware::class, SecondMiddleware::class]);
// priority list: [FirstMiddleware::class, SecondMiddleware::class]

$manager->remove(FirstMiddleware::class);
// priority list: [SecondMiddleware::class]
```

Also you can use array of middleware. All listed middleware will be removed from the priority list.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;
use App\Http\Middleware\ThirdMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->remove([SecondMiddleware::class, ThirdMiddleware::class]);
// priority list: [FirstMiddleware::class]
```

> After removing middleware from the priority list, it's position will be determined by the order of registration
> in the group middleware. See example below.

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;
use App\Http\Middleware\ThirdMiddleware;
use App\Http\Middleware\FourthMiddleware;
use App\Http\Middleware\FifthMiddleware;

$middleware->appendToGroup('web', [
    FirstMiddleware::class,
    SecondMiddleware::class,
    ThirdMiddleware::class,
    FourthMiddleware::class,
    FifthMiddleware::class,
]);

$manager = MiddlewarePriorityManager::withDefaults($middleware);
$manager->prepend(FirstMiddleware::class);
// priority list: [FirstMiddleware::class, ...]

$manager->prepend(SecondMiddleware::class);
// priority list: [SecondMiddleware::class, FirstMiddleware::class, ...]

$manager->before(FirstMiddleware::class, FourthMiddleware::class);
// priority list: [SecondMiddleware::class, FourthMiddleware::class, FirstMiddleware::class, ...]

$manager->after(FourthMiddleware::class, ThirdMiddleware::class);
// priority list: [SecondMiddleware::class, FourthMiddleware::class, ThirdMiddleware::class, FirstMiddleware::class, ...]

$manager->remove(FourthMiddleware::class);
// priority list: [SecondMiddleware::class, ThirdMiddleware::class, FirstMiddleware::class, ...]

/*
 * Middleware will be called in following order:
 * 
 * 1. FourthMiddleware::class (was removed from the priority list);
 * 2. FifthMiddleware::class (was not added to the priority list in first place);
 * 3. SecondMiddleware::class (by priority list order);
 * 4. ThirdMiddleware::class (by priority list order);
 * 5. FirstMiddleware::class (by priority list order).
 */
```

### Get current priority list

If you need to retrieve current priority list, you can use `getPriority()` method.

```php
<?php

$manager = MiddlewarePriorityManager::withDefaults($middleware);

// Manage priority list here.

$currentPriority = $manager->getPriority();
```

### Complete example

```php
<?php

use App\Http\Middleware\FirstMiddleware;
use App\Http\Middleware\SecondMiddleware;
use App\Http\Middleware\ThirdMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laniakea\MiddlewarePriority\MiddlewarePriorityManager;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // First you need to register middleware to some group.
        $middleware->appendToGroup('web', [
            FirstMiddleware::class,
            SecondMiddleware::class,
            ThirdMiddleware::class,
        ]);
        
        // Now you can manage priority list.

        $manager = MiddlewarePriorityManager::withDefaults($middleware);
        $manager->prepend(FirstMiddleware::class);
        $manager->before(\Illuminate\Routing\Middleware\SubstituteBindings::class, SecondMiddleware::class);
        $manager->after(\Illuminate\Auth\Middleware\Authorize::class, ThirdMiddleware::class);
        
        if (thirdMiddlewareNotRequired()) {
            $manager->remove(ThirdMiddleware::class);
        } elseif (thirdMiddlewareShouldBeFirst()) {
            $manager->remove(ThirdMiddleware::class)
                ->prepend(ThirdMiddleware::class);
        } elseif (thirdAndFirstMiddlewareShoudBeSwapped()) {
            $manager->swap(FirstMiddleware::class, ThirdMiddleware::class);
        }
        
        \Log::debug('[app.php@withMiddleware] Priority list generated.', ['priority' => $manager->getPriority()]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Timur Zurbaev](https://github.com/tzurbaev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
