<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting; // <-- Importar modelo
use Illuminate\Support\Facades\Artisan; // Para limpiar caché de config
use Illuminate\Support\Facades\Log; // Para logging

class SettingController extends Controller
{
    /**
     * Muestra la página de configuración.
     */
    public function index()
    {
        // Obtenemos todas las configuraciones guardadas como una colección key => value
        $settings = Setting::pluck('value', 'key');

        // Pasamos la colección a la vista
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Actualiza la configuración.
     */
    public function update(Request $request)
    {
        // 1. Validar los datos del formulario
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            // Añadir validación para otros campos si los agregas
            // 'contact_email' => 'nullable|email',
        ]);

        // 2. Guardar cada configuración en la base de datos
        try {
            Setting::updateOrCreate(
                ['key' => 'app_name'], // Buscar por esta clave
                ['value' => $validated['app_name']] // Actualizar o crear con este valor
            );

            // Guardar otras configuraciones aquí...
            // if ($request->filled('contact_email')) {
            //     Setting::updateOrCreate(['key' => 'contact_email'], ['value' => $request->input('contact_email')]);
            // }

            // 3. Limpiar la caché de configuración para que los cambios se reflejen
            // (Importante si usas config('settings.app_name') en algún lugar)
            Artisan::call('config:clear');
            Artisan::call('config:cache'); // Recachear con los nuevos valores (opcional pero recomendado)

            Log::info('Configuración actualizada por usuario ID: ' . auth()->id());

            return redirect()->route('admin.settings.index')->with('success', '¡Configuración guardada exitosamente!');

        } catch (\Exception $e) {
            Log::error("Error al guardar configuración: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al guardar la configuración.');
        }
    }
}
