<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CarritoController;
use App\Models\User;
use App\Models\Client;
use App\Traits\CountryDocumentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    use CountryDocumentTrait;

    public function showRegistrationForm()
    {
        $countries = $this->getCountries();
        $documentTypes = $this->getDocumentTypesForCountry(old('pais', 'PE'));

        return view('client.auth.register', compact('countries', 'documentTypes'));
    }

    public function register(Request $request)
    {
        // Validar los datos usando el trait
        $validator = Validator::make(
            $request->all(),
            $this->getValidationRules(),
            $this->getValidationMessages()
        );

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput()
                        ->with('show_errors', true);
        }

        // Validación específica del formato del documento por tipo
        $validationResult = $this->validateDocumentFormat($request->tipo_documento, $request->nro_documento);
        if ($validationResult !== true) {
            return redirect()->back()
                        ->withErrors(['nro_documento' => $validationResult])
                        ->withInput()
                        ->with('show_errors', true);
        }

        // Guardar session ID antes del registro
        $oldSessionId = Session::getId();

        DB::beginTransaction();

        try {
            $username = $this->generateUsername(
                $request->nombres,
                $request->apellido_paterno,
                $request->apellido_materno
            );

            $user = User::create([
                'username' => $username,
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'pais' => $request->pais,
                'email' => $request->email,
                'nro_documento' => $this->normalizeDocumentNumber($request->nro_documento),
                'tipo_documento' => $request->tipo_documento,
                'telefono' => $request->telefono,
                'password' => Hash::make($request->password),
            ]);

            Client::create([
                'user_id' => $user->id,
                'email' => $request->email,
                'is_active' => true
            ]);

            DB::commit();

            Auth::guard('client')->login($user);
            $request->session()->regenerate();

            $carritoController = new CarritoController();
            if (method_exists($carritoController, 'migrarCarritoSesionACliente')) {
                $carritoController->migrarCarritoSesionACliente($oldSessionId);
            }

            return redirect()->route('home')
                ->with('success', '¡Registro exitoso! Bienvenido a nuestra plataforma, ' . $user->nombres . '.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error en registro de cliente: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al registrar. Por favor, intenta nuevamente.')
                ->withInput()
                ->with('show_errors', true);
        }
    }

    // Método AJAX para obtener tipos de documento
    public function getDocumentTypes($countryCode)
    {
        $types = $this->getDocumentTypesForCountry($countryCode);
        return response()->json($types);
    }
}
