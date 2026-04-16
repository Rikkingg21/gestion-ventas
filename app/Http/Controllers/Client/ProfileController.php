<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\CountryDocumentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use CountryDocumentTrait;

    public function edit()
    {
        // Obtener el usuario autenticado desde el guard 'client'
        $user = Auth::guard('client')->user();

        // Verificar si el usuario existe
        if (!$user) {
            return redirect()->route('client.login')->with('error', 'Debes iniciar sesión primero.');
        }

        // Obtener el cliente asociado al usuario
        $client = $user->client;

        // Verificar si el cliente existe
        if (!$client) {
            Log::warning('Usuario sin cliente asociado', ['user_id' => $user->id, 'email' => $user->email]);
            return redirect()->route('client.login')->with('error', 'Error en tu cuenta. Contacta con soporte.');
        }

        // Obtener países y tipos de documento usando el trait
        $countries = $this->getCountries();
        $documentTypes = $this->getDocumentTypesForCountry($user->pais ?? 'PE');

        return view('client.profile.edit', compact('user', 'client', 'countries', 'documentTypes'));
    }

    public function update(Request $request)
    {
        // Obtener el usuario autenticado
        $user = Auth::guard('client')->user();

        if (!$user) {
            return redirect()->route('client.login')->with('error', 'Debes iniciar sesión primero.');
        }

        $client = $user->client;

        if (!$client) {
            return redirect()->route('client.profile.edit')->with('error', 'Error al cargar tu información de cliente.');
        }

        // Validar los datos usando el trait
        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            $this->getValidationRules($user->id),
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

        try {
            DB::beginTransaction();

            // Actualizar datos del usuario
            $user->nombres = $request->nombres;
            $user->apellido_paterno = $request->apellido_paterno;
            $user->apellido_materno = $request->apellido_materno;
            $user->email = $request->email;
            $user->tipo_documento = $request->tipo_documento;
            $user->nro_documento = $this->normalizeDocumentNumber($request->nro_documento);
            $user->telefono = $request->telefono;
            $user->pais = $request->pais;

            // Actualizar username si es necesario
            if ($user->wasChanged(['nombres', 'apellido_paterno', 'apellido_materno'])) {
                $newUsername = $this->generateUsername(
                    $request->nombres,
                    $request->apellido_paterno,
                    $request->apellido_materno
                );
                $user->username = $newUsername;
            }

            $user->save();

            // Actualizar email en el cliente si es necesario
            if ($client->email !== $request->email) {
                $client->email = $request->email;
                $client->save();
            }

            DB::commit();

            return redirect()->route('client.profile.edit')
                ->with('success', 'Perfil actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar perfil: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->route('client.profile.edit')
                ->with('error', 'Ocurrió un error al actualizar el perfil. Por favor, intenta nuevamente.');
        }
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('client')->user();

        if (!$user) {
            return redirect()->route('client.login')->with('error', 'Debes iniciar sesión primero.');
        }

        $client = $user->client;

        if (!$client) {
            return redirect()->route('client.profile.edit')->with('error', 'Error al cargar tu información de cliente.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                        ->withErrors(['current_password' => 'La contraseña actual es incorrecta.'])
                        ->withInput();
        }

        try {
            // Actualizar contraseña
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->route('client.profile.edit')
                ->with('success', 'Contraseña actualizada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar contraseña: ' . $e->getMessage());
            return redirect()->route('client.profile.edit')
                ->with('error', 'Ocurrió un error al actualizar la contraseña. Por favor, intenta nuevamente.');
        }
    }

    // Método AJAX para obtener tipos de documento según el país
    public function getDocumentTypes($countryCode)
    {
        $types = $this->getDocumentTypesForCountry($countryCode);
        return response()->json($types);
    }
}
