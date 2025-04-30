<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // <-- Importar modelo de ActivityLog

class AuditLogController extends Controller
{
    /**
     * Muestra el log de auditoría.
     */
    public function index()
    {
        // Obtenemos las últimas actividades registradas, paginadas
        // Cargamos las relaciones 'causer' (quién lo hizo) y 'subject' (qué se modificó)
        // para poder mostrar sus nombres, etc., en la vista.
        $activities = Activity::with(['causer', 'subject'])
                            ->latest() // Ordenar por más reciente
                            ->paginate(25); // Mostrar 25 entradas por página (puedes ajustar)

        // Pasamos la colección de actividades a la vista
        return view('admin.audit.index', compact('activities'));
    }
}
