<?php

namespace App\Helpers;

use App\Models\Moneda;
use App\Models\Producto;
use App\Models\User;
use App\Helpers\GeoLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MonedaHelper
{
    // Obtener la moneda actual (prioridad: usuario -> sesión -> país -> default)
public static function getMonedaActual()
{
    // 1. PRIORIDAD 1: Moneda seleccionada manualmente (SIEMPRE primero)
    if (session()->has('moneda_seleccionada')) {
        $codigoIso = session('moneda_seleccionada');
        $moneda = self::getMonedaByCodigo($codigoIso);

        if ($moneda && $moneda->is_active) {
            Log::info('Usando moneda seleccionada manualmente', [
                'codigo' => $codigoIso,
                'moneda_id' => $moneda->id
            ]);
            return $moneda;
        }
    }

    // 2. PRIORIDAD 2: Usuario logueado (solo si NO hay moneda seleccionada manualmente)
    if (Auth::guard('client')->check()) {
        $user = Auth::guard('client')->user();

        if (!empty($user->pais)) {
            $moneda = self::getMonedaByPais($user->pais);

            if ($moneda && $moneda->is_active) {
                Log::info('Usando moneda del usuario (sin selección manual)', [
                    'user_id' => $user->id,
                    'pais_code' => $user->pais,
                    'moneda' => $moneda->codigo_iso
                ]);
                session(['moneda_seleccionada' => $moneda->codigo_iso]);
                return $moneda;
            }
        }
    }

    // 3. PRIORIDAD 3: Moneda por país detectado (GeoLocation)
    try {
        $countryInfo = GeoLocation::getCountryInfo();
        $countryCode = $countryInfo['code'];

        if ($countryCode) {
            $moneda = self::getMonedaByPais($countryCode);

            if ($moneda) {
                Log::info('Usando moneda por GeoLocation', [
                    'pais_code' => $countryCode,
                    'moneda' => $moneda->codigo_iso
                ]);
                session(['moneda_seleccionada' => $moneda->codigo_iso]);
                return $moneda;
            }
        }
    } catch (\Exception $e) {
        // Silenciosamente ignoramos el error
    }

    // 4. PRIORIDAD 4: Moneda por defecto (USD)
    $monedaDefault = self::getMonedaDefault();

    if ($monedaDefault) {
        Log::info('Usando moneda por defecto', [
            'codigo' => $monedaDefault->codigo_iso
        ]);
        session(['moneda_seleccionada' => $monedaDefault->codigo_iso]);
        return $monedaDefault;
    }

    // 5. ÚLTIMO RECURSO: Moneda virtual USD
    Log::warning('No hay monedas en BD, usando USD virtual');
    $virtualMoneda = (object)[
        'id' => null,
        'codigo_iso' => 'USD',
        'simbolo' => '$',
        'nombre' => 'Dólar Americano',
        'pais' => 'United States',
        'pais_code' => 'US',
        'tasa_cambio_usd' => 1,
        'is_active' => true
    ];

    session(['moneda_seleccionada' => 'USD']);
    return $virtualMoneda;
}


    // Obtener moneda por código de país (pais_code)
    public static function getMonedaByPais($countryCode = null)
    {
        if (!$countryCode) {
            return null;
        }

        return Moneda::where('pais_code', $countryCode)
            ->where('is_active', true)
            ->first();
    }

    // Obtener todas las monedas activas
    public static function getMonedasActivas()
    {
        return Cache::remember('monedas_activas', now()->addDay(), function () {
            return Moneda::where('is_active', true)
                ->orderBy('codigo_iso')
                ->get();
        });
    }

    // Obtener moneda por código ISO
    public static function getMonedaByCodigo($codigoIso)
    {
        return Moneda::where('codigo_iso', $codigoIso)
            ->where('is_active', true)
            ->first();
    }

    // Obtener moneda por defecto (USD si existe, sino primera activa)
    public static function getMonedaDefault()
    {
        // Intentar obtener USD primero
        $moneda = self::getMonedaByCodigo('USD');

        // Si no hay USD, obtener la primera moneda activa
        if (!$moneda) {
            $moneda = self::getMonedasActivas()->first();
        }

        return $moneda;
    }

    // Obtener precio de un producto en una moneda específica
    public static function getPrecioProductoEnMoneda(Producto $producto, $monedaId = null)
    {
        // Si no hay moneda ID, obtener moneda actual
        if (!$monedaId) {
            $moneda = self::getMonedaActual();
            $monedaId = $moneda->id ?? null;
        } else {
            $moneda = Moneda::find($monedaId);
        }

        // Si no hay moneda válida, usar USD virtual
        if (!$moneda) {
            $moneda = (object)[
                'id' => null,
                'codigo_iso' => 'USD',
                'simbolo' => '$',
                'nombre' => 'Dólar Americano',
                'pais_code' => 'US',
                'tasa_cambio_usd' => 1
            ];
        }

        // Buscar precio fijo en la moneda solicitada
        $precioFijo = $producto->precios()
            ->where('moneda_id', $monedaId)
            ->where('is_active', true)
            ->first();

        // Buscar precio en USD (como moneda base para conversiones)
        $precioUSD = $producto->precios()
            ->whereHas('moneda', fn($q) => $q->where('codigo_iso', 'USD'))
            ->where('is_active', true)
            ->first();

        $tienePrecioFijo = !is_null($precioFijo);
        $tasaUsada = 1;
        $monedaBase = 'USD';

        if ($tienePrecioFijo) {
            // Usar precio fijo en la moneda solicitada
            $precioBase = $precioFijo->precio;
            $precioEnMonedaActual = $precioBase;
            $monedaBase = $moneda->codigo_iso;
        } elseif ($precioUSD) {
            // Convertir desde USD usando tasa de cambio
            $tasaUsada = $moneda->tasa_cambio_usd ?? 1;
            $precioEnMonedaActual = $precioUSD->precio * $tasaUsada;
            $monedaBase = 'USD';
        } else {
            // No hay precio definido
            $precioEnMonedaActual = 0;
        }

        // Calcular descuentos si aplican
        $precioOriginal = $precioEnMonedaActual;
        $precioConDescuento = $precioOriginal;
        $ahorro = 0;
        $porcentajeDescuento = 0;

        if ($producto->aplica_descuento && $producto->porcentaje_descuento > 0) {
            $porcentajeDescuento = $producto->porcentaje_descuento;
            $descuento = $porcentajeDescuento / 100;
            $precioConDescuento = $precioOriginal * (1 - $descuento);
            $ahorro = $precioOriginal - $precioConDescuento;
        }

        // Formatear precios
        $simbolo = $moneda->simbolo ?? '$';

        return [
            'moneda' => [
                'id' => $moneda->id,
                'codigo' => $moneda->codigo_iso ?? 'USD',
                'simbolo' => $simbolo,
                'nombre' => $moneda->nombre ?? 'Dólar Americano',
                'pais_code' => $moneda->pais_code ?? 'US',
                'tasa_cambio_usd' => $moneda->tasa_cambio_usd ?? 1
            ],
            'precio_original' => $precioOriginal,
            'precio_original_formateado' => $simbolo . ' ' . number_format($precioOriginal, 2),
            'precio_con_descuento' => $precioConDescuento,
            'precio_con_descuento_formateado' => $simbolo . ' ' . number_format($precioConDescuento, 2),
            'ahorro' => $ahorro,
            'ahorro_formateado' => $simbolo . ' ' . number_format($ahorro, 2),
            'porcentaje_descuento' => $porcentajeDescuento,
            'tiene_descuento' => $producto->aplica_descuento && $porcentajeDescuento > 0,

            // Metadatos útiles
            'tiene_precio_fijo' => $tienePrecioFijo,
            'tasa_usada' => $tasaUsada,
            'moneda_base' => $monedaBase,
            'tiene_precio_usd' => !is_null($precioUSD)
        ];
    }

    // Formatear array de moneda para la vista
    public static function formatMonedaForView($moneda)
    {
        if (!$moneda) {
            return null;
        }

        return [
            'id' => $moneda->id,
            'codigo' => $moneda->codigo_iso,
            'simbolo' => $moneda->simbolo,
            'nombre' => $moneda->nombre,
            'pais' => $moneda->pais,
            'pais_code' => $moneda->pais_code,
            'tasa_cambio_usd' => $moneda->tasa_cambio_usd
        ];
    }

    // Limpiar caché de monedas
    public static function clearCache()
    {
        Cache::forget('monedas_activas');
    }
}
