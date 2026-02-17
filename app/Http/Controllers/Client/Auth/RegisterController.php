<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /**
     * Mostrar el formulario de registro
     */
    public function showRegistrationForm()
    {
        return view('client.auth.register');
    }

    /**
     * Procesar el registro
     */
    public function register(Request $request)
    {
        // Validar los datos (sin username)
        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'tipo_documento' => 'required|string|max:20|in:DNI,RUC,CE,PASAPORTE',
            'nro_documento' => 'required|string|max:20|unique:users,nro_documento',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'terminos' => 'required|accepted'
        ], [
            'nombres.required' => 'Los nombres son obligatorios',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio',
            'apellido_materno.required' => 'El apellido materno es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está registrado',
            'email.email' => 'Ingresa un email válido',
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'tipo_documento.in' => 'Selecciona un tipo de documento válido',
            'nro_documento.required' => 'El número de documento es obligatorio',
            'nro_documento.unique' => 'Este documento ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'terminos.required' => 'Debes aceptar los términos y condiciones',
            'terminos.accepted' => 'Debes aceptar los términos y condiciones para registrarte'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        // Usar transacción para asegurar que ambas tablas se actualicen correctamente
        DB::beginTransaction();

        try {
            // Crear el username automáticamente basado en el email o nombre
            $username = $this->generateUsername($request->nombres, $request->apellido_paterno);

            // Crear el usuario
            $user = User::create([
                'username' => $username, // Generado automáticamente
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'nro_documento' => $request->nro_documento,
                'tipo_documento' => $request->tipo_documento,
                'telefono' => $request->telefono,
                'password' => Hash::make($request->password),
            ]);

            // Crear el cliente asociado
            Client::create([
                'user_id' => $user->id,
                'is_active' => true
            ]);

            DB::commit();

            // Iniciar sesión automáticamente
            auth()->guard('client')->login($user);

            // Redirigir al dashboard con mensaje de éxito
            return redirect()->route('client.dashboard')
                ->with('success', '¡Registro exitoso! Bienvenido a nuestra plataforma.');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Error en registro de cliente: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al registrar. Por favor, intenta nuevamente.')
                ->withInput();
        }
    }
    private function generateUsername($nombres, $apellidoPaterno)
    {
        // Tomar la primera parte del nombre y el apellido
        $nombreParte = explode(' ', trim($nombres))[0];
        $base = strtolower($nombreParte . '.' . $apellidoPaterno);

        // Eliminar caracteres especiales y espacios
        $base = preg_replace('/[^a-z0-9.]/', '', $base);

        $username = $base;
        $counter = 1;

        // Verificar si el username ya existe
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
