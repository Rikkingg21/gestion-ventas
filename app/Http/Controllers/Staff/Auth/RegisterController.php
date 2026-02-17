<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nro_documento' => 'required|string|max:20|unique:users',
            'tipo_documento' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nro_documento' => $request->nro_documento,
            'tipo_documento' => $request->tipo_documento,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
        ]);

        // Iniciar sesión automáticamente
        auth()->login($user);

        // Redirigir al dashboard
        return redirect()->route('dashboard');
    }
}
