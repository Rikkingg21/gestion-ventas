<?php

namespace App\Helpers;

use App\Models\MetodoPago;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MetodoPagoHelper
{
    /**
     * Obtener métodos de pago activos filtrados por moneda
     *
     * @param int|null $monedaId ID de la moneda actual (de MonedaHelper::getMonedaActual())
     * @param string|null $paisCode Código de país para filtrar adicionalmente
     */
    public static function getMetodosActivos($monedaId = null, $paisCode = null)
    {
        $query = MetodoPago::where('is_active', true);

        // Filtrar por moneda:
        // - Si hay moneda específica, mostrar métodos que coincidan O los que son internacionales (moneda_id null)
        // - Si no hay moneda, solo mostrar métodos internacionales
        if ($monedaId) {
            $query->where(function($q) use ($monedaId) {
                $q->where('moneda_id', $monedaId)
                  ->orWhereNull('moneda_id');
            });
        } else {
            $query->whereNull('moneda_id');
        }

        // Filtrar por país si se especifica
        if ($paisCode) {
            $query->where(function($q) use ($paisCode) {
                $q->where('pais_code', $paisCode)
                  ->orWhereNull('pais_code');
            });
        }

        return $query->orderBy('tipo')
                     ->orderBy('nombre')
                     ->get();
    }

    /**
     * Obtener método de pago por slug (sin filtro de moneda, para validación)
     */
    public static function getMetodoBySlug($slug)
    {
        return MetodoPago::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Verificar si un método de pago es válido para la moneda actual
     */
    public static function esValidoParaMoneda($metodo, $monedaActual)
    {
        if (!$metodo || !$monedaActual) {
            return false;
        }

        // Si el método no tiene moneda específica, es internacional
        if (is_null($metodo->moneda_id)) {
            return true;
        }

        // Si tiene moneda específica, debe coincidir con la moneda actual
        return $metodo->moneda_id == $monedaActual->id;
    }

    /**
     * Decodificar detalles JSON
     */
    public static function getDetalles($metodo)
    {
        if (!$metodo || !$metodo->detalles) {
            return [];
        }

        $detalles = $metodo->detalles;

        if (is_string($detalles)) {
            $detalles = json_decode($detalles, true);
        }

        return is_array($detalles) ? $detalles : [];
    }

    /**
     * Obtener campos del formulario - TODOS los métodos necesitan comprobantes
     */
    public static function getCamposFormulario($metodo)
    {
        $detalles = self::getDetalles($metodo);
        $campos = [];

        // Campos base según el tipo de método
        switch ($metodo->tipo) {
            case 'billetera_digital':
                // Para Yape, Plin, etc.
                $campos = [
                    [
                        'nombre_campo' => 'numero_referencia',
                        'label' => 'Número de operación / referencia',
                        'tipo' => 'text',
                        'required' => true,
                        'placeholder' => 'Ingresa el número de operación que te dio el banco'
                    ]
                ];
                break;

            case 'cuenta_bancaria':
                // Para BCP, Interbank, etc.
                $campos = [
                    [
                        'nombre_campo' => 'numero_operacion',
                        'label' => 'Número de operación',
                        'tipo' => 'text',
                        'required' => true,
                        'placeholder' => 'Ingresa el número de operación de la transferencia'
                    ],
                    [
                        'nombre_campo' => 'banco_origen',
                        'label' => 'Banco de origen',
                        'tipo' => 'text',
                        'required' => true,
                        'placeholder' => 'Ej: BCP, Interbank, BBVA'
                    ]
                ];
                break;

            case 'transferencia_email':
                // Para PayPal, etc.
                $campos = [
                    [
                        'nombre_campo' => 'email_paypal',
                        'label' => 'Email de tu cuenta PayPal',
                        'tipo' => 'email',
                        'required' => true,
                        'placeholder' => 'tu-email@ejemplo.com'
                    ],
                    [
                        'nombre_campo' => 'id_transaccion',
                        'label' => 'ID de transacción PayPal',
                        'tipo' => 'text',
                        'required' => true,
                        'placeholder' => 'Ej: 9XK12345XK123456X'
                    ]
                ];
                break;

            default:
                $campos = [];
                break;
        }

        // TODOS los métodos de pago necesitan adjuntar comprobantes (hasta 3 imágenes)
        $campos[] = [
            'nombre_campo' => 'comprobante_1',
            'label' => 'Comprobante de pago',
            'tipo' => 'file',
            'required' => true,
            'accept' => 'image/*',
            'max_size' => 5120,
            'help_text' => 'Captura de pantalla o foto del comprobante (máx. 5MB)'
        ];

        $campos[] = [
            'nombre_campo' => 'comprobante_2',
            'label' => 'Comprobante adicional (opcional)',
            'tipo' => 'file',
            'required' => false,
            'accept' => 'image/*',
            'max_size' => 5120,
            'help_text' => 'Captura adicional (opcional)'
        ];

        $campos[] = [
            'nombre_campo' => 'comprobante_3',
            'label' => 'Comprobante adicional (opcional)',
            'tipo' => 'file',
            'required' => false,
            'accept' => 'image/*',
            'max_size' => 5120,
            'help_text' => 'Captura adicional (opcional)'
        ];

        return $campos;
    }

    /**
     * Validar datos según el método de pago
     */
    public static function validarDatosPago($metodo, $datos)
    {
        $errores = [];
        $campos = self::getCamposFormulario($metodo);

        foreach ($campos as $campo) {
            if ($campo['required']) {
                if ($campo['tipo'] === 'file') {
                    if (!isset($datos[$campo['nombre_campo']]) || !$datos[$campo['nombre_campo']]->isValid()) {
                        $errores[] = "El campo {$campo['label']} es obligatorio.";
                    } elseif ($datos[$campo['nombre_campo']]->getSize() > $campo['max_size'] * 1024) {
                        $errores[] = "El archivo {$campo['label']} no debe superar los " . ($campo['max_size'] / 1024) . "MB.";
                    }
                } else {
                    if (empty($datos[$campo['nombre_campo']])) {
                        $errores[] = "El campo {$campo['label']} es obligatorio.";
                    } elseif ($campo['tipo'] === 'email' && !filter_var($datos[$campo['nombre_campo']], FILTER_VALIDATE_EMAIL)) {
                        $errores[] = "El formato del email no es válido.";
                    }
                }
            }
        }

        return $errores;
    }

    /**
     * Obtener métodos de pago con su información formateada para la vista
     * Esta es la función principal que usarás en el controlador
     */
    public static function getMetodosPagoData($monedaActual, $paisCode = null)
    {
        $monedaId = $monedaActual->id ?? null;
        $metodosPago = self::getMetodosActivos($monedaId, $paisCode);

        return $metodosPago->map(function($metodo) {
            return [
                'id' => $metodo->id,
                'nombre' => $metodo->nombre,
                'slug' => $metodo->slug,
                'tipo' => $metodo->tipo,
                'moneda_id' => $metodo->moneda_id,
                'pais_code' => $metodo->pais_code,
                'imagen_url' => $metodo->imagen_url,
                'icono_class' => $metodo->icono_class,
                'detalles' => self::getDetalles($metodo),
                'campos_formulario' => self::getCamposFormulario($metodo),
                'es_internacional' => is_null($metodo->moneda_id)
            ];
        });
    }
}
