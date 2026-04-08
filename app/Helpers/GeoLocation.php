<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoLocation
{
    /**
     * Obtener IP real para geolocalización
     */
    protected static function getIpForGeoLocation($ip = null)
    {
        $ip = $ip ?? request()->ip();

        // SOLO PARA DESARROLLO - Comentar en producción
        if (app()->environment('local')) {
            // Usar una IP pública conocida (ej: Google DNS, Cloudflare, etc)
            //$testIp = '190.12.74.242'; // IP de UGELT
            //$testIp = '102.217.238.255'; // IP de Colombia
            //$testIp = '1.178.29.255'; //ip MExico
            //$testIp = '1.0.3.255'; //ip China
            //$testIp = '8.8.8.8'; // IP de Googl
            //$testIp = '80.66.14.38'; //Alemania
            $testIp = '1.178.47.255'; // Brasil
            // $testIp = '1.1.1.1'; // IP de Cloudflare
            // $testIp = '208.67.222.222'; // IP de OpenDNS
            /*
            Log::info('MODO DESARROLLO: Usando IP de prueba', [
                'original_ip' => $ip,
                'test_ip' => $testIp
            ]);
            */

            return $testIp;
        }

        // Código normal para producción...
        if (self::isLocalIp($ip)) {
            $publicIp = self::getPublicIp();
            if ($publicIp) {
                return $publicIp;
            }
            throw new \Exception('No se puede determinar ubicación en entorno local sin IP pública');
        }

        return $ip;
    }

    // Obtener información de ubicación basada en IP
    public static function getLocationInfo($ip = null)
    {
        try {
            $ipForGeo = self::getIpForGeoLocation($ip);

            return Cache::remember('geo_location_' . $ipForGeo, now()->addDay(), function () use ($ipForGeo) {
                // Intentar con ip-api.com
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipForGeo}?fields=status,country,countryCode,region,city,isp,query");

                if ($response->successful() && $response->json('status') === 'success') {
                    $data = $response->json();
                    return $data;
                }

                throw new \Exception('No se pudo obtener geolocalización de ip-api.com');
            });

        } catch (\Exception $e) {
            Log::error('Error en geolocalización: ' . $e->getMessage());
            throw $e; // Relanzamos la excepción para que el composer sepa que falló
        }
    }

    // Obtener país desde IP
    public static function getCountry($ip = null)
    {
        $info = self::getLocationInfo($ip);
        return $info['country'];
    }

    // Obtener código de país (ISO) desde IP
    public static function getCountryCode($ip = null)
    {
        $info = self::getLocationInfo($ip);
        return $info['countryCode'];
    }

    // Obtener IP pública del servidor
    protected static function getPublicIp()
    {
        // Intentar con varios servicios
        $services = [
            'https://api.ipify.org',
            'https://icanhazip.com',
            'https://ifconfig.me/ip'
        ];

        foreach ($services as $service) {
            try {
                $response = Http::timeout(3)->get($service);
                if ($response->successful()) {
                    $ip = trim($response->body());
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    // Verificar si es IP local
    protected static function isLocalIp($ip)
    {
        $localIps = [
            '127.0.0.1',
            '::1',
            'localhost',
            '0.0.0.0'
        ];

        if (in_array($ip, $localIps)) {
            return true;
        }

        // Rangos de IPs privadas
        if (strpos($ip, '192.168.') === 0) return true;
        if (strpos($ip, '10.') === 0) return true;
        if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) return true;

        return false;
    }

    // Verificar si la IP es de un país específico
    public static function isCountry($countries, $ip = null)
    {
        $countryCode = self::getCountryCode($ip);
        $countries = is_array($countries) ? $countries : [$countries];
        return in_array($countryCode, $countries);
    }

    public static function getCountryInfo($ip = null)
    {
        try {
            $info = self::getLocationInfo($ip);
            return [
                'code' => $info['countryCode'] ?? null,
                'name' => $info['country'] ?? null,
                'success' => true
            ];
        } catch (\Exception $e) {
            Log::warning('No se pudo determinar país: ' . $e->getMessage());
            return [
                'code' => null,
                'name' => null,
                'success' => false
            ];
        }
    }
}
