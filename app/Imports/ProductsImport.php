<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Throwable;
use Illuminate\Support\Facades\Auth; 

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError 
{
    use SkipsErrors; 

    private Collection $categories; 
    private ?int $importerSupplierId; 

    public function __construct()
    {
        $this->categories = Category::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        });

        $user = Auth::user();
        $this->importerSupplierId = ($user && $user->hasRole('Proveedor') && $user->supplier_id) ? $user->supplier_id : null;
        
        Log::debug('[ProductsImport] Constructor: Categories cargadas. Importer Supplier ID: ' . ($this->importerSupplierId ?? 'N/A'));
    }

    /**
     * @param array $row (Los nombres de las claves DEBEN coincidir con los encabezados del Excel)
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        Log::debug("[ProductsImport] --- Procesando Fila Excel ---", $row);

        // --- LEER DATOS SEGÚN NUEVO ORDEN DE ENCABEZADOS ---
        // supplier_id, codigo, nombre, descripcion, precio, category_name
        $supplierIdFromFile = filter_var(trim($row['supplier_id'] ?? null), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $productCode = trim($row['codigo'] ?? null); 
        $productName = trim($row['nombre'] ?? null); 
        $productDescription = trim($row['descripcion'] ?? null);
        $productPriceRaw = trim($row['precio'] ?? null);
        $categoryName = strtolower(trim($row['category_name'] ?? '')); // Opcional
        // ---------------------------------------------------

        $productPrice = is_numeric(str_replace(',', '.', $productPriceRaw)) ? floatval(str_replace(',', '.', $productPriceRaw)) : null;

        // Determinar el supplier_id a usar
        $supplierIdToUse = $this->importerSupplierId ?? $supplierIdFromFile;

        // Validaciones críticas ANTES de updateOrCreate
        if (is_null($supplierIdToUse)) {
             Log::error("[ProductsImport] OMITIENDO FILA: supplier_id inválido o faltante y no importa un Proveedor.", $row);
             $this->onError(new \Exception("Falta supplier_id o es inválido en fila")); // No podemos saber el número de fila aquí fácilmente
             return null;
        }
         if (!Supplier::where('id', $supplierIdToUse)->exists()) {
              Log::error("[ProductsImport] OMITIENDO FILA: Proveedor ID '{$supplierIdToUse}' no existe.", $row);
              $this->onError(new \Exception("Proveedor ID {$supplierIdToUse} no existe"));
              return null;
         }
        if (empty($productCode)) {
             Log::error("[ProductsImport] OMITIENDO FILA: Falta el código del producto.", $row);
             $this->onError(new \Exception("Falta código de producto"));
             return null;
        }
         if (empty($productName)) { 
             Log::error("[ProductsImport] OMITIENDO FILA: Falta el nombre del producto.", $row);
             $this->onError(new \Exception("Falta nombre de producto"));
             return null;
         }

        Log::debug("[ProductsImport] Datos limpios: supplierId={$supplierIdToUse}, productCode='{$productCode}', productName='{$productName}', categoryName='{$categoryName}'");

        // Buscar ID de categoría (es opcional)
        $categoryId = null; // Por defecto null
        if (!empty($categoryName)) {
            $categoryId = $this->categories->get($categoryName);
            if (is_null($categoryId)) {
                 Log::warning("[ProductsImport] Categoría '{$row['category_name']}' no encontrada, se asignará NULL.", $row);
            }
        }

        // --- LÓGICA updateOrCreate ---
        try {
            // Clave única compuesta
            $uniqueKeyData = [
                'supplier_id' => $supplierIdToUse,
                'code'        => $productCode,
            ];

            // Datos a actualizar o insertar
            $productDataToUpdate = [
                'name'        => $productName, 
                'description' => $productDescription,
                'price'       => $productPrice,
                'category_id' => $categoryId, // Será null si no se encontró o no se especificó
            ];

            Log::debug("[ProductsImport] Intentando updateOrCreate con Key:", $uniqueKeyData);
            Log::debug("[ProductsImport] Intentando updateOrCreate con Data:", $productDataToUpdate);

            $product = Product::updateOrCreate($uniqueKeyData, $productDataToUpdate);

            Log::info("[ProductsImport] Producto ID {$product->id} ('{$product->name}') procesado.");
            return $product; 

        } catch (\Exception $e) {
            Log::error("[ProductsImport] Error en DB al importar producto. Key: " . json_encode($uniqueKeyData) . ". Data: " . json_encode($productDataToUpdate) . ". Error: " . $e->getMessage());
            $this->onError($e); 
            return null; 
        }
    }

    /**
     * Reglas de validación para cada fila (ajustadas al nuevo orden y opcionalidad).
     */
    public function rules(): array
    {
        return [
            // Validar supplier_id solo si no importa un Proveedor
            'supplier_id' => [Rule::requiredIf(Auth::user() && !Auth::user()->hasRole('Proveedor')), 'nullable', 'integer', 'exists:suppliers,id'], 
            'codigo' => ['required', 'string', 'max:50'], // Código es requerido
            'nombre' => ['required', 'string', 'max:255'], // Nombre es requerido
            'descripcion' => ['nullable', 'string'],
            'precio' => ['nullable', 'present'], 
            'category_name' => ['nullable', 'string', 'max:255'], // Categoría es opcional
             // Validación personalizada para precio
             'precio' => [
                 'nullable', 'present',
                 function ($attribute, $value, $fail) {
                     if (!is_null($value) && $value !== '') {
                         $numericValue = str_replace(',', '.', $value);
                         if (!is_numeric($numericValue)) { $fail('El campo :attribute debe ser un número válido.'); }
                         elseif (floatval($numericValue) < 0) { $fail('El campo :attribute debe ser mayor o igual a 0.'); }
                     }
                 },
             ],
        ];
    }

    /**
     * Manejo de errores por fila.
     */
    public function onError(Throwable $e)
    {
        Log::error("[ProductsImport] Error procesando fila: " . $e->getMessage());
        // El trait SkipsErrors se encarga de registrar y saltar.
    }

} // Fin clase ProductsImport
