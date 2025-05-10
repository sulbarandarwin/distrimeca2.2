<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Importante importar el BaseController de Laravel

abstract class Controller extends BaseController // Asegúrate que extienda de BaseController
{
    use AuthorizesRequests, ValidatesRequests; // Traits para $this->authorize() y $this->validate()
                                               // El método $this->middleware() se hereda de BaseController
}