<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/__testing', function () {
    return [
        'stack' => [
            'route',
        ],
    ];
})->middleware(['api'])->name('testing.index');
