<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
// Si no usas AuthorizesRequests directamente aquí porque usas middleware de permiso en rutas, puedes comentarlo.
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests; // Descomentar si se llama $this->authorize() explícitamente

    /**
     * Muestra el listado de productos.
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', Product::class); // Autorización vía Policy

        $user = Auth::user();
        $query = Product::with(['supplier', 'category']);

        if ($user->hasRole('Proveedor')) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id')->toArray();
            if (!empty($supplierIds)) {
                $query->whereIn('products.supplier_id', $supplierIds);
            } else {
                Log::warning("Usuario Proveedor ID {$user->id} intentó listar productos sin estar asociado a ningún proveedor en supplier_user.");
                $query->whereRaw('1 = 0'); // No muestra nada si no tiene proveedores asociados
            }
        }

        $itemsPerPage = (int) (Setting::where('key', 'items_per_page')->value('value') ?? 15);
        $products = $query->latest('products.created_at')->paginate($itemsPerPage);

        if ($request->ajax()) {
            return view('admin.products.partials._table_data', compact('products'))->render();
        }

        return view('admin.products.index', compact('products'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        $this->authorize('create', Product::class); // Autorización vía Policy

        $user = Auth::user();
        $suppliers = collect();
        if ($user->hasRole('Proveedor')) {
            // Un proveedor solo puede crear productos para los proveedores a los que está asociado
            $suppliers = $user->suppliers()->orderBy('name')->pluck('name', 'id');
        } else if ($user->hasAnyRole(['Admin', 'Vendedor'])) { // Asumiendo que Vendedor también puede crear
            $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        }

        if ($suppliers->isEmpty() && $user->hasRole('Proveedor')) {
             // Si es un proveedor y no está asociado a ningún Supplier, no puede crear productos.
             // Podrías redirigir con un error o mostrar una vista diferente.
             return redirect()->route('admin.products.index')->with('error', 'No estás asociado a ningún proveedor para crear productos.');
        }


        $categories = Category::orderBy('name')->pluck('name', 'id');
        return view('admin.products.create', compact('suppliers', 'categories'));
    }

    /**
     * Guarda un nuevo producto.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class); // Autorización vía Policy
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'code')
                    ->where('supplier_id', $request->input('supplier_id'))
            ],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'gte:0'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        // Si el usuario es Proveedor, asegurarse que el supplier_id sea uno de los suyos
        if ($user->hasRole('Proveedor')) {
            if (!$user->suppliers()->where('suppliers.id', $validatedData['supplier_id'])->exists()) {
                return back()->with('error', 'No puedes crear productos para un proveedor no asociado.')->withInput();
            }
        }

        try {
            Product::create($validatedData);
            return redirect()->route('admin.products.index')->with('success', '¡Producto creado exitosamente!');
        } catch (\Exception $e) {
            Log::error('Error al crear producto: ' . $e->getMessage(), ['exception' => $e, 'validated_data' => $validatedData]);
            return back()->with('error', 'Error SQL al crear el producto. Detalles: ' . $e->getMessage())->withInput();
        }
    }


    public function show(Product $product)
    {
        $this->authorize('view', $product); // Autorización vía Policy

        $user = Auth::user();
        $suppliers = collect();
        if ($user->hasRole('Proveedor')) {
            $suppliers = $user->suppliers()->orderBy('name')->pluck('name', 'id');
        } else if ($user->hasAnyRole(['Admin', 'Vendedor'])) {
            $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        }

        $categories = Category::orderBy('name')->pluck('name', 'id');
        $product->load('supplier', 'category');
        return view('admin.products.edit', compact('product', 'suppliers', 'categories'));
    }


    public function edit(Product $product)
    {
        $this->authorize('update', $product); // Autorización vía Policy

        $user = Auth::user();
        $suppliers = collect();

        if ($user->hasRole('Proveedor')) {
            // Un proveedor solo puede editar productos de los proveedores a los que está asociado.
            // El dropdown solo mostrará sus proveedores asociados.
            $suppliers = $user->suppliers()->orderBy('name')->pluck('name', 'id');
        } else if ($user->hasAnyRole(['Admin', 'Vendedor'])) {
            $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        }

        $categories = Category::orderBy('name')->pluck('name', 'id');
        return view('admin.products.edit', compact('product', 'suppliers', 'categories'));
    }


    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product); // Autorización vía Policy
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'code')
                    ->where('supplier_id', $request->input('supplier_id')) // Usar el supplier_id que viene del form
                    ->ignore($product->id)
            ],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'gte:0'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        // Si el usuario es Proveedor, asegurarse que el supplier_id (si se intenta cambiar) sea uno de los suyos.
        // Y no permitir que cambie el producto a un proveedor que no le pertenece.
        // La Policy ya debería cubrir esto, pero una doble verificación no hace daño.
        if ($user->hasRole('Proveedor')) {
            // El producto original debe pertenecer a uno de sus proveedores.
            // Y el nuevo supplier_id (si se cambia) también debe pertenecerle.
            if (!$user->suppliers()->where('suppliers.id', $product->supplier_id)->exists() ||
                !$user->suppliers()->where('suppliers.id', $validatedData['supplier_id'])->exists()) {
                 return back()->with('error', 'No puedes modificar este producto o asignarlo a un proveedor no asociado.')->withInput();
            }
             // Un proveedor no debería poder cambiar el 'supplier_id' de un producto a otro proveedor,
             // incluso si ambos proveedores le pertenecen, a menos que esa sea una funcionalidad deseada.
             // Para simplicidad, forzamos que el supplier_id no cambie si edita un proveedor.
             $validatedData['supplier_id'] = $product->supplier_id;
        }


        try {
            $product->update($validatedData);
            return redirect()->route('admin.products.index')->with('success', '¡Producto actualizado exitosamente!');
        } catch (\Exception $e) {
            Log::error("Error al actualizar producto ID {$product->id}: " . $e->getMessage(), ['exception' => $e, 'validated_data' => $validatedData]);
            return back()->with('error', 'Error SQL al actualizar el producto. Detalles: ' . $e->getMessage())->withInput();
        }
    }


    public function destroy(Product $product)
    {
        $this->authorize('delete', $product); // Autorización vía Policy

        // La política 'delete' debería manejar quién puede eliminar.
        // Si un Proveedor tiene el permiso 'delete products' (a través de la policy) y
        // la policy 'delete' verifica que es su producto, entonces podría eliminarlo.
        // La lógica actual de tu policy ('eliminar productos' solo Admin) ya lo controla.
        // Así que no se necesita lógica adicional de rol aquí si la policy es la fuente de verdad.

        // if (Auth::user()->hasRole('Proveedor')) {
        //      Log::warning("Proveedor ID ". Auth::id() ." intentó borrar producto ID ". $product->id);
        //      return redirect()->route('admin.products.index')->with('error', 'No tienes permiso para eliminar productos.');
        // }

        try {
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', '¡Producto eliminado exitosamente!');
        } catch (\Exception $e) {
            Log::error("Error al eliminar producto ID {$product->id}: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error inesperado al eliminar el producto.');
        }
    }
}