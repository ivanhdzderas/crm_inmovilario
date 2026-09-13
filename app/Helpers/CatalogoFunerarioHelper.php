<?php

namespace App\Helpers;

class CatalogoFunerarioHelper
{
    public static function slug(string $text): string
    {
        return CatalogoHelper::slug($text);
    }

    public static function urlCatalogo(): string
    {
        return base_url('funerario');
    }

    public static function urlLugar(array $lugar): string
    {
        $slug = $lugar['slug'] ?? self::slug($lugar['name'] ?? '');

        return base_url('funerario/lugar/' . rawurlencode($slug));
    }

    public static function urlProducto(array $producto): string
    {
        $lugarSlug = $producto['location_slug'] ?? self::slug($producto['location_name'] ?? 'lugar');
        $homeSlug  = $producto['home_slug'] ?? self::slug($producto['home_name'] ?? 'funeraria');
        $prodSlug  = $producto['slug'] ?? self::slug($producto['name'] ?? 'paquete');

        return base_url('funerario/' . rawurlencode($lugarSlug) . '/' . rawurlencode($homeSlug) . '/' . rawurlencode($prodSlug));
    }

    public static function formatoPrecio($precio): string
    {
        return CatalogoHelper::formatoPrecioMxn($precio);
    }

    public static function totalPlan(array $plan): float
    {
        return (float) $plan['enganche'] + ((float) $plan['mensualidad'] * (int) $plan['months']);
    }

    /**
     * Convierte la mensualidad del plan al monto por periodo según preferencia del cliente.
     */
    public static function pagoPorFrecuencia(float $mensualidad, ?string $frecuencia): float
    {
        return match ($frecuencia) {
            'Semanal'   => round($mensualidad / 4, 2),
            'Quincenal' => round($mensualidad / 2, 2),
            default     => round($mensualidad, 2),
        };
    }

    public static function etiquetaFrecuencia(?string $frecuencia): string
    {
        return match ($frecuencia) {
            'Semanal'     => 'por semana',
            'Quincenal'   => 'por quincena',
            'Fin de mes'  => 'al fin de mes',
            'Mensual'     => 'mensual',
            default       => 'mensual',
        };
    }
}
