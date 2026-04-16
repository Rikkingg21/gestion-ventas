<?php

return [
    'countries' => [
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
    ],

    'document_types' => [
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
    ],

    'document_formats' => [
        'dni' => '/^[0-9]{8}$/',
        'cedula' => '/^[0-9]{7,12}$/',
        'ine' => '/^[A-Z0-9]{18}$/',
        'pasaporte' => '/^[A-Z0-9]{6,12}$/',
        'visa' => '/^[A-Z0-9]{8,9}$/',
        'passport' => '/^[A-Z0-9]{6,12}$/',
        'default' => '/^[A-Za-z0-9\s\-]{5,30}$/'
    ],

    'document_errors' => [
        'dni' => 'El DNI debe contener exactamente 8 dígitos numéricos.',
        'cedula' => 'La Cédula debe contener entre 7 y 12 dígitos numéricos.',
        'ine' => 'El INE debe tener 18 caracteres (letras mayúsculas y números).',
        'pasaporte' => 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).',
        'visa' => 'La Visa debe tener 8 o 9 caracteres (letras mayúsculas y números).',
        'passport' => 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).',
        'default' => 'El formato del número de documento no es válido.'
    ]
];
