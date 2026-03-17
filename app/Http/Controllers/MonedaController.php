<?php

namespace App\Http\Controllers;

use App\Helpers\MonedaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonedaController extends Controller
{
    public function cambiar(Request $request)
    {
        try {
            // Validar que la moneda exista y esté activa
            $request->validate([
                'currency' => [
                    'required',
                    'string',
                    'size:3',
                    function ($attribute, $value, $fail) {
                        $moneda = MonedaHelper::getMonedaByCodigo($value);
                        if (!$moneda || !$moneda->is_active) {
                            $fail('La moneda seleccionada no está disponible.');
                        }
                    }
                ]
            ]);

            // Obtener la moneda completa para guardar más información
            $moneda = MonedaHelper::getMonedaByCodigo($request->currency);

            // Guardar en sesión (podemos guardar solo el código o todo el objeto)
            session([
                'moneda_seleccionada' => $request->currency,
                'moneda_id' => $moneda->id,
                'moneda_info' => [
                    'id' => $moneda->id,
                    'codigo' => $moneda->codigo_iso,
                    'simbolo' => $moneda->simbolo,
                    'nombre' => $moneda->nombre
                ]
            ]);

            // Limpiar cachés relacionados con monedas si es necesario
            // MonedaHelper::clearCache(); // Si tienes este método

            Log::info('Moneda cambiada exitosamente', [
                'currency' => $request->currency,
                'moneda_id' => $moneda->id,
                'user_ip' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Moneda cambiada correctamente',
                'currency' => $request->currency,
                'moneda' => [
                    'codigo' => $moneda->codigo_iso,
                    'simbolo' => $moneda->simbolo,
                    'nombre' => $moneda->nombre
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Intento de cambiar a moneda no válida', [
                'currency' => $request->currency,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Moneda no válida',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error al cambiar moneda: ' . $e->getMessage(), [
                'currency' => $request->currency,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al cambiar la moneda'
            ], 500);
        }
    }

    /**
     * Método opcional para obtener la moneda actual
     */
    public function actual()
    {
        try {
            $moneda = MonedaHelper::getMonedaActual();

            return response()->json([
                'success' => true,
                'moneda' => [
                    'id' => $moneda->id,
                    'codigo' => $moneda->codigo_iso,
                    'simbolo' => $moneda->simbolo,
                    'nombre' => $moneda->nombre,
                    'tasa_cambio_usd' => $moneda->tasa_cambio_usd ?? 1
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener moneda actual: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener moneda actual'
            ], 500);
        }
    }

    /**
     * Método opcional para obtener todas las monedas disponibles
     */
    public function disponibles()
    {
        try {
            $monedas = MonedaHelper::getMonedasActivas();

            return response()->json([
                'success' => true,
                'monedas' => $monedas->map(function($moneda) {
                    return [
                        'id' => $moneda->id,
                        'codigo' => $moneda->codigo_iso,
                        'simbolo' => $moneda->simbolo,
                        'nombre' => $moneda->nombre,
                        'pais' => $moneda->pais
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener monedas disponibles: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener monedas disponibles'
            ], 500);
        }
    }
}
