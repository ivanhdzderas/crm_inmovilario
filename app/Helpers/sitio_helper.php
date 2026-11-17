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

if (! function_exists('correo_enviar')) {
    /**
     * Envía un correo usando el servidor SMTP de Configuración > Correo.
     * Si no hay SMTP configurado, usa la configuración de app/Config/Email.php.
     * Devuelve [bool enviado, string detalle].
     */
    function correo_enviar(string $para, string $asunto, string $html): array
    {
        $cfg = [];
        try {
            foreach (db_connect()->table('configurations')->like('config_key', 'mail_', 'after')->get()->getResultArray() as $f) {
                $cfg[$f['config_key']] = trim((string) $f['config_value']);
            }
        } catch (\Throwable $e) {
            return [false, 'No se pudo leer la configuración: ' . $e->getMessage()];
        }

        $email = \Config\Services::email(null, false);
        if (! empty($cfg['mail_smtp_host'])) {
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $cfg['mail_smtp_host'],
                'SMTPPort'   => (int) ($cfg['mail_smtp_port'] ?? 587) ?: 587,
                'SMTPUser'   => $cfg['mail_smtp_user'] ?? '',
                'SMTPPass'   => $cfg['mail_smtp_pass'] ?? '',
                'SMTPCrypto' => in_array($cfg['mail_smtp_crypto'] ?? 'tls', ['tls', 'ssl', ''], true) ? ($cfg['mail_smtp_crypto'] ?? 'tls') : 'tls',
                'SMTPTimeout' => 15,
                'mailType'   => 'html',
                'charset'    => 'UTF-8',
            ]);
        } else {
            $email->setMailType('html');
        }

        $desde  = ($cfg['mail_from_email'] ?? '') ?: (config('Email')->fromEmail ?: ($cfg['mail_smtp_user'] ?? ''));
        $nombre = ($cfg['mail_from_name'] ?? '') ?: sitio('site_name');
        if ($desde === '') {
            return [false, 'Falta el correo remitente. Configúralo en Configuración > Correo.'];
        }

        $email->setFrom($desde, $nombre);
        $email->setTo($para);
        $email->setSubject($asunto);
        $email->setMessage($html);

        try {
            if ($email->send(false)) {
                return [true, 'Correo enviado.'];
            }
            $debug = strip_tags((string) $email->printDebugger(['headers']));

            return [false, mb_substr(trim(preg_replace('/\s+/', ' ', $debug)), 0, 400)];
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }
    }
}

if (! function_exists('dinero')) {
    /** Monto en pesos para estados de cuenta: $0, $1,250, $3,400.50 */
    function dinero($monto): string
    {
        $n = (float) $monto;

        return ($n < 0 ? '-$' : '$') . number_format(abs($n), fmod(abs($n), 1.0) === 0.0 ? 0 : 2);
    }
}
