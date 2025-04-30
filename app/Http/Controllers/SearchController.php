<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport; // Asegúrate que esta clase exista en app/Exports/
use Illuminate\Support\Facades\Log; // Para logging

class SearchController extends Controller
{
    /**
     * Muestra el formulario de búsqueda.
     * ¡ESTE MÉTODO DEBE EXISTIR!
     */
    public function index()
    {
        // Obtenemos los datos para los desplegables
        $countries = Country::orderBy('name')->pluck('name', 'id');
        // Cargamos todos los estados inicialmente; el JS los filtrará
        $states = State::orderBy('name')->pluck('name', 'id');

        // Retornamos la vista del formulario, sin pasar $products inicialmente
        return view('search.index', compact('countries', 'states'));
    }

    /**
     * Devuelve sugerencias de nombres de productos para autocompletado.
     */
    public function autocomplete(Request $request)
    {
        $term = $request->input('term');
        $suggestions = [];

        if ($term) {
            $suggestions = Product::where('name', 'LIKE', $term . '%')
                                ->limit(10)
                                ->pluck('name')
                                ->toArray();
        }

        return response()->json($suggestions);
    }

    /**
     * Procesa la búsqueda y muestra los resultados.
     */
    public function results(Request $request)
    {
        // Validación básica
        $request->validate([
            'product_name' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
        ]);

        // Obtener los filtros
        $productName = $request->input('product_name');
        $countryId = $request->input('country_id');
        $stateId = $request->input('state_id');

        // Construir la consulta base
        $query = Product::with(['supplier.country', 'supplier.state', 'category']);

        // Aplicar filtro por nombre
        if ($productName) {
            $query->where('name', 'like', '%' . $productName . '%');
        }

        // Aplicar filtro por ubicación del proveedor
        if ($countryId || $stateId) {
            $query->whereHas('supplier', function ($supplierQuery) use ($countryId, $stateId) {
                if ($countryId) {
                    $supplierQuery->where('country_id', $countryId);
                }
                if ($stateId) {
                    $supplierQuery->where('state_id', $stateId);
                }
            });
        }

        // Obtener y paginar resultados
        $products = $query->latest()->paginate(15)->withQueryString();

        // Cargar datos para los selects del formulario
        $countries = Country::orderBy('name')->pluck('name', 'id');
        $states = State::orderBy('name')->pluck('name', 'id');

        // Devolver la vista con los resultados y los datos del formulario
        return view('search.index', compact('products', 'countries', 'states'));
    }

    /**
     * Exporta los productos seleccionados a un archivo Excel.
     */
    public function exportExcel(Request $request)
    {
        // Validar que recibimos los IDs
        $request->validate([
            'selected_ids' => 'required|string',
        ]);

        // Convertir la cadena de IDs en un array de enteros
        $selectedIds = explode(',', $request->input('selected_ids'));
        $productIds = array_filter(array_map('intval', $selectedIds));

        if (empty($productIds)) {
            return back()->with('error', 'No se seleccionaron productos válidos para exportar.');
        }

        try {
            // Pasar los IDs al constructor de ProductsExport
            return Excel::download(new ProductsExport($productIds), 'productos_seleccionados.xlsx');
        } catch (\Exception $e) {
             Log::error("Error al exportar productos seleccionados: " . $e->getMessage());
             return back()->with('error', 'Ocurrió un error al generar el archivo de exportación.');
        }
    }
}
