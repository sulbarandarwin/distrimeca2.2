<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Simplemente mostramos una vista para el dashboard
        return view('admin.dashboard');
    }
}