<?php

namespace App\Helpers;

class CatalogoHelper
{
    /**
     * Slug ASCII seguro para URLs (sin acentos ni caracteres especiales).
     */
    public static function slug(string $text): string
    {
        helper(['url', 'text']);

        return mb_url_title($text, '-', true);
    }

    /**
     * Normaliza un segmento recibido en la URL (decodifica %C3%B3, quita acentos, etc.).
     */
    public static function normalizeSegment(string $segment): string
    {
        $decoded = rawurldecode($segment);
        $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return self::slug($decoded);
    }

    public static function coincideSlug(string $texto, string $segmentoUrl): bool
    {
        if ($texto === '') {
            return false;
        }

        return self::slug($texto) === self::normalizeSegment($segmentoUrl);
    }

    public static function coincideSlugProyecto(string $nombreProyecto, string $segmentoUrl): bool
    {
        $slugProyecto = self::slug($nombreProyecto !== '' ? $nombreProyecto : 'general');
        $segmento     = self::normalizeSegment($segmentoUrl);

        if ($slugProyecto === $segmento) {
            return true;
        }

        $prefijo = 'desarrollo-';

        return ($prefijo . $slugProyecto === $segmento) || ($slugProyecto === $prefijo . $segmento);
    }

    /**
     * @return list<string>
     */
    public static function parsePaymentMethods(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw), static fn ($v) => $v !== ''));
        }

        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded), static fn ($v) => $v !== ''));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $raw)), static fn ($v) => $v !== ''));
    }

    public static function urlDesarrollo(array $proyecto): string
    {
        $slug = $proyecto['slug'] ?? self::slug($proyecto['name'] ?? $proyecto['nombre_proyecto'] ?? '');

        return base_url('desarrollo/' . rawurlencode($slug));
    }

    public static function urlInmueble(array $propiedad): string
    {
        return base_url(self::pathInmueble($propiedad));
    }

    public static function urlCatalogoInmueble(array $propiedad): string
    {
        return base_url(self::pathCatalogoInmueble($propiedad));
    }

    public static function pathInmueble(array $propiedad): string
    {
        $projectSlug  = $propiedad['project_slug'] ?? self::slug($propiedad['project_name'] ?? 'general');
        $propertySlug = $propiedad['slug'] ?? self::slug($propiedad['name'] ?? 'inmueble');

        return 'desarrollo/' . rawurlencode($projectSlug) . '/' . rawurlencode($propertySlug);
    }

    public static function pathCatalogoInmueble(array $propiedad): string
    {
        $projectSlug  = $propiedad['project_slug'] ?? self::slug($propiedad['project_name'] ?? 'general');
        $propertySlug = $propiedad['slug'] ?? self::slug($propiedad['name'] ?? 'inmueble');

        return 'catalogo/' . rawurlencode($projectSlug) . '/' . rawurlencode($propertySlug);
    }

    /**
     * Indica si la URL entrante debe redirigirse a la versión canónica ASCII.
     */
    public static function requiereRedireccionCanonica(string $segmentoEntrada, string $segmentoCanonico): bool
    {
        return self::normalizeSegment($segmentoEntrada) !== $segmentoCanonico
            || $segmentoEntrada !== $segmentoCanonico;
    }

    public static function formatoPrecioMxn($precio): string
    {
        return '$' . number_format((float) $precio, 2) . ' MXN';
    }
}
