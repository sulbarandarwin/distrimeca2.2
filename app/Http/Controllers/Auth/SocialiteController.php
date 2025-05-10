<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite; // <-- Importar Socialite
use App\Models\User;
use App\Models\Invitation;
use Exception; // Para capturar errores de Socialite

class SocialiteController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     */
    public function redirectToGoogle()
    {
        // Guarda la URL a la que se intentaba acceder antes de redirigir (opcional)
        // session()->put('url.intended', url()->previous()); 

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtiene la información del usuario de Google después de la autenticación.
     */
    public function handleGoogleCallback()
    {
        try {
            // Obtener datos del usuario de Google
            $googleUser = Socialite::driver('google')->user();

            // 1. Buscar si ya existe un usuario con ese email en nuestra BD
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                // --- Usuario ya existe ---
                // Simplemente iniciar sesión
                Auth::login($existingUser, true); // true para 'recordar' sesión

                // Redirigir al dashboard o a la URL intentada
                return redirect()->intended(route('dashboard', absolute: false));

            } else {
                // --- Usuario NO existe ---
                // ¡Aquí es donde verificamos si hay una invitación PENDIENTE para este email!
                $invitation = Invitation::where('email', $googleUser->getEmail())
                                        ->whereNull('registered_at') // Que no se haya usado
                                        ->where('expires_at', '>', now()) // Que no haya expirado
                                        ->first();

                if ($invitation) {
                    // --- Invitación válida encontrada ---
                    // Crear el nuevo usuario
                     $newUser = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'password' => Hash::make(Str::random(16)), // Generar pass aleatorio seguro
                        'email_verified_at' => now(), // Marcar como verificado
                        'google_id' => $googleUser->getId(), // Guardar ID de Google (opcional pero útil)
                        'supplier_id' => $invitation->associated_supplier_id, // Asignar proveedor si existe
                    ]);

                    // Asignar el rol de la invitación
                    $invitation->load('role');
                    if ($invitation->role) {
                         $newUser->assignRole($invitation->role->name);
                    } else {
                         Log::critical('Rol no encontrado al aceptar invitación vía Google.', ['invitation_id' => $invitation->id]);
                         // Manejar error: quizás redirigir con error o asignar rol por defecto
                         throw new Exception("No se pudo asignar el rol de la invitación.");
                     }

                    // Marcar la invitación como usada
                    $invitation->update(['registered_at' => now()]);

                    // Iniciar sesión
                    Auth::login($newUser, true);

                    // Disparar evento Registered
                    event(new Registered($newUser));

                    // Redirigir al dashboard
                    return redirect()->intended(route('dashboard', absolute: false))
                                     ->with('status', '¡Registro vía Google completado! Bienvenido/a.');

                } else {
                     // --- No existe usuario NI invitación válida ---
                     Log::warning('Intento de login/registro con Google fallido: No existe usuario ni invitación válida.', ['google_email' => $googleUser->getEmail()]);
                     return redirect()->route('login')
                                      ->with('error', 'No tienes una cuenta registrada con este email de Google ni una invitación pendiente válida.');
                }
            }

        } catch (Exception $e) {
             // Error al obtener datos de Google o durante el proceso
             Log::error('Error durante el callback de Google Socialite: ' . $e->getMessage(), ['exception' => $e]);
             return redirect()->route('login')
                              ->with('error', 'No se pudo iniciar sesión con Google en este momento.');
        }
    }
}