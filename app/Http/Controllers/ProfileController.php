<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse; // <-- Añadir para respuesta JSON
use Illuminate\Support\Facades\Log; // <-- Añadir para logging

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // --- NUEVO MÉTODO PARA ACTUALIZAR MODO OSCURO ---
    /**
     * Update the user's dark mode preference.
     * Recibe una petición AJAX (normalmente PATCH o POST).
     */
    public function updateDarkModePreference(Request $request): JsonResponse
    {
        // Validar la entrada (esperamos un booleano o algo convertible)
        $validated = $request->validate([
            'dark_mode_enabled' => 'required|boolean', // Espera true o false (o 1/0)
        ]);

        try {
            $user = $request->user(); // Obtener usuario autenticado
            $user->dark_mode_enabled = $validated['dark_mode_enabled'];
            $user->save();

            // Devolver respuesta JSON de éxito
            return response()->json(['success' => true, 'message' => 'Preferencia guardada.']);

        } catch (\Exception $e) {
            Log::error('Error al actualizar preferencia de modo oscuro para usuario ID ' . Auth::id() . ': ' . $e->getMessage());
            // Devolver respuesta JSON de error
            return response()->json(['success' => false, 'message' => 'Error al guardar la preferencia.'], 500);
        }
    }
    // --- FIN NUEVO MÉTODO ---

} // Fin clase ProfileController
