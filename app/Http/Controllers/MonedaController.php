<?php

namespace App\Http\Controllers;

use App\Helpers\MonedaHelper;
use App\Models\Cupon;
use App\Models\Moneda;
use App\Models\CarritoProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonedaController extends Controller
{
    public function cambiar(Request $request)
    {
        try {
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

            $nuevaMoneda = MonedaHelper::getMonedaByCodigo($request->currency);
            $monedaAnterior = MonedaHelper::getMonedaActual();

            // Verificar cupón incompatible
            $cuponIncompatible = null;
            $cuponInfo = null;

            if (session()->has('cupon_aplicado')) {
                $cuponSesion = session('cupon_aplicado');
                $cupon = Cupon::find($cuponSesion['id']);

                if ($cupon && $cupon->moneda_id && $cupon->moneda_id != $nuevaMoneda->id) {
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
                        'moneda_nueva' => $nuevaMoneda->nombre,
                        'moneda_nueva_codigo' => $nuevaMoneda->codigo_iso
                    ];
                }
            }

            if ($cuponIncompatible && !$request->has('confirmar')) {
                return response()->json([
                    'success' => false,
                    'requires_confirmation' => true,
                    'cupon_info' => $cuponInfo,
                    'new_currency' => [
                        'codigo' => $nuevaMoneda->codigo_iso,
                        'simbolo' => $nuevaMoneda->simbolo,
                        'nombre' => $nuevaMoneda->nombre
                    ]
                ]);
            }

            if ($cuponIncompatible && $request->has('confirmar') && $request->confirmar === true) {
                session()->forget('cupon_aplicado');
                $mensajeCupon = " El cupón '{$cuponInfo['codigo']}' ({$cuponInfo['descuento']}) ha sido eliminado porque solo era válido para compras en {$cuponInfo['moneda_original']}.";
            } else {
                $mensajeCupon = '';
            }

            // ACTUALIZAR SOLO EL PRECIO LOCAL DEL CARRITO (NO EL USD)
            $this->actualizarPrecioLocalCarrito($nuevaMoneda);

            // Guardar nueva moneda en sesión
            session([
                'moneda_seleccionada' => $request->currency,
                'moneda_id' => $nuevaMoneda->id,
                'moneda_info' => [
                    'id' => $nuevaMoneda->id,
                    'codigo' => $nuevaMoneda->codigo_iso,
                    'simbolo' => $nuevaMoneda->simbolo,
                    'nombre' => $nuevaMoneda->nombre
                ]
            ]);

            $mensaje = "Moneda cambiada a {$nuevaMoneda->nombre} ({$nuevaMoneda->simbolo})";
            if ($mensajeCupon) {
                $mensaje .= $mensajeCupon;
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'message_type' => $mensajeCupon ? 'warning' : 'success',
                'currency' => $request->currency,
                'moneda' => [
                    'id' => $nuevaMoneda->id,
                    'codigo' => $nuevaMoneda->codigo_iso,
                    'simbolo' => $nuevaMoneda->simbolo,
                    'nombre' => $nuevaMoneda->nombre
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
     * Actualizar SOLO el precio local del carrito (precio_adquirido_local)
     * El precio USD (precio_adquirido_usd) NO se modifica
     */
    private function actualizarPrecioLocalCarrito($nuevaMoneda)
    {
        try {
            $carrito = CarritoController::cargarCarrito();

            if (!$carrito || $carrito->estaVacio()) {
                return;
            }

            foreach ($carrito->productos as $productoCarrito) {
                $producto = $productoCarrito->producto;

                if (!$producto) continue;

                // Obtener el precio en la nueva moneda usando el precio USD como base
                $precioInfo = MonedaHelper::getPrecioProductoEnMoneda($producto, $nuevaMoneda->id);

                // ACTUALIZAR SOLO el precio local, el USD se mantiene
                $productoCarrito->update([
                    'precio_adquirido_local' => $precioInfo['precio_con_descuento']
                ]);
            }

            // Actualizar la moneda del carrito
            $carrito->update([
                'moneda_id' => $nuevaMoneda->id
            ]);

            Log::info('Carrito actualizado a nueva moneda (solo precio local)', [
                'carrito_id' => $carrito->id,
                'moneda_id' => $nuevaMoneda->id,
                'moneda_codigo' => $nuevaMoneda->codigo_iso
            ]);

        } catch (\Exception $e) {
            Log::error('Error al actualizar precio local del carrito: ' . $e->getMessage(), [
                'moneda_id' => $nuevaMoneda->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
