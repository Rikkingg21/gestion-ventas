<?php

namespace App\Http\Controllers;

use App\Helpers\MonedaHelper;
use App\Models\Cupon;
use App\Models\Moneda;
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

            // Obtener la moneda completa
            $moneda = MonedaHelper::getMonedaByCodigo($request->currency);
            $monedaAnterior = MonedaHelper::getMonedaActual();

            // Verificar si hay un cupón incompatible
            $cuponIncompatible = null;
            $cuponInfo = null;

            if (session()->has('cupon_aplicado')) {
                $cuponSesion = session('cupon_aplicado');
                $cupon = Cupon::find($cuponSesion['id']);

                if ($cupon && $cupon->moneda_id && $cupon->moneda_id != $moneda->id) {
                    $monedaCupon = Moneda::find($cupon->moneda_id);
                    $nombreMonedaCupon = $monedaCupon ? $monedaCupon->nombre : 'otra moneda';
                    $simboloMonedaCupon = $monedaCupon ? $monedaCupon->simbolo : '';

                    $cuponIncompatible = true;
                    $cuponInfo = [
                        'id' => $cupon->id,
                        'codigo' => $cupon->codigo,
                        'descuento' => $cupon->porcentaje_descuento
                            ? "{$cupon->porcentaje_descuento}% de descuento"
                            : "{$simboloMonedaCupon} " . number_format($cupon->descuento_monto, 2),
                        'moneda_original' => $nombreMonedaCupon,
                        'moneda_nueva' => $moneda->nombre,
                        'moneda_nueva_codigo' => $moneda->codigo_iso
                    ];
                }
            }

            // Si hay cupón incompatible, preguntar antes de cambiar
            if ($cuponIncompatible && !$request->has('confirmar')) {
                return response()->json([
                    'success' => false,
                    'requires_confirmation' => true,
                    'cupon_info' => $cuponInfo,
                    'new_currency' => [
                        'codigo' => $moneda->codigo_iso,
                        'simbolo' => $moneda->simbolo,
                        'nombre' => $moneda->nombre
                    ]
                ]);
            }

            // Si llegamos aquí, es porque no hay cupón incompatible o el usuario confirmó
            if ($cuponIncompatible && $request->has('confirmar') && $request->confirmar === true) {
                // Eliminar cupón de sesión
                session()->forget('cupon_aplicado');
                $mensajeCupon = " El cupón '{$cuponInfo['codigo']}' ({$cuponInfo['descuento']}) ha sido eliminado porque solo era válido para compras en {$cuponInfo['moneda_original']}.";
            } else {
                $mensajeCupon = '';
            }

            // Guardar nueva moneda en sesión
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

            // Construir mensaje de respuesta
            $mensaje = "Moneda cambiada a {$moneda->nombre} ({$moneda->simbolo})";
            if ($mensajeCupon) {
                $mensaje .= $mensajeCupon;
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'message_type' => $mensajeCupon ? 'warning' : 'success',
                'currency' => $request->currency,
                'moneda' => [
                    'id' => $moneda->id,
                    'codigo' => $moneda->codigo_iso,
                    'simbolo' => $moneda->simbolo,
                    'nombre' => $moneda->nombre
                ],
                'recargar' => true
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
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
