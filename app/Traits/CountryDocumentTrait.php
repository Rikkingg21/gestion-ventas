<?php

namespace App\Traits;

trait CountryDocumentTrait
{
    /**
     * Obtener lista de países
     */
    public function getCountries()
    {
        return [
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
    }

    /**
     * Obtener tipos de documento por país
     */
    public function getDocumentTypesByCountry()
    {
        return [
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
    }

    /**
     * Obtener tipos de documento para un país específico
     */
    public function getDocumentTypesForCountry($countryCode)
    {
        $documentTypes = $this->getDocumentTypesByCountry();

        if (isset($documentTypes[$countryCode])) {
            return $documentTypes[$countryCode];
        }

        return $documentTypes['OTRO'];
    }

    /**
     * Validar formato del documento según su tipo
     */
    public function validateDocumentFormat($documentType, $documentNumber)
    {
        $documentNumber = strtoupper($documentNumber);

        $formats = [
            'dni' => '/^[0-9]{8}$/',
            'cedula' => '/^[0-9]{7,12}$/',
            'ine' => '/^[A-Z0-9]{18}$/',
            'pasaporte' => '/^[A-Z0-9]{6,12}$/',
            'visa' => '/^[A-Z0-9]{8,9}$/',
            'passport' => '/^[A-Z0-9]{6,12}$/',
        ];

        $errors = [
            'dni' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
            'cedula' => 'La Cédula debe contener entre 7 y 12 dígitos numéricos.',
            'ine' => 'El INE debe tener 18 caracteres (letras mayúsculas y números).',
            'pasaporte' => 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).',
            'visa' => 'La Visa debe tener 8 o 9 caracteres (letras mayúsculas y números).',
            'passport' => 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).',
        ];

        if (isset($formats[$documentType])) {
            if (!preg_match($formats[$documentType], $documentNumber)) {
                return $errors[$documentType];
            }
        } else {
            if (!preg_match('/^[A-Za-z0-9\s\-]{5,30}$/', $documentNumber)) {
                return 'El formato del número de documento no es válido.';
            }
        }

        return true;
    }

    /**
     * Normalizar número de documento
     */
    public function normalizeDocumentNumber($documentNumber)
    {
        return preg_replace('/[^A-Za-z0-9]/', '', strtoupper($documentNumber));
    }

    /**
     * Generar username único
     */
    public function generateUsername($nombres, $apellido_paterno, $apellido_materno = '')
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

        while (\App\Models\User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;

            if ($counter > 1000) {
                $username = $base . time();
                break;
            }
        }

        return $username;
    }

    /**
     * Eliminar acentos de un string
     */
    public function removeAccents($string)
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

    /**
     * Obtener reglas de validación para el perfil/registro
     */
    public function getValidationRules($userId = null)
    {
        $uniqueEmail = 'unique:users,email';
        $uniqueDocument = 'unique:users,nro_documento';

        if ($userId) {
            $uniqueEmail = 'unique:users,email,' . $userId;
            $uniqueDocument = 'unique:users,nro_documento,' . $userId;
        }

        return [
            'nombres' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido_paterno' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido_materno' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', $uniqueEmail],
            'pais' => ['required', 'string', 'max:10'],
            'tipo_documento' => ['required', 'string', 'max:30'],
            'nro_documento' => ['required', 'string', 'max:30', $uniqueDocument],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
        ];
    }

    /**
     * Obtener mensajes de validación personalizados
     */
    public function getValidationMessages()
    {
        return [
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
        ];
    }
}
