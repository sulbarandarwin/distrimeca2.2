<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuppliersExport;
use App\Exports\ProductsExport;
use App\Imports\SuppliersImport;
use App\Imports\ProductsImport; // Importar clase de importación de productos
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Log; // Importar Log

class ImportExportController extends Controller
{
    /**
     * Muestra la página principal de Importar/Exportar.
     */
    public function index() // <-- ¡Asegúrate que este método exista!
    {
        return view('admin.import_export.index');
    }

    /**
     * Exporta los proveedores a un archivo Excel.
     */
    public function exportSuppliers()
    {
        try {
            return Excel::download(new SuppliersExport, 'proveedores.xlsx');
        } catch (\Exception $e) {
            Log::error("Error al exportar proveedores: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al generar el archivo de exportación de proveedores.');
        }
    }

     /**
     * Exporta los productos a un archivo Excel.
     */
    public function exportProducts()
    {
        try {
            return Excel::download(new ProductsExport(), 'productos.xlsx');
        } catch (\Exception $e) {
            Log::error("Error al exportar productos: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al generar el archivo de exportación de productos.');
        }
    }

    /**
     * Importa proveedores desde un archivo Excel.
     */
    public function importSuppliers(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new SuppliersImport, $request->file('import_file'));
            return redirect()->route('admin.import_export.index')->with('success', '¡Importación de proveedores completada exitosamente!');
        } catch (ValidationException $e) {
             $failures = $e->failures();
             $errorMessages = [];
             foreach ($failures as $failure) {
                 $errorMessages[] = "Fila: " . $failure->row() . " - Columna: '" . $failure->attribute() . "' (Valor: '" . ($failure->values()[$failure->attribute()] ?? 'N/A') . "')" . " - Error: " . implode(', ', $failure->errors());
             }
             return back()->withErrors($errorMessages)->with('error', 'La importación de proveedores falló debido a errores de validación.');
        } catch (\Exception $e) {
            Log::error("Error al importar proveedores: " . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Ocurrió un error inesperado durante la importación de proveedores.');
        }
    }

    /**
     * Importa productos desde un archivo Excel.
     */
    public function importProducts(Request $request)
    {
         $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('import_file'));
            return redirect()->route('admin.import_export.index')->with('success', '¡Importación de productos completada exitosamente!');
        } catch (ValidationException $e) {
             $failures = $e->failures();
             $errorMessages = [];
             foreach ($failures as $failure) {
                  $errorMessages[] = "Fila: " . $failure->row()
                                    . " - Columna: '" . $failure->attribute()
                                    . "' (Valor: '" . ($failure->values()[$failure->attribute()] ?? 'N/A') . "')"
                                    . " - Error: " . implode(', ', $failure->errors());
             }
             return back()->withErrors($errorMessages)->with('error', 'La importación de productos falló debido a errores de validación.');
        } catch (\Exception $e) {
            Log::error("Error al importar productos: " . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Ocurrió un error inesperado durante la importación de productos. Revise el log para más detalles.');
        }
    }
}
