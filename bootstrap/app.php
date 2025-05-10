<?php // bootstrap/app.php

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
        // Registra tus alias de middleware aquí:
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            // Otros alias que puedas tener...
        ]);
        // ... otras configuraciones de middleware globales si las tienes ...
    })
    // IMPORTANTE: La sección ->withProviders(...) está ahora vacía o puedes omitirla
    // si no tienes OTROS proveedores que necesites registrar manualmente.
    // Si la omites, asegúrate de que la cadena de métodos continúe correctamente:
    // ->withMiddleware(...)
    // ->withExceptions(...)
    // ->create();
    //
    // Si prefieres dejarla vacía:
    ->withProviders([
        // App\Providers\OtroProviderQueNecesitesManualmente::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // Configuración de manejo de excepciones...
    })->create();