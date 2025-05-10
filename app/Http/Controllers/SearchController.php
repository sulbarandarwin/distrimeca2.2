<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Setting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // <--- DESCOMENTADO Y ACTIVO
use App\Exports\ProductsExport;      // <--- DESCOMENTADO Y ACTIVO
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    /**
     * Prepara los datos comunes para los dropdowns del formulario.
     */
    private function getSearchFormData(Request $request, array $currentFilters = []) : array
    {
        $user = Auth::user();
        $countries = Country::orderBy('name')->pluck('name', 'id');
        $states = collect();
        $suppliers = collect(); // Inicializa como colección vacía
        $settings = Setting::pluck('value', 'key')->all();

        // Cargar proveedores según rol (para el dropdown de filtro)
        if ($user?->hasRole('Proveedor')) {
            // Un usuario Proveedor puede filtrar entre los proveedores a los que está asociado
            $suppliers = $user->suppliers()->orderBy('name')->pluck('name', 'id');
        } elseif ($user && !$user->hasRole('Cliente')) {
            // Admin, Vendedor, etc., ven todos los proveedores
            $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        }
        // El rol Cliente no ve el filtro de proveedores, por lo que $suppliers permanece vacío.

        // Cargar estados si hay país preseleccionado
        $selectedCountryId = old('country_id', $currentFilters['country_id'] ?? null);
        if ($selectedCountryId) {
            $states = State::where('country_id', $selectedCountryId)->orderBy('name')->pluck('name', 'id');
        }

        return compact('countries', 'states', 'suppliers', 'settings') + ['validated' => $currentFilters];
    }

    /**
     * Muestra el formulario de búsqueda inicial O los resultados filtrados.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $products = null;
        $validated = $request->query(); 

        $viewData = $this->getSearchFormData($request, $validated);
        $itemsPerPage = (int) ($viewData['settings']['items_per_page'] ?? 15);

        $isSearchRequest = $request->filled('product_name') ||
                           $request->filled('supplier_name') || 
                           $request->filled('supplier_id') ||   
                           $request->filled('country_id') ||
                           $request->filled('state_id');

        if ($isSearchRequest || $request->routeIs('search.results')) {
            $validated = $request->validate([
                'product_name' => ['nullable', 'string', 'max:100'],
                'country_id' => ['nullable', 'integer', 'exists:countries,id'],
                'state_id' => ['nullable', 'integer', 'exists:states,id'],
                'supplier_name' => ['nullable', 'string', 'max:100'], 
                'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'], 
            ]);

            $productName = $validated['product_name'] ?? null;
            $countryId = $validated['country_id'] ?? null;
            $stateId = $validated['state_id'] ?? null;
            $supplierNameInput = $validated['supplier_name'] ?? null;
            $supplierIdInput = $validated['supplier_id'] ?? null;

            try {
                $query = Product::query();

                if ($user?->hasRole('Cliente')) {
                    $query->with('category');
                } else {
                    $query->with(['supplier.country', 'supplier.state', 'category']);
                }

                if ($productName) {
                    $query->where('products.name', 'like', '%' . $productName . '%');
                }

                if ($user?->hasRole('Proveedor')) {
                    $userSupplierIds = $user->suppliers()->pluck('suppliers.id')->toArray();
                    if (empty($userSupplierIds)) {
                        $query->whereRaw('1 = 0');
                    } else {
                        if ($supplierIdInput && in_array($supplierIdInput, $userSupplierIds)) {
                            $query->where('products.supplier_id', $supplierIdInput);
                        } else {
                            // Si no se especificó un supplier_id en el filtro O si el especificado no le pertenece,
                            // busca en todos sus proveedores asociados.
                            $query->whereIn('products.supplier_id', $userSupplierIds);
                        }
                    }
                } else { // Para Admin, Vendedor, etc. (Cliente no verá filtro de proveedor)
                    if ($supplierIdInput) {
                        $query->where('products.supplier_id', $supplierIdInput);
                    } elseif ($supplierNameInput) {
                        $query->whereHas('supplier', function ($q) use ($supplierNameInput) {
                            $q->where('name', 'like', '%' . $supplierNameInput . '%');
                        });
                    }
                }

                // Aplicar filtro de ubicación solo si no se filtró por proveedor específico
                if (!$supplierIdInput && !$supplierNameInput && ($countryId || $stateId)) {
                    $query->whereHas('supplier', function ($q) use ($countryId, $stateId) {
                        if ($countryId) { $q->where('country_id', $countryId); }
                        if ($stateId) { $q->where('state_id', $stateId); }
                    });
                }

                $products = $query->latest('products.created_at')->paginate($itemsPerPage)->appends($validated);

            } catch (\Exception $e) {
                Log::error("Error al buscar productos: " . $e->getMessage(), ['filters' => $validated, 'trace' => $e->getTraceAsString()]);
                session()->flash('error', 'Ocurrió un error al realizar la búsqueda.');
                $products = new LengthAwarePaginator([], 0, $itemsPerPage);
            }
            $viewData['validated'] = $validated;
        } else {
            $products = new LengthAwarePaginator([], 0, $itemsPerPage);
            $viewData['validated'] = [];
        }

        $viewData['products'] = $products;
        return view('search.index', $viewData);
    }

    /**
     * Devuelve sugerencias de nombres de productos para autocompletado.
     */
    public function autocomplete(Request $request)
    {
        $term = $request->input('term');
        if (!$term) { return response()->json([]); }

        $query = Product::where('products.name', 'like', '%' . $term . '%');
        
        $user = Auth::user();
        if ($user && $user->hasRole('Proveedor')) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id')->toArray();
            if (!empty($supplierIds)) {
                $query->whereIn('products.supplier_id', $supplierIds);
            } else {
                return response()->json([]); // No tiene proveedores, no hay sugerencias
            }
        }

        $products = $query->orderBy('products.name')->limit(10)->get(['products.id', 'products.name as name']);
        return response()->json($products);
    }

    /**
     * Exporta los productos seleccionados a un archivo Excel.
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'selected_products' => 'required|array',
            'selected_products.*' => 'integer|exists:products,id',
        ]);

        $productIds = $validated['selected_products'];

        if (empty($productIds)) {
            return back()->with('error', 'No se seleccionaron productos para exportar.');
        }

        $user = Auth::user();
        if ($user && $user->hasRole('Proveedor')) {
            $userSupplierIds = $user->suppliers()->pluck('suppliers.id')->toArray();
            
            // Filtrar los productIds para asegurar que solo se exporten productos de sus proveedores
            $allowedProductIds = Product::whereIn('id', $productIds) // Busca solo dentro de los IDs seleccionados
                                        ->whereIn('supplier_id', $userSupplierIds) // Y que pertenezcan a sus proveedores
                                        ->pluck('id')
                                        ->toArray();
            
            // Comprobar si todos los productos seleccionados eran válidos
            if (count($allowedProductIds) !== count($productIds)) {
                Log::warning("Usuario Proveedor ID {$user->id} intentó exportar productos no autorizados. Exportando solo los permitidos.");
            }

            $productIds = $allowedProductIds; // Actualizar productIds a solo los permitidos
            
            if (empty($productIds)) { // Si después de filtrar no queda ninguno
                 return back()->with('error', 'Ninguno de los productos seleccionados te pertenece o es válido para exportar.');
            }
        }
        // Podrías añadir lógica similar para otros roles si es necesario (ej. Clientes)

        try {
            $fileName = 'productos_seleccionados_' . now()->format('Ymd_His') . '.xlsx';
            // Tu ProductsExport.php ya está preparado para recibir $productIds en el constructor
            return Excel::download(new ProductsExport($productIds), $fileName);
        } catch (\Exception $e) {
            Log::error("Error al exportar productos a Excel: " . $e->getMessage(), [
                'product_ids' => $productIds,
                'user_id' => Auth::id(),
                'exception' => $e
            ]);
            return back()->with('error', 'Ocurrió un error al generar el archivo Excel. Inténtalo de nuevo más tarde.');
        }
    }

    /**
     * Devuelve sugerencias de nombres de proveedores para autocompletado.
     */
    public function autocompleteSupplier(Request $request)
    {
        $term = $request->input('term');
        if (!$term) { return response()->json([]); }

        $query = Supplier::where('name', 'like', '%' . $term . '%');

        $user = Auth::user();
        if ($user && $user->hasRole('Proveedor')) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id')->toArray();
             if (!empty($supplierIds)) {
                $query->whereIn('id', $supplierIds); // Filtrar por los IDs de sus proveedores
            } else {
                return response()->json([]); // No tiene proveedores, no hay sugerencias
            }
        }

        $suppliers = $query->orderBy('name')->limit(10)->get(['id', 'name']);
        return response()->json($suppliers);
    }
}