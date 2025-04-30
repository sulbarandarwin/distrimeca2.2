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
        // Registra los alias aquí:
        $middleware->alias([
            // Pueden existir otros alias por defecto o que hayas añadido...
    
            // <<< Asegúrate de que estas líneas estén presentes >>>
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    
        // Otras configuraciones de middleware pueden ir aquí...
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();


