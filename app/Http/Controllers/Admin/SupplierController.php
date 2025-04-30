<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Country;
use App\Models\State;
use App\Models\SupplierType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::with(['country', 'state', 'types'])->latest()->paginate(10);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::orderBy('name')->pluck('name', 'id');
        $states = State::orderBy('name')->pluck('name', 'id');
        $supplierTypes = SupplierType::orderBy('name')->get();
        return view('admin.suppliers.create', compact('countries', 'states', 'supplierTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rif' => ['nullable', 'string', 'max:50', Rule::unique('suppliers', 'rif')],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('suppliers', 'email')],
            'phone1' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'supplier_types' => ['required', 'array'],
            'supplier_types.*' => ['integer', 'exists:supplier_types,id']
        ]);

        $supplier = Supplier::create($request->only([
            'name', 'rif', 'email', 'phone1', 'phone2', 'country_id', 'state_id'
        ]));

        if ($request->filled('supplier_types')) {
            $typeIds = array_map('intval', $validatedData['supplier_types']);
            $supplier->types()->sync($typeIds);
        } else {
            $supplier->types()->sync([]);
        }

        return redirect()->route('admin.suppliers.index')->with('success', '¡Proveedor creado exitosamente!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
         $countries = Country::orderBy('name')->pluck('name', 'id');
         $states = State::orderBy('name')->pluck('name', 'id');
         $supplierTypes = SupplierType::orderBy('name')->get();
         $supplier->load('types');
         return view('admin.suppliers.edit', compact('supplier', 'countries', 'states', 'supplierTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        $supplier->load('types');
        $countries = Country::orderBy('name')->pluck('name', 'id');
        $states = State::orderBy('name')->pluck('name', 'id');
        $supplierTypes = SupplierType::orderBy('name')->get();
        return view('admin.suppliers.edit', compact('supplier', 'countries', 'states', 'supplierTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rif' => ['nullable', 'string', 'max:50', Rule::unique('suppliers', 'rif')->ignore($supplier->id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($supplier->id)],
            'phone1' => ['nullable', 'string', 'max:50'],
            'phone2' => ['nullable', 'string', 'max:50'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'supplier_types' => ['required', 'array'],
            'supplier_types.*' => ['integer', 'exists:supplier_types,id']
        ]);

        $supplier->update($request->only([
            'name', 'rif', 'email', 'phone1', 'phone2', 'country_id', 'state_id'
        ]));

        if ($request->filled('supplier_types')) {
            $typeIds = array_map('intval', $validatedData['supplier_types']);
            $supplier->types()->sync($typeIds);
        } else {
            $supplier->types()->sync([]);
        }

        return redirect()->route('admin.suppliers.index')->with('success', '¡Proveedor actualizado exitosamente!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier) // Laravel inyecta el proveedor a borrar
    {
        // Protección Opcional: Verificar si tiene productos asociados antes de borrar.
        // if ($supplier->products()->count() > 0) {
        //     return back()->with('error', 'No se puede eliminar el proveedor porque tiene productos asociados.');
        // }

        try {
            // Desvincular tipos antes de borrar (buena práctica)
            $supplier->types()->sync([]); // <-- Lógica descomentada

            // Borrar el proveedor
            $supplier->delete(); // <-- Lógica descomentada

            // Redirigir a la lista con mensaje de éxito
            return redirect()->route('admin.suppliers.index')->with('success', '¡Proveedor eliminado exitosamente!'); // <-- Lógica descomentada

        } catch (\Illuminate\Database\QueryException $e) {
             \Log::error("Error al eliminar proveedor ID {$supplier->id}: " . $e->getMessage());
             return back()->with('error', 'Ocurrió un error al eliminar el proveedor. Verifique si tiene datos relacionados.');
        } catch (\Exception $e) {
             \Log::error("Error general al eliminar proveedor ID {$supplier->id}: " . $e->getMessage());
             return back()->with('error', 'Ocurrió un error inesperado al eliminar el proveedor.');
        }
    }
}
