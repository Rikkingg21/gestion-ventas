<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeoLocation
{
    /**
     * Obtener información del país basado en la IP
     */
    public static function getCountryInfo($ip = null)
    {
        if (!$ip) {
            $ip = request()->ip();
        }

        // No intentar detectar para IPs locales
        if ($ip == '127.0.0.1' || $ip == '::1' || str_starts_with($ip, '192.168.')) {
            return self::getDefaultCountry();
        }

        // Usar caché para evitar muchas llamadas a la API
        return Cache::remember('geoip_' . $ip, now()->addDays(1), function () use ($ip) {
            try {
                // Usando ipapi.co (gratuito para uso básico)
                $response = Http::get("http://ip-api.com/json/{$ip}");

                if ($response->successful()) {
                    $data = $response->json();

                    if ($data['status'] == 'success') {
                        return [
                            'country' => $data['country'],
                            'country_code' => $data['countryCode'],
                            'currency' => self::getCurrencyFromCountry($data['countryCode']),
                            'flag' => strtolower($data['countryCode'])
                        ];
                    }
                }

                return self::getDefaultCountry();
            } catch (\Exception $e) {
                return self::getDefaultCountry();
            }
        });
    }

    /**
     * Obtener moneda basada en código de país
     */
    public static function getCurrencyFromCountry($countryCode)
    {
        $currencies = [
            'PE' => ['code' => 'PEN', 'symbol' => 'S/', 'name' => 'Sol Peruano'],
            'US' => ['code' => 'USD', 'symbol' => '$', 'name' => 'Dólar Americano'],
            'MX' => ['code' => 'MXN', 'symbol' => '$', 'name' => 'Peso Mexicano'],
            'CO' => ['code' => 'COP', 'symbol' => '$', 'name' => 'Peso Colombiano'],
            'CL' => ['code' => 'CLP', 'symbol' => '$', 'name' => 'Peso Chileno'],
            'AR' => ['code' => 'ARS', 'symbol' => '$', 'name' => 'Peso Argentino'],
            'BR' => ['code' => 'BRL', 'symbol' => 'R$', 'name' => 'Real Brasileño'],
            'EC' => ['code' => 'USD', 'symbol' => '$', 'name' => 'Dólar Americano'],
            'BO' => ['code' => 'BOB', 'symbol' => 'Bs', 'name' => 'Boliviano'],
            'PY' => ['code' => 'PYG', 'symbol' => '₲', 'name' => 'Guaraní'],
            'UY' => ['code' => 'UYU', 'symbol' => '$U', 'name' => 'Peso Uruguayo'],
            'VE' => ['code' => 'VES', 'symbol' => 'Bs.S', 'name' => 'Bolívar'],
            'ES' => ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
        ];

        return $currencies[$countryCode] ?? ['code' => 'USD', 'symbol' => '$', 'name' => 'Dólar Americano'];
    }

    /**
     * Configuración por defecto
     */
    public static function getDefaultCountry()
    {
        return [
            'country' => 'Perú',
            'country_code' => 'PE',
            'currency' => ['code' => 'PEN', 'symbol' => 'S/', 'name' => 'Sol Peruano'],
            'flag' => 'pe'
        ];
    }
}
