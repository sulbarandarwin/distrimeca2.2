<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // Modelo ActivityLog
use Maatwebsite\Excel\Facades\Excel;     // Facade Maatwebsite Excel
use App\Exports\ActivityLogExport;       // Nuestra clase Export
use Carbon\Carbon;                       // Carbon para fechas
use Illuminate\Support\Facades\DB;         // Facade DB para consulta de años
use Illuminate\Support\Facades\Log;        // Log Facade

class AuditLogController extends Controller
{
    /**
     * Muestra el listado de registros de auditoría.
     */
    public function index()
    {
        // Obtener registros paginados, más recientes primero, con relaciones cargadas
        $activities = Activity::with(['causer', 'subject']) 
                              ->latest() 
                              ->paginate(25); 

        // Obtener años únicos para los dropdowns de la vista
        $years = Activity::select(DB::raw('YEAR(created_at) as year'))
                         ->distinct()
                         ->orderBy('year', 'desc')
                         ->pluck('year');

        // --- CORRECCIÓN AQUÍ ---
        // Apuntamos a la vista correcta según tu estructura de carpetas
        return view('admin.audit.index', compact('activities', 'years')); 
        // --- FIN CORRECCIÓN ---
    }

    /**
     * Exporta los registros de auditoría a Excel según el rango solicitado.
     */
    public function export(Request $request)
    {
        // Validar los parámetros GET del formulario de exportación
        $validated = $request->validate([
            'export_range' => 'required|in:all,year,custom',
            'export_year' => 'required_if:export_range,year|integer|min:2000|max:' . date('Y'),
            'export_start_date' => 'required_if:export_range,custom|date|nullable',
            'export_end_date' => 'required_if:export_range,custom|date|after_or_equal:export_start_date|nullable',
        ],[
             'export_year.required_if' => 'Debe seleccionar un año para exportar por año.',
             'export_start_date.required_if' => 'Debe seleccionar fecha de inicio para rango personalizado.',
             'export_end_date.required_if' => 'Debe seleccionar fecha de fin para rango personalizado.',
             'export_end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
        ]);

        // Construcción de la consulta base
        $query = Activity::query()->orderBy('created_at', 'desc'); 
        $filename = 'audit_log_export_' . date('YmdHis') . '.xlsx'; // Nombre base

        // Aplicar filtros según el rango
        if ($validated['export_range'] === 'year') {
            $query->whereYear('created_at', $validated['export_year']);
            $filename = 'audit_log_export_' . $validated['export_year'] . '.xlsx';
        } elseif ($validated['export_range'] === 'custom') {
             if($validated['export_start_date'] && $validated['export_end_date']) {
                $startDate = Carbon::parse($validated['export_start_date'])->startOfDay();
                $endDate = Carbon::parse($validated['export_end_date'])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
                 $filename = 'audit_log_export_' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '.xlsx';
            } else {
                 return redirect()->back()->with('error', 'Fechas de inicio y fin son requeridas para rango personalizado.');
            }
        }
        // Si es 'all', no hay filtro adicional

        // Intentar generar y descargar el Excel
        try {
            // Pasamos la consulta ($query) a la clase de exportación
            return Excel::download(new ActivityLogExport($query), $filename);
        } catch (\Exception $e) {
            Log::error("Error al exportar auditoría: " . $e->getMessage());
            // Usamos el nombre de ruta definido en web.php para la redirección
            return redirect()->route('admin.audit-log.index') 
                             ->with('error', 'Ocurrió un error al generar el archivo de exportación.');
        }
    }

    /**
     * Limpia registros con más de 3 meses.
     */
    public function cleanOlderThan3Months()
    {
        try {
            $dateLimit = now()->subMonths(3);
            $deletedCount = Activity::where('created_at', '<', $dateLimit)->delete();

            return redirect()->route('admin.audit-log.index') // Usar nombre de ruta
                             ->with('success', "Se eliminaron $deletedCount registros con más de 3 meses.");
        } catch (\Exception $e) {
             Log::error("Error al limpiar auditoría (> 3 meses): " . $e->getMessage());
             return redirect()->route('admin.audit-log.index') // Usar nombre de ruta
                              ->with('error', 'Error al limpiar registros antiguos.');
        }
    }

    /**
     * Limpia registros de un año específico.
     */
    public function cleanByYear(Request $request)
    {
         $validated = $request->validate(
            ['clean_year' => 'required|integer|min:2000|max:' . date('Y')],
            ['clean_year.required' => 'Debe seleccionar un año para limpiar.']
         );
         $year = $validated['clean_year'];

         try {
            $deletedCount = Activity::whereYear('created_at', $year)->delete();

            return redirect()->route('admin.audit-log.index') // Usar nombre de ruta
                             ->with('success', "Se eliminaron $deletedCount registros del año $year.");
         } catch (\Exception $e) {
             Log::error("Error al limpiar auditoría (año $year): " . $e->getMessage());
              return redirect()->route('admin.audit-log.index') // Usar nombre de ruta
                               ->with('error', "Error al limpiar registros del año $year.");
         }
    }

     /**
      * Limpia TODOS los registros.
      */
     public function cleanAll()
     {
         try {
            DB::table('activity_log')->truncate(); // Usar truncate es más eficiente

             return redirect()->route('admin.audit-log.index') // Usar nombre de ruta
                              ->with('success', "¡Se eliminaron TODOS los registros de auditoría!");
         } catch (\Exception $e) {
              Log::error("Error al limpiar TODA la auditoría: " . $e->getMessage());
               return redirect()->route('admin.audit-log.index') // Usar nombre de ruta
                                ->with('error', 'Error al eliminar todos los registros.');
         }
     }

} // Fin de la clase AuditLogController