<?php

namespace App\Http\Controllers; // Asegúrate que el namespace sea correcto

use App\Models\Supplier;
use App\Models\Country;
use App\Models\State;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log; // Añadir Log

class SupplierSearchController extends Controller
{
    /**
     * Muestra el formulario y resultados de búsqueda de proveedores.
     */
    public function index(Request $request)
    {
         // Validar filtros de búsqueda
         $validated = $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
        ]);

        $supplierName = $validated['supplier_name'] ?? null;
        $countryId = $validated['country_id'] ?? null;
        $stateId = $validated['state_id'] ?? null;

        // Inicializar paginador vacío
        $settings = Setting::pluck('value', 'key')->all(); 
        $itemsPerPage = (int) ($settings['items_per_page'] ?? 15);
        $suppliers_results = new LengthAwarePaginator([], 0, $itemsPerPage); 

        // Construir consulta solo si hay filtros
        $isSearchRequest = $request->filled('supplier_name') || $request->filled('country_id') || $request->filled('state_id');

        if ($isSearchRequest) {
            try {
                $query = Supplier::with(['country', 'state', 'types']); 

                if ($supplierName) { $query->where('name', 'like', '%' . $supplierName . '%'); }
                if ($countryId) { $query->where('country_id', $countryId); }
                if ($stateId) { $query->where('state_id', $stateId); }
                
                $suppliers_results = $query->orderBy('name')->paginate($itemsPerPage)->appends($validated);

            } catch (\Exception $e) {
                Log::error("Error al buscar proveedores: " . $e->getMessage(), ['filters' => $validated]);
                session()->flash('error', 'Ocurrió un error al realizar la búsqueda de proveedores.'); 
                // $suppliers_results ya es un paginador vacío
            }
        }

        // Datos para los dropdowns del formulario
        $countries = Country::orderBy('name')->pluck('name', 'id');
        $states = $countryId 
             ? State::where('country_id', $countryId)->orderBy('name')->pluck('name', 'id') 
             : collect(); 
        // Recargar estados si es necesario (ej. error de validación)
        if ($countryId && !$states->has(old('state_id', $stateId ?? ''))) { 
             $states = State::where('country_id', $countryId)->orderBy('name')->pluck('name', 'id');
        } elseif (!$countryId) {
             $states = collect(); 
        }

        return view('supplier-search.index', [
            'suppliers' => $suppliers_results, // Pasar resultados con nombre 'suppliers'
            'countries' => $countries,
            'states' => $states,
            'validated' => $validated // Pasar filtros para rellenar form
        ]);
    }
}
