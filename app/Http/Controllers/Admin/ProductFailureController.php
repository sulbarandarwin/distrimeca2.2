<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductFailure;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Exports\ProductFailuresExport; // Para la exportación
use Maatwebsite\Excel\Facades\Excel;   // Para la exportación

class ProductFailureController extends Controller
{
    public function __construct()
    {
        // Asegúrate de que estos permisos existan y estén asignados
        $this->middleware('permission:view product_failures|manage product_failures', ['only' => ['index']]);
        $this->middleware('permission:manage product_failures', ['only' => ['create', 'store', 'show', 'edit', 'update', 'destroy']]);
        $this->middleware('permission:export product_failures', ['only' => ['export']]);
    }

    public function index(Request $request)
    {
        $query = ProductFailure::with(['product', 'user'])->latest('failure_date');

        $filterType = $request->input('filter_type', 'current_week');
        $dateStartInput = $request->input('date_start');
        $dateEndInput = $request->input('date_end');

        $viewDateStart = $dateStartInput;
        $viewDateEnd = $dateEndInput;
        $filterTitle = "Semana Actual";

        switch ($filterType) {
            case 'current_week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                $query->whereBetween('failure_date', [$start, $end]);
                $viewDateStart = $start->toDateString();
                $viewDateEnd = $end->toDateString();
                $filterTitle = "Semana Actual (" . $start->format('d/m/Y') . " - " . $end->format('d/m/Y') . ")";
                break;
            case 'current_month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                $query->whereBetween('failure_date', [$start, $end]);
                $viewDateStart = $start->toDateString();
                $viewDateEnd = $end->toDateString();
                $filterTitle = "Mes Actual (" . $start->format('F Y') . ")";
                break;
            case 'custom_range':
                $filterTitle = "Rango Personalizado";
                if ($dateStartInput) {
                    $query->where('failure_date', '>=', Carbon::parse($dateStartInput)->startOfDay());
                    $filterTitle .= " desde " . Carbon::parse($dateStartInput)->format('d/m/Y');
                }
                if ($dateEndInput) {
                    $query->where('failure_date', '<=', Carbon::parse($dateEndInput)->endOfDay());
                    $filterTitle .= ($dateStartInput ? " hasta " : "Hasta ") . Carbon::parse($dateEndInput)->format('d/m/Y');
                }
                break;
            case 'all':
                 $filterTitle = "Todos los Registros";
                break;
            default:
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                $query->whereBetween('failure_date', [$start, $end]);
                $viewDateStart = $start->toDateString();
                $viewDateEnd = $end->toDateString();
                $filterTitle = "Semana Actual (" . $start->format('d/m/Y') . " - " . $end->format('d/m/Y') . ")";
                break;
        }

        $failures = $query->paginate(15)->appends($request->query());

        return view('admin.failures.index', compact('failures', 'filterType', 'viewDateStart', 'viewDateEnd', 'filterTitle'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->pluck('name', 'id');
        return view('admin.failures.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'failure_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $validatedData['user_id'] = Auth::id();
        $validatedData['failure_date'] = Carbon::parse($validatedData['failure_date']);

        try {
            ProductFailure::create($validatedData);
            return redirect()->route('admin.failures.index')->with('success', 'Falla de producto registrada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al registrar falla de producto: ' . $e->getMessage(), ['data' => $validatedData, 'exception' => $e]);
            return back()->with('error', 'Error al registrar la falla. Detalles: ' . $e->getMessage())->withInput();
        }
    }

    public function show(ProductFailure $productFailure)
    {
        $productFailure->load('product', 'user');
        return redirect()->route('admin.failures.edit', $productFailure);
    }

    public function edit(ProductFailure $productFailure)
    {
        $products = Product::orderBy('name')->pluck('name', 'id');
        return view('admin.failures.edit', compact('productFailure', 'products'));
    }

    public function update(Request $request, ProductFailure $productFailure)
    {
        $validatedData = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'failure_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $validatedData['failure_date'] = Carbon::parse($validatedData['failure_date']);

        try {
            $productFailure->update($validatedData);
            return redirect()->route('admin.failures.index')->with('success', 'Registro de falla actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar falla de producto: ' . $e->getMessage(), ['id' => $productFailure->id, 'data' => $validatedData, 'exception' => $e]);
            return back()->with('error', 'Error al actualizar la falla. Detalles: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(ProductFailure $productFailure)
    {
        try {
            $productFailure->delete();
            return redirect()->route('admin.failures.index')->with('success', 'Registro de falla eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar falla de producto: ' . $e->getMessage(), ['id' => $productFailure->id, 'exception' => $e]);
            return back()->with('error', 'Error al eliminar la falla. Detalles: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        Log::info("ProductFailureController@export: Solicitud de exportación recibida con filtros:", $request->all());

        $filterType = $request->input('filter_type', 'current_week');
        $dateStartInput = $request->input('date_start');
        $dateEndInput = $request->input('date_end');

        $query = ProductFailure::query();

        switch ($filterType) {
            case 'current_week':
                $query->whereBetween('failure_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'current_month':
                $query->whereBetween('failure_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'custom_range':
                if ($dateStartInput) {
                    $query->where('failure_date', '>=', Carbon::parse($dateStartInput)->startOfDay());
                }
                if ($dateEndInput) {
                    $query->where('failure_date', '<=', Carbon::parse($dateEndInput)->endOfDay());
                }
                break;
            case 'all':
                // No se aplica filtro de fecha si es 'all'
                break;
            default:
                 $query->whereBetween('failure_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
        }

        $fileName = 'fallas_productos_';
        if ($filterType === 'custom_range' && $dateStartInput && $dateEndInput) {
            $fileName .= Carbon::parse($dateStartInput)->format('Ymd') . '_a_' . Carbon::parse($dateEndInput)->format('Ymd');
        } elseif ($filterType === 'current_week') {
            $fileName .= 'semana_actual_' . Carbon::now()->startOfWeek()->format('Ymd') . '_a_' . Carbon::now()->endOfWeek()->format('Ymd');
        } elseif ($filterType === 'current_month') {
            $fileName .= 'mes_actual_' . Carbon::now()->format('Y_m');
        } elseif ($filterType === 'all') {
            $fileName .= 'todos';
        } else {
            $fileName .= 'semana_actual_' . Carbon::now()->startOfWeek()->format('Ymd') . '_a_' . Carbon::now()->endOfWeek()->format('Ymd');
        }
        $fileName .= '.xlsx';

        try {
            return Excel::download(new ProductFailuresExport($query), $fileName);
        } catch (\Exception $e) {
            Log::error("ProductFailureController@export: Error al generar Excel: " . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Ocurrió un error al generar el archivo Excel.');
        }
    }
}