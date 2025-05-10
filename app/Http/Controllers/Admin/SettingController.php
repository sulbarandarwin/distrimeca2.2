<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Country; // <-- Añadir import para Country
use App\Models\State;   // <-- Añadir import para State
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Muestra la página de configuración.
     */
    public function index()
    {
        // Obtener todas las configuraciones guardadas como un array asociativo [key => value]
        $settings = Setting::pluck('value', 'key')->all(); 

        // Obtener todos los países para el dropdown
        $countries = Country::orderBy('name')->get();

        // Obtener los estados solo para el país por defecto seleccionado (si existe)
        $statesForDefaultCountry = collect(); // Colección vacía por defecto
        $defaultCountryId = $settings['default_country_id'] ?? null;
        if ($defaultCountryId) {
            $statesForDefaultCountry = State::where('country_id', $defaultCountryId)->orderBy('name')->get();
        }

        // Pasar todas las variables a la vista
        return view('admin.settings.index', compact('settings', 'countries', 'statesForDefaultCountry'));
    }

    /**
     * Actualiza la configuración.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'currency_symbol' => 'required|string|max:10',
            'notification_email' => 'required|email|max:255',
            'items_per_page' => 'required|integer|min:5|max:100',
            'default_country_id' => 'nullable|exists:countries,id',
            'default_state_id' => 'nullable|exists:states,id',
            // --- NUEVAS VALIDACIONES PARA IA ---
            'ai_search_provider' => ['nullable', 'string', Rule::in(['google_gemini', 'openai_gpt', 'deepseek', 'none'])], // AÑADIDO 'deepseek'
        ]);
    
        try {
            // ... (tu lógica existente para guardar logo, currency, etc.) ...
            if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
                // ... (código para guardar logo)
            }
            Setting::updateOrCreate(['key' => 'currency_symbol'], ['value' => $validated['currency_symbol']]);
            Setting::updateOrCreate(['key' => 'notification_email'], ['value' => $validated['notification_email']]);
            Setting::updateOrCreate(['key' => 'items_per_page'], ['value' => $validated['items_per_page']]);
            Setting::updateOrCreate(['key' => 'default_country_id'], ['value' => $request->input('default_country_id')]);
            Setting::updateOrCreate(['key' => 'default_state_id'], ['value' => $request->input('default_state_id')]);
    
            // --- GUARDAR NUEVAS CONFIGURACIONES DE IA ---
            Setting::updateOrCreate(['key' => 'ai_search_provider'], ['value' => $request->input('ai_search_provider', 'none')]);
    
            Log::info('Configuración actualizada por usuario ID: ' . auth()->id());
            return redirect()->route('admin.settings.index')->with('success', '¡Configuración guardada exitosamente!');
    
        } catch (\Exception $e) {
            Log::error("Error al guardar configuración: " . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocurrió un error al guardar la configuración. Revise los logs.')->withInput();
        }
        
    }
}