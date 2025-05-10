<?php

namespace App\Exports;

use App\Models\ProductFailure; // Asegúrate que el modelo ProductFailure esté correctamente referenciado
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder; // Para el tipado de la consulta

class ProductFailuresExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        // La consulta ya viene con los filtros aplicados desde el controlador
        $this->query = $query;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        // Asegurar que las relaciones necesarias para el mapeo estén cargadas
        return $this->query->with(['product:id,name,code', 'user:id,name'])->orderBy('failure_date', 'desc');
    }

    /**
     * Define los encabezados de las columnas en el Excel.
     */
    public function headings(): array
    {
        return [
            'ID Falla',
            'Fecha de Falla',
            'ID Producto',
            'Código Producto',
            'Nombre Producto',
            'Descripción de la Falla',
            'ID Usuario Registra',
            'Nombre Usuario Registra',
            'Registrado en Sistema (Fecha)',
        ];
    }

    /**
     * Mapea los datos de cada registro de falla a las columnas del Excel.
     * @param ProductFailure $failure El objeto ProductFailure
     */
    public function map($failure): array
    {
        return [
            $failure->id,
            $failure->failure_date->format('d/m/Y H:i:s'),
            $failure->product_id,
            $failure->product->code ?? 'N/A',
            $failure->product->name ?? 'Producto Eliminado o Desconocido',
            $failure->description,
            $failure->user_id,
            $failure->user->name ?? 'Sistema/Usuario Eliminado',
            $failure->created_at->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Aplica estilos básicos a la hoja.
     */
    public function styles(Worksheet $sheet)
    {
        // Poner en negrita la fila de encabezados (fila 1)
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        // Ejemplo: Alinear texto a la izquierda para descripciones
        // $sheet->getStyle('E:E')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        
        return []; // Devolver array vacío o configuraciones de estilo adicionales
    }
}