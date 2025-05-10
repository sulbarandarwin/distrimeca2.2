<?php

namespace App\Exports;

use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; 
use Illuminate\Database\Eloquent\Builder; 

class ActivityLogExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        // Cargar relaciones para usarlas en map() sin N+1
        return $this->query->with(['causer', 'subject']);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Encabezados de las columnas en Excel
        return [
            'ID Log',
            'Descripción (Evento)',
            'Modelo Afectado',
            'ID Modelo',
            'ID Usuario',
            'Nombre Usuario',
            'Propiedades (JSON)',
            'Fecha y Hora (UTC)',
        ];
    }

    /**
     * @param Activity $activity // El registro de actividad actual
     * @return array
     */
    public function map($activity): array
    {
        // Transforma cada registro de actividad en un array para la fila de Excel
        $subjectType = $activity->subject_type ? class_basename($activity->subject_type) : 'N/A';
        $causerName = $activity->causer?->name ?? ($activity->causer_id ? 'Usuario ID:'.$activity->causer_id : 'Sistema');

        // Formatear propiedades para mejor legibilidad (si es update con old/attributes)
        $propertiesString = '';
        if ($activity->properties->count() > 0) {
             if($activity->description == 'updated' && $activity->properties->has('old') && $activity->properties->has('attributes')) {
                $changes = [];
                foreach($activity->properties['attributes'] as $key => $newValue) {
                     $oldValue = $activity->properties['old'][$key] ?? '[N/A]';
                     if($oldValue != $newValue) {
                         $changes[] = "$key: '$oldValue' -> '$newValue'";
                     }
                }
                $propertiesString = implode('; ', $changes);
             } else {
                  // Si no, simplemente convertir a JSON
                  $propertiesString = $activity->properties->toJson();
             }
        }

        return [
            $activity->id,
            $activity->description, // created, updated, deleted, etc.
            $subjectType,
            $activity->subject_id ?? 'N/A',
            $activity->causer_id ?? 'N/A',
            $causerName,
            $propertiesString, // Propiedades formateadas o JSON
            $activity->created_at->format('Y-m-d H:i:s'), 
        ];
    }
}