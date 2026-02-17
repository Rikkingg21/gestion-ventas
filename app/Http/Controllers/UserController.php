<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::all();
        return response()->json($usuarios);
    }
    public function show($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario);
    }
    public function store(Request $request)
    {
        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nro_documento' => $request->nro_documento,
            'tipo_documento' => $request->tipo_documento,
            'telefono' => $request->telefono,
            'password' => bcrypt($request->password) // Encriptamos la contraseña
        ]);

        return response()->json($usuario, 201);
    }
    public function update(Request $request, $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->update($request->all());

        return response()->json($usuario);
    }
    public function destroy($id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }

        $usuario->delete();

        return response()->json(['mensaje' => 'Usuario eliminado correctamente']);
    }
}
