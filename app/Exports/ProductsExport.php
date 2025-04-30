<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $productIds;

    // Recibimos los IDs de los productos a exportar (si vienen de la selección)
    // Si $productIds es null, exportamos todos los productos.
    public function __construct(array $productIds = null)
    {
        $this->productIds = $productIds;
    }

    /**
    * Obtenemos la colección de productos a exportar.
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Preparamos la consulta base con las relaciones necesarias
        $query = Product::with(['supplier.country', 'supplier.state', 'category']);

        // Si recibimos IDs específicos, filtramos por ellos
        if (!is_null($this->productIds) && !empty($this->productIds)) {
            $query->whereIn('id', $this->productIds);
        }

        // Obtenemos la colección
        return $query->get();
    }

    /**
     * Define los encabezados de las columnas en el Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        // Títulos de la primera fila
        return [
            'ID Producto',
            'Nombre Producto',
            'Código',
            'Descripción',
            'Precio',
            'Categoría',
            'ID Proveedor',
            'Nombre Proveedor',
            'RIF Proveedor',
            'Teléfono 1 Prov.',
            'Teléfono 2 Prov.',
            'Email Proveedor',
            'País Proveedor',
            'Estado Proveedor',
        ];
    }

    /**
     * Mapea los datos de cada producto a las columnas del Excel.
     *
     * @param mixed $product Objeto Product de la colección
     * @return array
     */
    public function map($product): array
    {
        // Devolvemos un array con los datos en el orden de los headings
        return [
            $product->id,
            $product->name,
            $product->code ?? '',
            $product->description ?? '',
            $product->price ? number_format($product->price, 2, ',', '.') : '', // Formatear precio
            $product->category->name ?? 'N/A', // Nombre de categoría
            $product->supplier->id ?? '', // ID Proveedor
            $product->supplier->name ?? '', // Nombre Proveedor
            $product->supplier->rif ?? '', // RIF Proveedor
            $product->supplier->phone1 ?? '', // Tel 1 Proveedor
            $product->supplier->phone2 ?? '', // Tel 2 Proveedor
            $product->supplier->email ?? '', // Email Proveedor
            $product->supplier->country->name ?? '', // País Proveedor
            $product->supplier->state->name ?? '', // Estado Proveedor
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
