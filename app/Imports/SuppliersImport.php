<?php

namespace App\Imports;

use App\Models\Supplier;
use App\Models\Country;
use App\Models\State;
use App\Models\SupplierType;
use Maatwebsite\Excel\Concerns\ToModel; // Usamos ToModel para procesar fila por fila
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts; // Para eficiencia
use Maatwebsite\Excel\Concerns\WithChunkReading; // Para eficiencia
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Throwable;

class SuppliersImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnError
{
    use SkipsErrors; // Permite registrar errores y continuar

    private Collection $countries;     // Mapa: nombre_minusculas => id
    private Collection $states;        // Mapa: nombre_minusculas => id
    private Collection $supplierTypes; // Mapa: nombre_minusculas => id

    public function __construct()
    {
        // Cargar mapas una vez para eficiencia
        $this->countries = Country::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        });
        $this->states = State::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        });
        $this->supplierTypes = SupplierType::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        });
        Log::debug('[SuppliersImport] Constructor: Countries, States, Types cargados.');
    }

    /**
    * Procesa cada fila del archivo Excel.
    * @param array $row
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $rowNumber = method_exists($this, 'getRowNumber') ? $this->getRowNumber() : '?';
        Log::debug("[SuppliersImport ROW {$rowNumber}] --- Inicio Procesamiento ---", $row);

        // Limpiar y obtener datos, usando los nombres de encabezado del Excel
        $countryName = strtolower(trim($row['pais'] ?? ''));
        $stateName = strtolower(trim($row['estado'] ?? ''));
        $typesString = trim($row['tipos'] ?? ''); // Tipos separados por coma
        $supplierName = trim($row['nombre'] ?? null);
        $supplierRif = trim($row['rif'] ?? null); // Clave única principal
        $supplierEmail = trim($row['email'] ?? null);
        $supplierPhone1 = trim($row['telefono_1'] ?? null);
        $supplierPhone2 = trim($row['telefono_2'] ?? null);

        Log::debug("[SuppliersImport ROW {$rowNumber}] Datos extraídos: Name='{$supplierName}', RIF='{$supplierRif}', Country='{$countryName}', State='{$stateName}', Types='{$typesString}'");

        // Buscar IDs de País y Estado
        $countryId = $this->countries->get($countryName);
        $stateId = $this->states->get($stateName);

        // Validar País y Estado manualmente (más flexible que Rule::in para case-insensitivity)
        if (!$countryId) {
            Log::warning("[SuppliersImport ROW {$rowNumber}] OMITIENDO: País '{$row['pais']}' no encontrado.");
            $this->onError(new \Exception("País inválido: {$row['pais']}"));
            return null;
        }
        if (!$stateId) {
            Log::warning("[SuppliersImport ROW {$rowNumber}] OMITIENDO: Estado '{$row['estado']}' no encontrado.");
             $this->onError(new \Exception("Estado inválido: {$row['estado']}"));
            return null;
        }
        Log::debug("[SuppliersImport ROW {$rowNumber}] CountryID={$countryId}, StateID={$stateId}");


        // --- Preparar datos para guardar ---
        $supplierData = [
            'name' => $supplierName,
            'email' => $supplierEmail,
            'phone1' => $supplierPhone1,
            'phone2' => $supplierPhone2,
            'country_id' => $countryId,
            'state_id' => $stateId,
            // RIF se usa como clave única, se añade si no está vacío
        ];
         if (!empty($supplierRif)) {
             $supplierData['rif'] = $supplierRif;
         }

        // --- Clave única para updateOrCreate ---
        // Usaremos RIF como clave principal si existe, sino nombre
        $uniqueKeyData = [];
        if (!empty($supplierRif)) {
            $uniqueKeyData['rif'] = $supplierRif;
            // Si RIF es la clave, no necesita estar en los datos a actualizar/crear
             unset($supplierData['rif']);
        } else {
            // Si no hay RIF, usamos nombre como clave (menos fiable)
            $uniqueKeyData['name'] = $supplierName;
             // Si usamos nombre como clave, el RIF (si existe) SÍ debe estar en $supplierData
             if (!empty($supplierRif)) $supplierData['rif'] = $supplierRif;
        }

        // --- Ejecutar updateOrCreate ---
        try {
            Log::debug("[SuppliersImport ROW {$rowNumber}] Intentando updateOrCreate con Key:", $uniqueKeyData);
            Log::debug("[SuppliersImport ROW {$rowNumber}] Intentando updateOrCreate con Data:", $supplierData);

            $supplier = Supplier::updateOrCreate($uniqueKeyData, $supplierData);

            // --- Sincronizar Tipos ---
            if ($supplier && !empty($typesString)) {
                $typeNames = explode(',', $typesString);
                $typeIds = [];
                foreach ($typeNames as $typeName) {
                    $lowerTypeName = strtolower(trim($typeName));
                    $typeId = $this->supplierTypes->get($lowerTypeName);
                    if ($typeId) {
                        $typeIds[] = $typeId;
                    } else {
                        Log::warning("[SuppliersImport ROW {$rowNumber}] Tipo de proveedor no encontrado: '{$typeName}' para proveedor '{$supplier->name}'.");
                    }
                }
                if (!empty($typeIds)) {
                    $supplier->types()->sync($typeIds);
                    Log::debug("[SuppliersImport ROW {$rowNumber}] Tipos sincronizados: ", $typeIds);
                } else {
                     $supplier->types()->sync([]);
                     Log::debug("[SuppliersImport ROW {$rowNumber}] No se encontraron tipos válidos para sincronizar.");
                }
            } elseif($supplier) {
                 $supplier->types()->sync([]); // Desvincular si no se especificaron tipos
                 Log::debug("[SuppliersImport ROW {$rowNumber}] Columna 'tipos' vacía, desvinculando tipos.");
            }

            if ($supplier->wasRecentlyCreated) {
                Log::info("[SuppliersImport ROW {$rowNumber}] Proveedor ID {$supplier->id} ('{$supplier->name}') CREADO.");
            } else {
                Log::info("[SuppliersImport ROW {$rowNumber}] Proveedor ID {$supplier->id} ('{$supplier->name}') ACTUALIZADO.");
            }
            return $supplier;

        } catch (\Exception $e) {
            Log::error("[SuppliersImport ROW {$rowNumber}] Error en DB. Key: " . json_encode($uniqueKeyData) . ". Data: " . json_encode($supplierData) . ". Error: " . $e->getMessage());
            $this->onError($e);
            return null;
        }
    }

    /**
     * Reglas de validación para cada fila.
     */
    public function rules(): array
    {
        // Encabezados Excel deben coincidir con estas claves
        return [
            'nombre' => ['required', 'string', 'max:255'],
            // La unicidad de RIF/Email se maneja en la lógica de updateOrCreate
            'rif' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'telefono_1' => ['nullable', 'string', 'max:50'],
            'telefono_2' => ['nullable', 'string', 'max:50'],
            // Validamos que país y estado sean texto, la existencia se valida en model()
            'pais' => ['required', 'string'],
            'estado' => ['required', 'string'],
            'tipos' => ['nullable', 'string'], // La validación de cada tipo se hace en model()
        ];
    }

    // Mantenemos BatchInserts y ChunkReading para optimizar
    public function batchSize(): int { return 200; }
    public function chunkSize(): int { return 200; }

    public function onError(Throwable $e)
    {
        $rowNumber = method_exists($this, 'getRowNumber') ? $this->getRowNumber() : '?';
        Log::error("[SuppliersImport] Error en fila {$rowNumber}: " . $e->getMessage());
    }
}
