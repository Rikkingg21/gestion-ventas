<?php

namespace App\View\Composers;

use App\Models\Module;
use App\Models\Moneda;
use App\Helpers\GeoLocation;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ClienteMenuComposer
{
    public function compose(View $view)
    {
        try {
            // Verificar autenticación con guard client
            $user = Auth::guard('client')->user();
            $isAuthenticated = Auth::guard('client')->check();
        } catch (\Exception $e) {
            $user = null;
            $isAuthenticated = false;
            Log::warning('Error al verificar autenticación: ' . $e->getMessage());
        }

        // GEO LOCALIZACIÓN - DETECCIÓN DE PAÍS Y MONEDA
        try {
            // Intentar obtener de caché primero
            $geoInfo = Cache::remember('geo_' . request()->ip(), now()->addHours(6), function () {
                return GeoLocation::getCountryInfo();
            });

            // Obtener moneda de la base de datos basada en el código detectado
            $monedaDetectada = Moneda::where('codigo_iso', $geoInfo['currency']['code'])
                ->where('is_active', true)
                ->first();

            // Si hay moneda preferida en sesión (seleccionada por el usuario), usarla
            if (session()->has('moneda_seleccionada')) {
                $monedaUsuario = Moneda::where('codigo_iso', session('moneda_seleccionada'))
                    ->where('is_active', true)
                    ->first();

                if ($monedaUsuario) {
                    $geoInfo['currency'] = [
                        'code' => $monedaUsuario->codigo_iso,
                        'symbol' => $monedaUsuario->simbolo,
                        'name' => $monedaUsuario->nombre,
                        'id' => $monedaUsuario->id
                    ];
                    $geoInfo['moneda_seleccionada'] = true;
                }
            }
            // Si no hay moneda seleccionada pero tenemos moneda detectada en BD
            elseif ($monedaDetectada) {
                $geoInfo['currency'] = [
                    'code' => $monedaDetectada->codigo_iso,
                    'symbol' => $monedaDetectada->simbolo,
                    'name' => $monedaDetectada->nombre,
                    'id' => $monedaDetectada->id
                ];
                $geoInfo['moneda_detectada'] = true;
            }

            // Obtener todas las monedas activas para el selector
            $monedasDisponibles = Moneda::where('is_active', true)
                ->orderBy('codigo_iso')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error en geolocalización: ' . $e->getMessage());
            $geoInfo = GeoLocation::getDefaultCountry();
            $monedasDisponibles = collect([]);
        }

        // Si el usuario NO está autenticado, mostrar solo módulos públicos
        if (!$isAuthenticated || !$user) {
            $publicModules = $this->getPublicModules();

            return $view->with([
                'menuModules' => collect([]),
                'publicModules' => $publicModules,
                'isAuthenticated' => false,
                'currentUser' => null,
                // Datos de geolocalización
                'userGeoInfo' => $geoInfo,
                'monedasDisponibles' => $monedasDisponibles
            ]);
        }

        // Usuario autenticado - verificar si tiene relación con client
        try {
            $cliente = $user->client;

            if (!$cliente) {
                Log::info('Usuario autenticado pero no es cliente: ' . $user->id);

                return $view->with([
                    'menuModules' => collect([]),
                    'publicModules' => $this->getPublicModules(),
                    'isAuthenticated' => true,
                    'currentUser' => $user,
                    // Datos de geolocalización
                    'userGeoInfo' => $geoInfo,
                    'monedasDisponibles' => $monedasDisponibles
                ]);
            }

            // Verificar si el cliente está activo
            if (!$cliente->is_active) {
                Log::info('Cliente inactivo: ' . $user->id);

                return $view->with([
                    'menuModules' => collect([]),
                    'publicModules' => $this->getPublicModules(),
                    'isAuthenticated' => true,
                    'currentUser' => $user,
                    // Datos de geolocalización
                    'userGeoInfo' => $geoInfo,
                    'monedasDisponibles' => $monedasDisponibles
                ]);
            }

            // Si el cliente tiene moneda preferida en su perfil, usarla
            if ($cliente->moneda_preferida && !session()->has('moneda_seleccionada')) {
                $monedaPreferida = Moneda::find($cliente->moneda_preferida);
                if ($monedaPreferida) {
                    $geoInfo['currency'] = [
                        'code' => $monedaPreferida->codigo_iso,
                        'symbol' => $monedaPreferida->simbolo,
                        'name' => $monedaPreferida->nombre,
                        'id' => $monedaPreferida->id
                    ];
                    $geoInfo['moneda_preferida'] = true;
                }
            }

            // Obtener módulos públicos
            $publicModules = $this->getPublicModules();

            return $view->with([
                'menuModules' => collect([]),
                'publicModules' => $publicModules,
                'isAuthenticated' => true,
                'currentUser' => $user,
                // Datos de geolocalización
                'userGeoInfo' => $geoInfo,
                'monedasDisponibles' => $monedasDisponibles
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener datos del cliente: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return $view->with([
                'menuModules' => collect([]),
                'publicModules' => $this->getPublicModules(),
                'isAuthenticated' => true,
                'currentUser' => $user,
                // Datos de geolocalización
                'userGeoInfo' => $geoInfo,
                'monedasDisponibles' => $monedasDisponibles
            ]);
        }
    }

    // Obtener módulos públicos
    private function getPublicModules()
    {
        try {
            return Module::where('is_active', true)
                ->where('is_public', true)
                ->with(['children' => function($query) {
                    $query->where('is_active', true)
                          ->where('is_public', true)
                          ->orderBy('order_position');
                }])
                ->whereNull('parent_id')
                ->orderBy('order_position')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener módulos públicos: ' . $e->getMessage());
            return collect([]);
        }
    }
}
