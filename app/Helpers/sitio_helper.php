<?php

/**
 * Datos del sitio público (tabla configurations, claves site_*).
 * El contacto (WhatsApp, correo, dirección) es opcional: si está vacío, no se muestra.
 */

if (! function_exists('sitio')) {
    function sitio(?string $clave = null, $default = '')
    {
        static $datos = null;

        if ($datos === null) {
            $datos = [
                'site_name'    => 'Pacific Inmobiliaria',
                'site_tagline' => 'Tepic · Bahía · Pacífico',
            ];
            try {
                $filas = db_connect()->table('configurations')->like('config_key', 'site_', 'after')->get()->getResultArray();
                foreach ($filas as $f) {
                    if (trim((string) $f['config_value']) !== '') {
                        $datos[$f['config_key']] = trim((string) $f['config_value']);
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'sitio(): ' . $e->getMessage());
            }
        }

        return $clave === null ? $datos : ($datos[$clave] ?? $default);
    }
}

if (! function_exists('sitio_whatsapp_url')) {
    /** Enlace wa.me si hay WhatsApp configurado; null si no. */
    function sitio_whatsapp_url(string $mensaje = ''): ?string
    {
        $num = preg_replace('/\D/', '', (string) sitio('site_whatsapp'));
        if ($num === '') {
            return null;
        }
        if (strlen($num) === 10) {
            $num = '52' . $num;
        }

        return 'https://wa.me/' . $num . ($mensaje !== '' ? '?text=' . rawurlencode($mensaje) : '');
    }
}

if (! function_exists('precio_corto')) {
    /** $1,850,000 (sin centavos cuando son cero) */
    function precio_corto($precio): string
    {
        $n = (float) $precio;
        if ($n <= 0) {
            return 'Precio a consultar';
        }

        return '$' . number_format($n, fmod($n, 1.0) === 0.0 ? 0 : 2);
    }
}
