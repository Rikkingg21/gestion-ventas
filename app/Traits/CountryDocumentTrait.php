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
            // América (35 países)
            'PE' => 'Perú',
            'MX' => 'México',
            'CL' => 'Chile',
            'AR' => 'Argentina',
            'CO' => 'Colombia',
            'US' => 'Estados Unidos',
            'CA' => 'Canadá',
            'BR' => 'Brasil',
            'BO' => 'Bolivia',
            'UY' => 'Uruguay',
            'PY' => 'Paraguay',
            'VE' => 'Venezuela',
            'EC' => 'Ecuador',
            'CR' => 'Costa Rica',
            'PA' => 'Panamá',
            'GT' => 'Guatemala',
            'SV' => 'El Salvador',
            'HN' => 'Honduras',
            'NI' => 'Nicaragua',
            'DO' => 'República Dominicana',
            'CU' => 'Cuba',
            'PR' => 'Puerto Rico',
            'HT' => 'Haití',
            'JM' => 'Jamaica',
            'BS' => 'Bahamas',
            'BB' => 'Barbados',
            'TT' => 'Trinidad y Tobago',
            'GY' => 'Guyana',
            'SR' => 'Surinam',
            'BZ' => 'Belice',
            'LC' => 'Santa Lucía',
            'VC' => 'San Vicente y las Granadinas',
            'GD' => 'Granada',
            'AG' => 'Antigua y Barbuda',
            'DM' => 'Dominica',

            // Europa (44 países)
            'ES' => 'España',
            'FR' => 'Francia',
            'DE' => 'Alemania',
            'IT' => 'Italia',
            'GB' => 'Reino Unido',
            'PT' => 'Portugal',
            'NL' => 'Países Bajos',
            'BE' => 'Bélgica',
            'CH' => 'Suiza',
            'AT' => 'Austria',
            'SE' => 'Suecia',
            'NO' => 'Noruega',
            'DK' => 'Dinamarca',
            'FI' => 'Finlandia',
            'IE' => 'Irlanda',
            'PL' => 'Polonia',
            'CZ' => 'República Checa',
            'HU' => 'Hungría',
            'SK' => 'Eslovaquia',
            'SI' => 'Eslovenia',
            'HR' => 'Croacia',
            'RS' => 'Serbia',
            'BA' => 'Bosnia y Herzegovina',
            'ME' => 'Montenegro',
            'MK' => 'Macedonia del Norte',
            'AL' => 'Albania',
            'GR' => 'Grecia',
            'BG' => 'Bulgaria',
            'RO' => 'Rumania',
            'MD' => 'Moldavia',
            'UA' => 'Ucrania',
            'BY' => 'Bielorrusia',
            'LT' => 'Lituania',
            'LV' => 'Letonia',
            'EE' => 'Estonia',
            'RU' => 'Rusia',
            'GE' => 'Georgia',
            'AM' => 'Armenia',
            'AZ' => 'Azerbaiyán',
            'IS' => 'Islandia',
            'LU' => 'Luxemburgo',
            'MC' => 'Mónaco',
            'LI' => 'Liechtenstein',
            'MT' => 'Malta',

            // Asia (48 países)
            'CN' => 'China',
            'JP' => 'Japón',
            'KR' => 'Corea del Sur',
            'KP' => 'Corea del Norte',
            'IN' => 'India',
            'PK' => 'Pakistán',
            'BD' => 'Bangladesh',
            'ID' => 'Indonesia',
            'MY' => 'Malasia',
            'SG' => 'Singapur',
            'PH' => 'Filipinas',
            'VN' => 'Vietnam',
            'TH' => 'Tailandia',
            'MM' => 'Myanmar',
            'KH' => 'Camboya',
            'LA' => 'Laos',
            'NP' => 'Nepal',
            'BT' => 'Bután',
            'LK' => 'Sri Lanka',
            'MV' => 'Maldivas',
            'AF' => 'Afganistán',
            'IR' => 'Irán',
            'IQ' => 'Irak',
            'SY' => 'Siria',
            'LB' => 'Líbano',
            'JO' => 'Jordania',
            'IL' => 'Israel',
            'PS' => 'Palestina',
            'SA' => 'Arabia Saudita',
            'YE' => 'Yemen',
            'OM' => 'Omán',
            'AE' => 'Emiratos Árabes Unidos',
            'QA' => 'Qatar',
            'BH' => 'Baréin',
            'KW' => 'Kuwait',
            'TR' => 'Turquía',
            'CY' => 'Chipre',
            'KZ' => 'Kazajistán',
            'UZ' => 'Uzbekistán',
            'TM' => 'Turkmenistán',
            'KG' => 'Kirguistán',
            'TJ' => 'Tayikistán',
            'MN' => 'Mongolia',
            'TW' => 'Taiwán',
            'BN' => 'Brunéi',
            'TL' => 'Timor Oriental',
            'AM' => 'Armenia',
            'GE' => 'Georgia',

            // África (54 países)
            'ZA' => 'Sudáfrica',
            'EG' => 'Egipto',
            'MA' => 'Marruecos',
            'DZ' => 'Argelia',
            'TN' => 'Túnez',
            'LY' => 'Libia',
            'SD' => 'Sudán',
            'SS' => 'Sudán del Sur',
            'ER' => 'Eritrea',
            'DJ' => 'Yibuti',
            'SO' => 'Somalia',
            'ET' => 'Etiopía',
            'KE' => 'Kenia',
            'UG' => 'Uganda',
            'TZ' => 'Tanzania',
            'RW' => 'Ruanda',
            'BI' => 'Burundi',
            'CD' => 'República Democrática del Congo',
            'CG' => 'República del Congo',
            'CF' => 'República Centroafricana',
            'TD' => 'Chad',
            'CM' => 'Camerún',
            'NG' => 'Nigeria',
            'GH' => 'Ghana',
            'CI' => 'Costa de Marfil',
            'BF' => 'Burkina Faso',
            'ML' => 'Malí',
            'MR' => 'Mauritania',
            'NE' => 'Níger',
            'BJ' => 'Benín',
            'TG' => 'Togo',
            'SN' => 'Senegal',
            'GM' => 'Gambia',
            'GW' => 'Guinea-Bisáu',
            'GN' => 'Guinea',
            'SL' => 'Sierra Leona',
            'LR' => 'Liberia',
            'CV' => 'Cabo Verde',
            'ST' => 'Santo Tomé y Príncipe',
            'GQ' => 'Guinea Ecuatorial',
            'GA' => 'Gabón',
            'AO' => 'Angola',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabue',
            'MW' => 'Malawi',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'BW' => 'Botsuana',
            'LS' => 'Lesoto',
            'SZ' => 'Suazilandia',
            'MG' => 'Madagascar',
            'KM' => 'Comoras',
            'MU' => 'Mauricio',
            'SC' => 'Seychelles',

            // Oceanía (14 países)
            'AU' => 'Australia',
            'NZ' => 'Nueva Zelanda',
            'PG' => 'Papúa Nueva Guinea',
            'FJ' => 'Fiyi',
            'SB' => 'Islas Salomón',
            'VU' => 'Vanuatu',
            'WS' => 'Samoa',
            'TO' => 'Tonga',
            'KI' => 'Kiribati',
            'TV' => 'Tuvalu',
            'MH' => 'Islas Marshall',
            'FM' => 'Micronesia',
            'PW' => 'Palaos',
            'NR' => 'Nauru',

            'OTRO' => 'Otro'
        ];
    }

    /**
     * Obtener tipos de documento por país
     */
    public function getDocumentTypesByCountry()
    {
        return [
            // América
            'PE' => [ // Perú
                'dni' => 'DNI (Documento Nacional de Identidad) - 8 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'carnet_extranjeria' => 'Carnet de Extranjería - 9-12 dígitos'
            ],
            'MX' => [ // México
                'ine' => 'INE / Credencial para Votar - 18 caracteres',
                'pasaporte' => 'Pasaporte - 10-12 caracteres',
                'curp' => 'CURP - 18 caracteres alfanuméricos',
                'rfc' => 'RFC - 13 caracteres'
            ],
            'CL' => [ // Chile
                'rut' => 'RUT (Rol Único Tributario) - 8-9 dígitos + dígito verificador',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'AR' => [ // Argentina
                'dni' => 'DNI (Documento Nacional de Identidad) - 7-8 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'cuit' => 'CUIT/CUIL - 11 dígitos'
            ],
            'CO' => [ // Colombia
                'cedula' => 'Cédula de Ciudadanía - 7-10 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'nit' => 'NIT - 9-12 dígitos',
                'tarjeta_identidad' => 'Tarjeta de Identidad - 7-10 dígitos'
            ],
            'US' => [ // Estados Unidos
                'ssn' => 'Social Security Number - 9 dígitos (XXX-XX-XXXX)',
                'passport' => 'Pasaporte Estadounidense - 9 caracteres',
                'drivers_license' => 'Licencia de Conducir - 7-15 caracteres',
                'visa' => 'Visa - 8-9 caracteres'
            ],
            'CA' => [ // Canadá
                'sin' => 'SIN (Social Insurance Number) - 9 dígitos',
                'passport' => 'Pasaporte Canadiense - 8-10 caracteres',
                'drivers_license' => 'Licencia de Conducir - 7-15 caracteres'
            ],
            'BR' => [ // Brasil
                'cpf' => 'CPF - 11 dígitos',
                'rg' => 'RG (Registro Geral) - 9-12 dígitos',
                'passport' => 'Pasaporte - 6-12 caracteres',
                'cnh' => 'CNH (Licencia Nacional) - 11 dígitos'
            ],
            'BO' => [ // Bolivia
                'ci' => 'Cédula de Identidad - 7-10 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'UY' => [ // Uruguay
                'ci' => 'Cédula de Identidad - 7-8 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'rut' => 'RUT - 12 dígitos'
            ],
            'PY' => [ // Paraguay
                'ci' => 'Cédula de Identidad - 7-10 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'VE' => [ // Venezuela
                'ci' => 'Cédula de Identidad - 6-8 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'EC' => [ // Ecuador
                'ci' => 'Cédula de Identidad - 10 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'CR' => [ // Costa Rica
                'cedula' => 'Cédula de Identidad - 9 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'dimex' => 'DIMEX (Documento de Identidad para Extranjeros) - 11-12 dígitos'
            ],
            'PA' => [ // Panamá
                'cedula' => 'Cédula de Identidad - 7-8 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'GT' => [ // Guatemala
                'dpi' => 'DPI (Documento Personal de Identificación) - 13 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'SV' => [ // El Salvador
                'dui' => 'DUI (Documento Único de Identidad) - 8-9 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'nit' => 'NIT - 14 dígitos'
            ],
            'HN' => [ // Honduras
                'dni' => 'DNI (Documento Nacional de Identidad) - 13 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'rtn' => 'RTN - 14 dígitos'
            ],
            'NI' => [ // Nicaragua
                'cedula' => 'Cédula de Identidad - 9-10 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'DO' => [ // República Dominicana
                'cedula' => 'Cédula de Identidad - 11 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'rnc' => 'RNC - 9 dígitos'
            ],
            'CU' => [ // Cuba
                'ci' => 'Carné de Identidad - 11 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'HT' => [ // Haití
                'nin' => 'NIN (National Identification Number) - 8-10 dígitos',
                'pasaporte' => 'Pasaporte - 6-12 caracteres'
            ],
            'JM' => [ // Jamaica
                'trn' => 'TRN (Taxpayer Registration Number) - 9 dígitos',
                'passport' => 'Pasaporte - 6-12 caracteres'
            ],

            // Europa
            'ES' => [ // España
                'dni' => 'DNI / NIE - 8 dígitos + letra o 9 caracteres',
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'cif' => 'CIF - 9 caracteres'
            ],
            'FR' => [ // Francia
                'cni' => 'CNI (Carte Nationale d\'Identité) - 12-15 caracteres',
                'passport' => 'Pasaporte - 8-10 caracteres',
                'nir' => 'NIR (Numéro d\'Inscription au Répertoire) - 15 dígitos'
            ],
            'DE' => [ // Alemania
                'perso' => 'Personalausweis - 9-11 caracteres',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'tax_id' => 'Steueridentifikationsnummer - 11 dígitos'
            ],
            'IT' => [ // Italia
                'ci' => 'Carta d\'Identità - 7-9 caracteres',
                'passport' => 'Pasaporte - 9 caracteres',
                'codice_fiscale' => 'Codice Fiscale - 16 caracteres'
            ],
            'GB' => [ // Reino Unido
                'ni' => 'National Insurance Number - 9 caracteres',
                'passport' => 'Pasaporte - 9 caracteres',
                'drivers_license' => 'Licencia de Conducir - 16 caracteres'
            ],
            'PT' => [ // Portugal
                'cc' => 'Cartão de Cidadão - 12 dígitos',
                'passport' => 'Pasaporte - 8-10 caracteres',
                'nif' => 'NIF (Número de Identificação Fiscal) - 9 dígitos'
            ],
            'NL' => [ // Países Bajos
                'bsn' => 'BSN (Burgerservicenummer) - 9 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'drivers_license' => 'Licencia de Conducir - 10 dígitos'
            ],
            'BE' => [ // Bélgica
                'rn' => 'Registre National - 11 dígitos',
                'passport' => 'Pasaporte - 8-10 caracteres',
                'bis' => 'BIS (Tarjeta de identidad) - 8-12 caracteres'
            ],
            'CH' => [ // Suiza
                'ahv' => 'AHV/AVS Number - 13 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'drivers_license' => 'Licencia de Conducir - 12 caracteres'
            ],
            'AT' => [ // Austria
                'abv' => 'Abweisung - 10 dígitos',
                'passport' => 'Pasaporte - 9 caracteres',
                'svnr' => 'SVNR (Social Security) - 10 dígitos'
            ],
            'SE' => [ // Suecia
                'pn' => 'Personnummer - 10-12 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'samordning' => 'Samordningsnummer - 12 dígitos'
            ],
            'NO' => [ // Noruega
                'fnr' => 'Fødselsnummer - 11 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'd_number' => 'D-nummer - 11 dígitos'
            ],
            'DK' => [ // Dinamarca
                'cpr' => 'CPR (Det Centrale Personregister) - 10 dígitos',
                'passport' => 'Pasaporte - 9 caracteres'
            ],
            'FI' => [ // Finlandia
                'hetu' => 'Henkilötunnus - 11 caracteres',
                'passport' => 'Pasaporte - 9 caracteres'
            ],
            'IE' => [ // Irlanda
                'pps' => 'PPS Number - 8-9 caracteres',
                'passport' => 'Pasaporte - 9 caracteres'
            ],
            'PL' => [ // Polonia
                'pesel' => 'PESEL - 11 dígitos',
                'passport' => 'Pasaporte - 9 caracteres',
                'dowod' => 'Dowód Osobisty - 9 caracteres'
            ],
            'RU' => [ // Rusia
                'passport' => 'Pasaporte Interno - 10 dígitos',
                'foreign_passport' => 'Pasaporte Internacional - 9 caracteres',
                'inn' => 'INN (ИНН) - 10-12 dígitos',
                'snils' => 'SNILS (СНИЛС) - 11 dígitos'
            ],

            // Asia
            'CN' => [ // China
                'id_card' => 'Tarjeta de Identidad - 18 dígitos',
                'passport' => 'Pasaporte - 9 caracteres',
                'residence_permit' => 'Permiso de Residencia - 15 dígitos'
            ],
            'JP' => [ // Japón
                'my_number' => 'My Number - 12 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'drivers_license' => 'Licencia de Conducir - 12 dígitos'
            ],
            'KR' => [ // Corea del Sur
                'rrn' => 'Resident Registration Number - 13 dígitos',
                'passport' => 'Pasaporte - 9 caracteres',
                'drivers_license' => 'Licencia de Conducir - 12 dígitos'
            ],
            'IN' => [ // India
                'aadhaar' => 'Aadhaar - 12 dígitos',
                'pan' => 'PAN Card - 10 caracteres alfanuméricos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'voter_id' => 'Voter ID - 10 caracteres alfanuméricos'
            ],
            'ID' => [ // Indonesia
                'nik' => 'NIK (Nomor Induk Kependudukan) - 16 dígitos',
                'passport' => 'Pasaporte - 9 caracteres'
            ],
            'MY' => [ // Malasia
                'ic' => 'MyKad - 12 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'army_id' => 'ID Militar - 8-12 caracteres'
            ],
            'SG' => [ // Singapur
                'nric' => 'NRIC - 9 caracteres (S/T + 7 dígitos + letra)',
                'fin' => 'FIN (Foreign Identification Number) - 9 caracteres',
                'passport' => 'Pasaporte - 9 caracteres'
            ],
            'PH' => [ // Filipinas
                'psn' => 'PSN (PhilSys Number) - 12 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'tin' => 'TIN - 9-12 dígitos',
                'umid' => 'UMID - 12-14 dígitos'
            ],
            'VN' => [ // Vietnam
                'id_card' => 'Tarjeta de Identidad - 9-12 dígitos',
                'passport' => 'Pasaporte - 8-10 caracteres',
                'citizen_id' => 'Citizen ID - 12 dígitos'
            ],
            'TH' => [ // Tailandia
                'pid' => 'Personal ID Number - 13 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres'
            ],
            'SA' => [ // Arabia Saudita
                'national_id' => 'National ID - 10 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'iqama' => 'Iqama (Residencia) - 10 dígitos'
            ],
            'IL' => [ // Israel
                'tz' => 'Teudat Zehut - 9 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'drivers_license' => 'Licencia de Conducir - 8-9 dígitos'
            ],
            'TR' => [ // Turquía
                'tckn' => 'TCKN (Turkish ID Number) - 11 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'foreigner_id' => 'Foreigner ID - 11 dígitos'
            ],

            // África
            'ZA' => [ // Sudáfrica
                'id' => 'South African ID - 13 dígitos',
                'passport' => 'Pasaporte - 9 caracteres',
                'tax_ref' => 'Tax Reference Number - 10 dígitos'
            ],
            'EG' => [ // Egipto
                'national_id' => 'National ID - 14 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres'
            ],
            'MA' => [ // Marruecos
                'cin' => 'CIN (Carte d\'Identité Nationale) - 8-10 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'code_fiscal' => 'Código Fiscal - 10-12 dígitos'
            ],
            'NG' => [ // Nigeria
                'nin' => 'NIN (National Identification Number) - 11 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'voter_id' => 'Voter ID - 12-14 caracteres'
            ],
            'KE' => [ // Kenia
                'national_id' => 'National ID - 8 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'pin' => 'KRA PIN - 11 caracteres'
            ],

            // Oceanía
            'AU' => [ // Australia
                'tf' => 'Tax File Number - 9 dígitos',
                'passport' => 'Pasaporte - 8-9 caracteres',
                'medicare' => 'Medicare Number - 10 dígitos',
                'drivers_license' => 'Licencia de Conducir - 9-10 caracteres'
            ],
            'NZ' => [ // Nueva Zelanda
                'ird' => 'IRD Number - 8-9 dígitos',
                'passport' => 'Pasaporte - 9-10 caracteres',
                'nhc' => 'NHS Number - 10-12 caracteres'
            ],

            'OTRO' => [ // Otros países
                'pasaporte' => 'Pasaporte - 6-12 caracteres',
                'visa' => 'Visa - 8-9 caracteres',
                'id_card' => 'Tarjeta de Identidad - 6-15 caracteres'
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
        $documentNumber = strtoupper(trim($documentNumber));

        $formats = [
            // América
            'dni' => '/^[0-9]{7,8}$/',
            'cedula' => '/^[0-9]{7,12}$/',
            'ine' => '/^[A-Z0-9]{18}$/',
            'curp' => '/^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}$/',
            'rfc' => '/^[A-Z]{4}[0-9]{6}[A-Z0-9]{3}$/',
            'rut' => '/^[0-9]{7,9}[0-9Kk]{1}$/',
            'cuit' => '/^[0-9]{11}$/',
            'nit' => '/^[0-9]{9,12}$/',
            'ssn' => '/^[0-9]{9}$/',
            'sin' => '/^[0-9]{9}$/',
            'cpf' => '/^[0-9]{11}$/',
            'rg' => '/^[0-9]{9,12}$/',
            'cnh' => '/^[0-9]{11}$/',
            'ci' => '/^[0-9]{6,10}$/',
            'dpi' => '/^[0-9]{13}$/',
            'dui' => '/^[0-9]{8,9}$/',
            'dimex' => '/^[0-9]{11,12}$/',
            'tarjeta_identidad' => '/^[0-9]{7,10}$/',
            'carnet_extranjeria' => '/^[0-9]{9,12}$/',
            'nin' => '/^[0-9]{8,12}$/',
            'trn' => '/^[0-9]{9}$/',

            // Europa
            'nie' => '/^[XYZ][0-9]{7}[A-Z]$/',
            'cif' => '/^[A-Z][0-9]{8}$/',
            'cni' => '/^[0-9]{12,15}$/',
            'nir' => '/^[0-9]{15}$/',
            'perso' => '/^[A-Z0-9]{9,11}$/',
            'tax_id' => '/^[0-9]{11}$/',
            'codice_fiscale' => '/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/',
            'ni' => '/^[A-Z]{2}[0-9]{6}[A-Z]$/',
            'cc' => '/^[0-9]{12}$/',
            'nif' => '/^[0-9]{9}$/',
            'bsn' => '/^[0-9]{9}$/',
            'rn' => '/^[0-9]{11}$/',
            'ahv' => '/^[0-9]{13}$/',
            'svnr' => '/^[0-9]{10}$/',
            'pn' => '/^[0-9]{10,12}$/',
            'fnr' => '/^[0-9]{11}$/',
            'cpr' => '/^[0-9]{10}$/',
            'hetu' => '/^[0-9]{6}[+-A][0-9]{3}[0-9A-Z]$/',
            'pps' => '/^[0-9]{7}[A-Z]{1,2}$/',
            'pesel' => '/^[0-9]{11}$/',
            'dowod' => '/^[A-Z0-9]{9}$/',
            'inn' => '/^[0-9]{10,12}$/',
            'snils' => '/^[0-9]{11}$/',

            // Asia
            'my_number' => '/^[0-9]{12}$/',
            'rrn' => '/^[0-9]{13}$/',
            'aadhaar' => '/^[0-9]{12}$/',
            'pan' => '/^[A-Z]{5}[0-9]{4}[A-Z]$/',
            'nik' => '/^[0-9]{16}$/',
            'ic' => '/^[0-9]{12}$/',
            'nric' => '/^[STFG][0-9]{7}[A-Z]$/',
            'fin' => '/^[FGM][0-9]{7}[A-Z]$/',
            'psn' => '/^[0-9]{12}$/',
            'tin' => '/^[0-9]{9,12}$/',
            'umid' => '/^[0-9]{12,14}$/',
            'pid' => '/^[0-9]{13}$/',
            'national_id' => '/^[0-9]{10}$/',
            'iqama' => '/^[0-9]{10}$/',
            'tz' => '/^[0-9]{9}$/',
            'tckn' => '/^[0-9]{11}$/',
            'foreigner_id' => '/^[0-9]{11}$/',
            'citizen_id' => '/^[0-9]{12}$/',
            'voter_id' => '/^[A-Z0-9]{10,14}$/',

            // África
            'code_fiscal' => '/^[0-9]{10,12}$/',
            'kra_pin' => '/^[A-Z]{2}[0-9]{9}[A-Z]$/',

            // Oceanía
            'tf' => '/^[0-9]{9}$/',
            'medicare' => '/^[0-9]{10}$/',
            'ird' => '/^[0-9]{8,9}$/',
            'nhc' => '/^[A-Z0-9]{10,12}$/',

            // Generales
            'pasaporte' => '/^[A-Z0-9]{6,12}$/',
            'visa' => '/^[A-Z0-9]{8,9}$/',
            'passport' => '/^[A-Z0-9]{6,12}$/',
            'id_card' => '/^[A-Z0-9]{6,15}$/',
            'drivers_license' => '/^[A-Z0-9]{7,15}$/',
            'residence_permit' => '/^[0-9]{15}$/',
            'army_id' => '/^[A-Z0-9]{8,12}$/',
        ];

        $errors = [
            // América
            'dni' => 'El DNI debe contener entre 7 y 8 dígitos numéricos.',
            'cedula' => 'La Cédula debe contener entre 7 y 12 dígitos numéricos.',
            'ine' => 'El INE debe tener 18 caracteres (letras mayúsculas y números).',
            'curp' => 'El CURP debe tener 18 caracteres con formato específico.',
            'rfc' => 'El RFC debe tener 13 caracteres con formato específico.',
            'rut' => 'El RUT debe tener 8-9 dígitos más dígito verificador.',
            'cuit' => 'El CUIT/CUIL debe tener exactamente 11 dígitos.',
            'nit' => 'El NIT debe tener entre 9 y 12 dígitos numéricos.',
            'ssn' => 'El SSN debe tener exactamente 9 dígitos.',
            'sin' => 'El SIN debe tener exactamente 9 dígitos.',
            'cpf' => 'El CPF debe tener exactamente 11 dígitos.',
            'rg' => 'El RG debe tener entre 9 y 12 dígitos numéricos.',
            'cnh' => 'La CNH debe tener exactamente 11 dígitos.',
            'ci' => 'La Cédula debe tener entre 6 y 10 dígitos numéricos.',
            'dpi' => 'El DPI debe tener exactamente 13 dígitos.',
            'dui' => 'El DUI debe tener 8 o 9 dígitos numéricos.',
            'dimex' => 'El DIMEX debe tener 11 o 12 dígitos.',
            'tarjeta_identidad' => 'La Tarjeta de Identidad debe tener entre 7 y 10 dígitos.',
            'carnet_extranjeria' => 'El Carnet de Extranjería debe tener entre 9 y 12 dígitos.',
            'nin' => 'El NIN debe tener entre 8 y 12 dígitos.',
            'trn' => 'El TRN debe tener exactamente 9 dígitos.',

            // Europa
            'nie' => 'El NIE debe tener formato válido (X/Y/Z + 7 dígitos + letra).',
            'cif' => 'El CIF debe tener letra + 8 dígitos.',
            'cni' => 'La CNI debe tener entre 12 y 15 dígitos.',
            'nir' => 'El NIR debe tener exactamente 15 dígitos.',
            'perso' => 'El Personalausweis debe tener entre 9 y 11 caracteres.',
            'tax_id' => 'El Tax ID debe tener exactamente 11 dígitos.',
            'codice_fiscale' => 'El Codice Fiscale debe tener formato válido de 16 caracteres.',
            'ni' => 'El NI debe tener formato: 2 letras + 6 dígitos + letra.',
            'cc' => 'El Cartão de Cidadão debe tener 12 dígitos.',
            'nif' => 'El NIF debe tener exactamente 9 dígitos.',
            'bsn' => 'El BSN debe tener exactamente 9 dígitos.',
            'rn' => 'El Registre National debe tener 11 dígitos.',
            'ahv' => 'El AHV debe tener exactamente 13 dígitos.',
            'svnr' => 'El SVNR debe tener exactamente 10 dígitos.',
            'pn' => 'El Personnummer debe tener 10-12 dígitos.',
            'fnr' => 'El Fødselsnummer debe tener exactamente 11 dígitos.',
            'cpr' => 'El CPR debe tener exactamente 10 dígitos.',
            'hetu' => 'El Henkilötunnus debe tener formato válido de 11 caracteres.',
            'pps' => 'El PPS debe tener 7 dígitos + 1-2 letras.',
            'pesel' => 'El PESEL debe tener exactamente 11 dígitos.',
            'dowod' => 'El Dowód Osobisty debe tener 9 caracteres.',
            'inn' => 'El INN debe tener 10 o 12 dígitos.',
            'snils' => 'El SNILS debe tener exactamente 11 dígitos.',

            // Asia
            'my_number' => 'El My Number debe tener exactamente 12 dígitos.',
            'rrn' => 'El RRN debe tener exactamente 13 dígitos.',
            'aadhaar' => 'El Aadhaar debe tener exactamente 12 dígitos.',
            'pan' => 'El PAN debe tener 5 letras + 4 dígitos + letra.',
            'nik' => 'El NIK debe tener exactamente 16 dígitos.',
            'ic' => 'El MyKad debe tener exactamente 12 dígitos.',
            'nric' => 'El NRIC debe tener formato: S/T/F/G + 7 dígitos + letra.',
            'fin' => 'El FIN debe tener formato: F/G/M + 7 dígitos + letra.',
            'psn' => 'El PSN debe tener exactamente 12 dígitos.',
            'tin' => 'El TIN debe tener entre 9 y 12 dígitos.',
            'umid' => 'El UMID debe tener entre 12 y 14 dígitos.',
            'pid' => 'El Personal ID debe tener exactamente 13 dígitos.',
            'national_id' => 'El National ID debe tener exactamente 10 dígitos.',
            'iqama' => 'El Iqama debe tener exactamente 10 dígitos.',
            'tz' => 'El Teudat Zehut debe tener exactamente 9 dígitos.',
            'tckn' => 'El TCKN debe tener exactamente 11 dígitos.',
            'foreigner_id' => 'El Foreigner ID debe tener exactamente 11 dígitos.',
            'citizen_id' => 'El Citizen ID debe tener exactamente 12 dígitos.',
            'voter_id' => 'El Voter ID debe tener entre 10 y 14 caracteres.',

            // África
            'code_fiscal' => 'El Código Fiscal debe tener entre 10 y 12 dígitos.',
            'kra_pin' => 'El KRA PIN debe tener formato: 2 letras + 9 dígitos + letra.',

            // Oceanía
            'tf' => 'El Tax File Number debe tener exactamente 9 dígitos.',
            'medicare' => 'El Medicare Number debe tener exactamente 10 dígitos.',
            'ird' => 'El IRD debe tener 8 o 9 dígitos.',
            'nhc' => 'El NHC debe tener entre 10 y 12 caracteres.',

            // Generales
            'pasaporte' => 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).',
            'visa' => 'La Visa debe tener 8 o 9 caracteres (letras mayúsculas y números).',
            'passport' => 'El Pasaporte debe tener entre 6 y 12 caracteres (letras mayúsculas y números).',
            'id_card' => 'La Tarjeta de Identidad debe tener entre 6 y 15 caracteres.',
            'drivers_license' => 'La Licencia de Conducir debe tener entre 7 y 15 caracteres.',
            'residence_permit' => 'El Permiso de Residencia debe tener exactamente 15 dígitos.',
            'army_id' => 'El ID Militar debe tener entre 8 y 12 caracteres.',
        ];

        if (isset($formats[$documentType])) {
            if (!preg_match($formats[$documentType], $documentNumber)) {
                return $errors[$documentType] ?? 'El formato del número de documento no es válido.';
            }
        } else {
            // Formato genérico para tipos no definidos
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
        return preg_replace('/[^A-Za-z0-9]/', '', strtoupper(trim($documentNumber)));
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
