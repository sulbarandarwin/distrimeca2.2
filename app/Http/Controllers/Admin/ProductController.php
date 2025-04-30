<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Para validación unique

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['supplier', 'category'])->latest()->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $categories = Category::orderBy('name')->pluck('name', 'id');
        return view('admin.products.create', compact('suppliers', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('products', 'code')],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'gte:0'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        // Crear el producto usando los datos validados
        Product::create($validatedData); // create() ya maneja los $fillable

        return redirect()->route('admin.products.index')->with('success', '¡Producto creado exitosamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
         // Reutilizamos la vista de edición para mostrar detalles
         $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
         $categories = Category::orderBy('name')->pluck('name', 'id');
         $product->load('supplier', 'category'); // Cargar relaciones si no vienen por route model binding
         return view('admin.products.edit', compact('product', 'suppliers', 'categories'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $suppliers = Supplier::orderBy('name')->pluck('name', 'id');
        $categories = Category::orderBy('name')->pluck('name', 'id');
        // El $product ya viene inyectado con sus datos por Route Model Binding
        return view('admin.products.edit', compact('product', 'suppliers', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product) // <-- Implementamos este método
    {
        // 1. Validación de los datos del formulario
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // unique:products,code -> verifica unicidad ignorando el código del producto actual
            'code' => ['nullable', 'string', 'max:50', Rule::unique('products', 'code')->ignore($product->id)],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'gte:0'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'], // Permite seleccionar "Sin categoría" (null)
        ]);

        // 2. Actualizar el producto con los datos validados
        // Usamos $validatedData para asegurar que solo actualizamos campos validados
        $product->update($validatedData);

        // 3. Redirigir a la lista con mensaje de éxito
        return redirect()->route('admin.products.index')->with('success', '¡Producto actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Lógica para borrar el producto (pendiente)
        try {
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', '¡Producto eliminado exitosamente!');
        } catch (\Exception $e) {
             \Log::error("Error al eliminar producto ID {$product->id}: " . $e->getMessage());
             return back()->with('error', 'Ocurrió un error inesperado al eliminar el producto.');
        }
    }
}
