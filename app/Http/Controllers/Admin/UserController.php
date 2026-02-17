<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Verificar permiso específico
        if (!auth()->user()->admin->hasPermission('users.view')) {
            abort(403, 'No tienes permiso para ver usuarios');
        }

        $users = User::paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        if (!auth()->user()->admin->hasPermission('users.create')) {
            abort(403, 'No tienes permiso para crear usuarios');
        }

        return view('admin.users.create');
    }

    public function edit($id)
    {
        if (!auth()->user()->admin->hasPermission('users.edit')) {
            abort(403, 'No tienes permiso para editar usuarios');
        }

        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }
}
