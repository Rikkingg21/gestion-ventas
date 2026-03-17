<?php

namespace App\View\Composers;

use App\Models\Module;
use App\Helpers\GeoLocation;
use App\Helpers\MonedaHelper;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClienteMenuComposer
{
    public function compose(View $view)
    {
        try {
            $user = Auth::guard('client')->user();
            $isAuthenticated = Auth::guard('client')->check();
        } catch (\Exception $e) {
            $user = null;
            $isAuthenticated = false;
        }

        // PASO 1: DETECTAR PAÍS POR IP
        $countryCode = null;
        $countryName = null;

        try {
            $countryCode = GeoLocation::getCountryCode();
            $countryName = GeoLocation::getCountry();
        } catch (\Exception $e) {}

        // PASO 2: OBTENER MONEDA ACTUAL (del helper)
        $monedaActual = MonedaHelper::getMonedaActual();
        $monedaInfo = MonedaHelper::formatMonedaForView($monedaActual);

        // PASO 3: OBTENER MONEDAS DISPONIBLES PARA EL SELECTOR
        $monedasDisponibles = $this->getMonedasDisponibles($user, $countryCode);

        // PASO 4: CONSTRUIR userGeoInfo
        $userGeoInfo = [
            'country' => [
                'code' => $countryCode,
                'name' => $countryName,
            ],
            'currency' => $monedaInfo,
        ];

        // Módulos públicos
        $publicModules = $this->getPublicModules();

        return $view->with([
            'menuModules' => collect([]),
            'publicModules' => $publicModules,
            'isAuthenticated' => $isAuthenticated,
            'currentUser' => $user,
            'userGeoInfo' => $userGeoInfo,
            'monedasDisponibles' => $monedasDisponibles
        ]);
    }

    /**
     * Obtener monedas disponibles para el selector
     */
    private function getMonedasDisponibles($user, $geoCountryCode)
    {
        $monedasIds = [];

        // 1. Siempre incluir moneda por defecto (ID 1 - USD)
        $monedasIds[] = 1;

        // 2. Moneda por GeoLocation (si existe y no es la misma que la default)
        if ($geoCountryCode) {
            $monedaGeo = MonedaHelper::getMonedaByPais($geoCountryCode);
            if ($monedaGeo && $monedaGeo->id != 1) {
                $monedasIds[] = $monedaGeo->id;
            }
        }

        // 3. Moneda del país del usuario (si está logueado, tiene país y no está ya incluida)
        if ($user && !empty($user->pais)) {
            $monedaUser = MonedaHelper::getMonedaByPais($user->pais);
            if ($monedaUser && $monedaUser->id != 1 && !in_array($monedaUser->id, $monedasIds)) {
                $monedasIds[] = $monedaUser->id;
            }
        }

        // Obtener todas las monedas activas y filtrar por los IDs únicos
        $todasMonedas = MonedaHelper::getMonedasActivas();

        // Filtrar y ordenar: primero default (USD), luego las demás
        return $todasMonedas->filter(function($moneda) use ($monedasIds) {
            return in_array($moneda->id, $monedasIds);
        })->sortBy(function($moneda) {
            // USD (ID 1) primero
            return $moneda->id == 1 ? 0 : 1;
        })->values();
    }

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
