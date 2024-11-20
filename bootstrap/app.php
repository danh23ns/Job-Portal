<?php

use GuzzleHttp\RedirectMiddleware;
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
        // Đăng ký alias cho các middleware trong ứng dụng
        $middleware->alias([
            // Alias 'CheckUser' trỏ tới middleware CheckUser trong thư mục \App\Http\Middleware
            'CheckUser' => \App\Http\Middleware\CheckUser::class,
            // Alias 'CheckAdmin' trỏ tới middleware CheckAdmin trong thư mục \App\Http\Middleware
            'CheckAdmin' => \App\Http\Middleware\CheckAdmin::class,
            // Alias 'Authen' trỏ tới middleware Authen trong thư mục \App\Http\Middleware
            'Authen' => \App\Http\Middleware\Authen::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
