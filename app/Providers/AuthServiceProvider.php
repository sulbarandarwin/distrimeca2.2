<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate; // Descomentar si usas Gates directamente aquí
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// Importa tus modelos y policies aquí cuando los registres
use App\Models\Product; 
use App\Policies\ProductPolicy; 

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Aquí es donde registraremos el ProductPolicy
        Product::class => ProductPolicy::class, 
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Ejemplo genérico
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Opcional: Definir Gate 'before' para que Admin tenga todos los permisos
        // Esto evita tener que asignar todos los permisos explícitamente al rol Admin
         \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
             // Asegúrate de que el modelo User y el trait HasRoles estén correctamente configurados
             // El método hasRole viene del paquete spatie/laravel-permission
             if ($user->hasRole('Admin')) {
                 return true; // Admin puede hacer todo
             }
             return null; // Dejar que el Policy/Gate específico decida
         });
    }
}
