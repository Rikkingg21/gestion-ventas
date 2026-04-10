<?php

namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CarritoController;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    // Lista de países disponibles
    private $countries = [
        'PE' => 'Perú',
        'MX' => 'México',
        'CL' => 'Chile',
        'AR' => 'Argentina',
        'CO' => 'Colombia',
        'US' => 'Estados Unidos',
        'ES' => 'España',
        'EC' => 'Ecuador',
        'BO' => 'Bolivia',
        'UY' => 'Uruguay',
        'PY' => 'Paraguay',
        'VE' => 'Venezuela',
        'CR' => 'Costa Rica',
        'PA' => 'Panamá',
        'GT' => 'Guatemala',
        'SV' => 'El Salvador',
        'HN' => 'Honduras',
        'NI' => 'Nicaragua',
        'DO' => 'República Dominicana',
        'CU' => 'Cuba',
        'BR' => 'Brasil',
        'FR' => 'Francia',
        'DE' => 'Alemania',
        'IT' => 'Italia',
        'GB' => 'Reino Unido',
        'CA' => 'Canadá',
        'AU' => 'Australia',
        'JP' => 'Japón',
        'CN' => 'China',
        'KR' => 'Corea del Sur',
        'IN' => 'India',
        'OTRO' => 'Otro'
    ];

    // Tipos de documento por país
    private $documentTypes = [
        'PE' => [ // Perú
            'dni' => 'DNI (Documento Nacional de Identidad)',
            'pasaporte' => 'Pasaporte'
        ],
        'MX' => [ // México
            'ine' => 'INE / Credencial para Votar',
            'pasaporte' => 'Pasaporte'
        ],
        'CL' => [ // Chile
            'cedula' => 'Cédula de Identidad',
            'pasaporte' => 'Pasaporte'
        ],
        'AR' => [ // Argentina
            'dni' => 'DNI (Documento Nacional de Identidad)',
            'pasaporte' => 'Pasaporte'
        ],
        'CO' => [ // Colombia
            'cedula' => 'Cédula de Ciudadanía',
            'pasaporte' => 'Pasaporte'
        ],
        'US' => [ // Estados Unidos
            'visa' => 'Visa',
            'passport' => 'Pasaporte Estadounidense'
        ],
        'ES' => [ // España
            'dni' => 'DNI / NIE',
            'pasaporte' => 'Pasaporte'
        ],
        'OTRO' => [ // Otros países
            'pasaporte' => 'Pasaporte',
            'visa' => 'Visa'
        ]
    ];

    public function showRegistrationForm()
    {
        $countries = $this->countries;
        $documentTypes = $this->getDocumentTypesForCountry(old('pais', 'PE'));

        return view('client.auth.register', compact('countries', 'documentTypes'));
    }

    public function register(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'nombres' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido_paterno' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido_materno' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'pais' => ['required', 'string', 'max:10'],
            'tipo_documento' => ['required', 'string', 'max:30'],
            'nro_documento' => ['required', 'string', 'max:30', 'unique:users,nro_documento'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'terminos' => ['required', 'accepted']
        ], [
            'nombres.required' => 'El campo nombres es obligatorio.',
            'nombres.regex' => 'El campo nombres solo puede contener letras y espacios.',
            'nombres.max' => 'El campo nombres no puede tener más de 255 caracteres.',

            'apellido_paterno.required' => 'El campo apellido paterno es obligatorio.',
            'apellido_paterno.regex' => 'El campo apellido paterno solo puede contener letras y espacios.',
            'apellido_paterno.max' => 'El campo apellido paterno no puede tener más de 255 caracteres.',

            'apellido_materno.required' => 'El campo apellido materno es obligatorio.',
            'apellido_materno.regex' => 'El campo apellido materno solo puede contener letras y espacios.',
            'apellido_materno.max' => 'El campo apellido materno no puede tener más de 255 caracteres.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido (ejemplo: usuario@dominio.com).',
            'email.unique' => 'Este correo electrónico ya está registrado. Por favor, utiliza otro o inicia sesión.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',

            'pais.required' => 'Debes seleccionar un país.',
            'tipo_documento.required' => 'Debes seleccionar un tipo de documento.',

            'nro_documento.required' => 'El número de documento es obligatorio.',
            'nro_documento.unique' => 'Este número de documento ya está registrado. Verifica que sea correcto.',
            'nro_documento.max' => 'El número de documento no puede tener más de 30 caracteres.',

            'telefono.regex' => 'El teléfono solo puede contener números, espacios y los caracteres + y -.',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden. Por favor, verifica.',

            'terminos.required' => 'Debes aceptar los términos y condiciones para registrarte.',
            'terminos.accepted' => 'Debes aceptar los términos y condiciones para continuar.'
        ]);

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
            $username = $this->generateUsername($request->nombres, $request->apellido_paterno, $request->apellido_materno);

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

    // Obtener países
    public function getCountries()
    {
        return $this->countries;
    }

    // Obtener tipos de documento según el país
    private function getDocumentTypesForCountry($countryCode)
    {
        if (isset($this->documentTypes[$countryCode])) {
            return $this->documentTypes[$countryCode];
        }
        return $this->documentTypes['OTRO'];
    }

    // Validar formato del documento según su tipo
    private function validateDocumentFormat($documentType, $documentNumber)
    {
        $documentNumber = strtoupper($documentNumber);

        switch ($documentType) {
            case 'dni':
                if (!preg_match('/^[0-9]{8}$/', $documentNumber)) {
                    return 'El DNI debe contener exactamente 8 dígitos numéricos.';
                }
                break;

            case 'cedula':
                if (!preg_match('/^[0-9]{7,12}$/', $documentNumber)) {
                    return 'La Cédula debe contener entre 7 y 12 dígitos numéricos.';
                }
                break;

            case 'ine':
                if (!preg_match('/^[A-Z0-9]{18}$/', $documentNumber)) {
                    return 'El INE debe tener 18 caracteres (letras mayúsculas y números).';
                }
                break;

            case 'pasaporte':
                if (!preg_match('/^[A-Z0-9]{6,12}$/', $documentNumber)) {
                    return 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).';
                }
                break;

            case 'visa':
                if (!preg_match('/^[A-Z0-9]{8,9}$/', $documentNumber)) {
                    return 'La Visa debe tener 8 o 9 caracteres (letras mayúsculas y números).';
                }
                break;

            case 'passport':
                if (!preg_match('/^[A-Z0-9]{6,12}$/', $documentNumber)) {
                    return 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).';
                }
                break;

            default:
                if (!preg_match('/^[A-Za-z0-9\s\-]{5,30}$/', $documentNumber)) {
                    return 'El formato del número de documento no es válido.';
                }
                break;
        }

        return true;
    }

    // Normalizar número de documento
    private function normalizeDocumentNumber($documentNumber)
    {
        return preg_replace('/[^A-Za-z0-9]/', '', strtoupper($documentNumber));
    }

    // Método AJAX para obtener tipos de documento
    public function getDocumentTypes($countryCode)
    {
        $types = $this->getDocumentTypesForCountry($countryCode);
        return response()->json($types);
    }

    private function generateUsername($nombres, $apellido_paterno, $apellido_materno = '')
    {
        $nombres = $this->removeAccents(trim($nombres));
        $apellido_paterno = $this->removeAccents(trim($apellido_paterno));
        $apellido_materno = $this->removeAccents(trim($apellido_materno));

        $primerNombre = explode(' ', $nombres)[0];
        $base = strtolower(substr($primerNombre, 0, 1) . $apellido_paterno);

        if (!empty($apellido_materno)) {
            $base .= substr($apellido_materno, 0, 1);
        }

        $base = preg_replace('/[^a-z0-9]/', '', $base);

        if (empty($base)) {
            $base = 'usuario';
        }

        $base = substr($base, 0, 15);
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;

            if ($counter > 1000) {
                $username = $base . time();
                break;
            }
        }

        return $username;
    }

    private function removeAccents($string)
    {
        $accents = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U'
        ];

        return strtr($string, $accents);
    }
}
