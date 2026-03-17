<?php

namespace App\Helpers;

use App\Models\Producto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImagenesHelper
{
    // Tiempo de caché para imágenes (7 días)
    const CACHE_TIEMPO = 604800; // 7 días en segundos

    // Obtener todas las imágenes de un producto
    public static function getImagenesProducto($producto)
    {
        if (is_numeric($producto)) {
            $producto = Producto::find($producto);
        }

        if (!$producto) return collect();

        $cacheKey = 'producto_imagenes_' . $producto->id;

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($producto) {
            return collect(range(1, 5))
                ->map(fn($i) => $producto->{"imagen_url_$i"})
                ->filter()
                ->map(fn($url) => self::procesarImagen($url))
                ->values();
        });
    }

    // Obtener imagen principal de un producto
    public static function getImagenPrincipal($producto, $default = null)
    {
        if (is_numeric($producto)) {
            $producto = Producto::find($producto);
        }

        if (!$producto) {
            return $default;
        }

        // Intentar obtener del caché
        $cacheKey = 'producto_imagen_principal_' . $producto->id;

        return Cache::remember($cacheKey, self::CACHE_TIEMPO, function () use ($producto, $default) {
            for ($i = 1; $i <= 5; $i++) {
                $campo = "imagen_url_$i";
                if (!empty($producto->$campo)) {
                    return self::procesarImagen($producto->$campo);
                }
            }

            return $default ?? self::getDefaultImage();
        });
    }

    // Obtener todas las imágenes de un producto con metadatos
    public static function getImagenesConMetadata($producto)
    {
        if (is_numeric($producto)) {
            $producto = Producto::find($producto);
        }

        if (!$producto) {
            return [];
        }

        $cacheKey = 'producto_imagenes_metadata_' . $producto->id;

        return Cache::remember($cacheKey, self::CACHE_TIEMPO, function () use ($producto) {
            $imagenes = [];

            for ($i = 1; $i <= 5; $i++) {
                $campo = "imagen_url_$i";
                if (!empty($producto->$campo)) {
                    $url = self::procesarImagen($producto->$campo);

                    $imagenes[] = [
                        'id' => $i,
                        'url' => $url,
                        'es_principal' => ($i === 1),
                        'orden' => $i,
                        'campo' => $campo
                    ];
                }
            }

            return $imagenes;
        });
    }

    // Procesar una URL de imagen
    protected static function procesarImagen($url)
    {
        if (!$url) {
            return self::getDefaultImage();
        }

        // Limpiar la URL
        $url = str_replace(['/producto/', 'storage/storage/'], ['/', 'storage/'], $url);

        // Si ya es URL completa, devolverla
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // Si empieza con storage/
        if (str_starts_with($url, 'storage/')) {
            return asset($url);
        }

        // Si empieza con /storage/
        if (str_starts_with($url, '/storage/')) {
            return asset(substr($url, 1));
        }

        // Por defecto
        return asset('storage/' . ltrim($url, '/'));
    }

    // Obtener imagen por defecto

    public static function getDefaultImage()
    {
        return asset('images/default-product.jpg');
    }

    // Limpiar caché de imágenes de un producto
    public static function clearCache($productoId)
    {
        $keys = [
            'producto_imagenes_' . $productoId,
            'producto_imagen_principal_' . $productoId,
            'producto_imagenes_metadata_' . $productoId,
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    // Verificar si una imagen existe
    public static function imagenExiste($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $headers = @get_headers($url);
            return $headers && strpos($headers[0], '200') !== false;
        }

        $path = public_path(parse_url($url, PHP_URL_PATH));
        return file_exists($path);
    }

    // Obtener todas las imágenes de un producto para carrusel
    public static function getImagenesParaCarrusel($producto)
    {
        $imagenes = self::getImagenesConMetadata($producto);

        if (empty($imagenes)) {
            return [
                [
                    'id' => 0,
                    'url' => self::getDefaultImage(),
                    'es_principal' => true,
                    'orden' => 0
                ]
            ];
        }

        return $imagenes;
    }
}
