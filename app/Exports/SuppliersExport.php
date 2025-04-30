<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles; // Para estilos básicos
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Para estilos

class SuppliersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
    * Obtenemos la colección de todos los proveedores con sus relaciones.
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Cargamos todas las relaciones necesarias para el mapeo
        return Supplier::with(['country', 'state', 'types'])->get();
    }

    /**
     * Define los encabezados de las columnas en el Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        // Estos serán los títulos de la primera fila
        return [
            'ID',
            'Nombre',
            'RIF',
            'Teléfono 1',
            'Teléfono 2',
            'Email',
            'País',
            'Estado',
            'Tipos', // Columna para los tipos concatenados
        ];
    }

    /**
     * Mapea los datos de cada proveedor a las columnas del Excel.
     *
     * @param mixed $supplier Objeto Supplier de la colección
     * @return array
     */
    public function map($supplier): array
    {
        // Devolvemos un array con los datos en el orden de los headings
        return [
            $supplier->id,
            $supplier->name,
            $supplier->rif ?? '', // Usamos ?? '' para evitar errores si es null
            $supplier->phone1 ?? '',
            $supplier->phone2 ?? '',
            $supplier->email ?? '',
            $supplier->country->name ?? '', // Accedemos al nombre a través de la relación
            $supplier->state->name ?? '',   // Accedemos al nombre a través de la relación
            $supplier->types->pluck('name')->join(', '), // Concatenamos los nombres de los tipos
        ];
    }

    /**
     * Aplica estilos básicos a la hoja.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Poner en negrita la fila de encabezados (fila 1)
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
