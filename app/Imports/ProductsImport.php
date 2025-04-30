<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
// Quitamos WithBatchInserts, WithChunkReading
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Throwable;

// Quitamos WithBatchInserts, WithChunkReading de la lista de implements
class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private Collection $suppliers;
    private Collection $categories; // Mapa: nombre_minusculas => id

    public function __construct()
    {
        $this->suppliers = Supplier::pluck('id', 'id');
        $this->categories = Category::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        });
        Log::debug('[ProductsImport] Constructor: Suppliers y Categories cargados.');
    }

    /**
    * @param array $row
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        Log::debug("[ProductsImport] --- Procesando Fila Excel ---", $row);

        // Limpiar y obtener datos
        $supplierIdRaw = trim($row['supplier_id'] ?? null);
        $supplierId = is_numeric($supplierIdRaw) ? intval($supplierIdRaw) : null;
        $categoryName = strtolower(trim($row['category_name'] ?? ''));
        $productName = trim($row['nombre'] ?? null);
        $productCode = trim($row['codigo'] ?? null);
        $productDescription = trim($row['descripcion'] ?? null);
        $productPriceRaw = trim($row['precio'] ?? null);
        $productPrice = is_numeric(str_replace(',', '.', $productPriceRaw)) ? floatval(str_replace(',', '.', $productPriceRaw)) : null;

        Log::debug("[ProductsImport] Datos extraídos: supplierId={$supplierId}, categoryName='{$categoryName}', productName='{$productName}', productCode='{$productCode}'");

        // Validar supplier_id
        if (is_null($supplierId) || !$this->suppliers->has($supplierId)) {
            Log::warning("[ProductsImport] OMITIENDO FILA: Proveedor ID '{$supplierIdRaw}' inválido o no existe.", $row);
            return null;
        }

        // Buscar ID de categoría
        $categoryId = $this->categories->get($categoryName);
        if (!empty($categoryName) && is_null($categoryId)) {
             Log::warning("[ProductsImport] Categoría '{$row['category_name']}' no encontrada, se asignará NULL.", $row);
        }
        Log::debug("[ProductsImport] CategoryID encontrado/asignado: " . ($categoryId ?? 'NULL'));

        // Preparar datos para la BD
        $productData = [
            'name' => $productName,
            'description' => $productDescription,
            'price' => $productPrice,
            'supplier_id' => $supplierId,
            'category_id' => $categoryId, // <-- ID numérico o null
        ];
        // Añadir código solo si no está vacío Y no es la clave de búsqueda
         if (!empty($productCode)) {
            $productData['code'] = $productCode;
        }

        // Definir clave única
        $uniqueKeyData = [];
         if (!empty($productCode)) {
            $uniqueKeyData['code'] = $productCode;
            // Si code es la clave, no necesita estar en $productData para updateOrCreate
            // unset($productData['code']); // Descomentar si causa problemas
        } else {
            $uniqueKeyData['name'] = $productName;
            $uniqueKeyData['supplier_id'] = $supplierId;
        }


        // Ejecutar updateOrCreate
        try {
            Log::debug("[ProductsImport] Intentando updateOrCreate con Key:", $uniqueKeyData);
            Log::debug("[ProductsImport] Intentando updateOrCreate con Data:", $productData);

            $product = Product::updateOrCreate($uniqueKeyData, $productData);

            Log::info("[ProductsImport] Producto ID {$product->id} ('{$product->name}') procesado.");
            return $product; // Devolver el modelo procesado

        } catch (\Exception $e) {
            Log::error("[ProductsImport] Error en DB al importar producto. Key: " . json_encode($uniqueKeyData) . ". Data: " . json_encode($productData) . ". Error: " . $e->getMessage());
            $this->onError($e);
            return null;
        }
    }

    /**
     * Reglas de validación para cada fila.
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['nullable', 'present'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'category_name' => ['nullable', 'string', 'max:255'],
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

    // ¡ELIMINADOS batchSize() y chunkSize()!

    public function onError(Throwable $e)
    {
        $rowNumber = method_exists($this, 'getRowNumber') ? $this->getRowNumber() : '?';
        Log::error("[ProductsImport] Error en fila {$rowNumber}: " . $e->getMessage());
    }
}
