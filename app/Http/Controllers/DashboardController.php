<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product; // Para productos del proveedor
use App\Models\Supplier; // Para últimos proveedores (Vendedor)

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard general para usuarios no administradores,
     * o redirige a los administradores a su dashboard específico.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();

        // Si el usuario es Admin, redirigir a admin.dashboard
        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Lógica para otros roles (como ya la tenías)
        $viewData = ['user' => $user];

        if ($user->hasRole('Proveedor')) {
            $supplierIds = $user->suppliers()->pluck('suppliers.id')->toArray();
            if (!empty($supplierIds)) {
                $viewData['supplierProducts'] = Product::whereIn('supplier_id', $supplierIds)
                                                    ->latest()
                                                    ->take(5)
                                                    ->get();
            } else {
                $viewData['supplierProducts'] = collect();
            }
        } elseif ($user->hasRole('Vendedor')) {
            $viewData['latestSuppliers'] = Supplier::latest()->take(5)->get();
        }
        // Otros roles pueden tener sus propios datos aquí

        return view('dashboard', $viewData);
    }
}