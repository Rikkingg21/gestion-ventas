<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        // Obtener todos los módulos con sus relaciones
        $modules = Module::with(['permissions', 'children'])
                        ->orderBy('order_position')
                        ->orderBy('name')
                        ->get();

        return view('admin.modules.index', compact('modules'));
    }
    public function create()
    {
        return view('admin.modules.create');
    }
}
