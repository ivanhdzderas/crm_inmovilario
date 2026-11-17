<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ConfigurationModel;

class ConfigurationController extends BaseController
{
    /**
     * Muestra el formulario para configurar los documentos requeridos.
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        helper('form'); // Cargar el helper de formulario

        $configModel = new ConfigurationModel();
        
        // Obtener todas las configuraciones de la base de datos
        $configs = $configModel->findAll();
        
        // Convertir a un array asociativo más fácil de usar en la vista
        $data['settings'] = [];
        foreach ($configs as $config) {
            $data['settings'][$config['config_key']] = $config['config_value'];
        }

        // Asegurar valores por defecto si no existen
        $data['settings']['company_name'] = $data['settings']['company_name'] ?? 'Tu Inmobiliaria';
        $data['settings']['company_website'] = $data['settings']['company_website'] ?? 'www.tusitio.com';
        $data['settings']['document_requirement_type'] = $data['settings']['document_requirement_type'] ?? 'all';
        $data['asesores'] = db_connect()->table('users')->select('id, full_name, role_id')->where('is_active', 1)->orderBy('full_name')->get()->getResultArray();

        return view('admin/configuration/index', $data);
    }

    /**
     * Guarda la configuración de documentos requeridos.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update()
    {
        $configModel = new ConfigurationModel();
        $postData = $this->request->getPost();

        // Solo se guardan claves conocidas (antes se guardaba cualquier campo enviado)
        $permitidas = [
            'company_name', 'company_website', 'document_requirement_type',
            'site_name', 'site_tagline', 'site_whatsapp', 'site_email', 'site_address', 'site_leads_user_id',
            'mail_smtp_host', 'mail_smtp_port', 'mail_smtp_user', 'mail_smtp_pass', 'mail_smtp_crypto', 'mail_from_email', 'mail_from_name',
        ];

        // La contraseña SMTP no se muestra en el formulario: si llega vacía, se conserva la guardada
        if (array_key_exists('mail_smtp_pass', $postData) && trim((string) $postData['mail_smtp_pass']) === '') {
            unset($postData['mail_smtp_pass']);
        }
        if (! empty($postData['mail_from_email']) && ! filter_var($postData['mail_from_email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'El correo remitente no es válido.');
        }
        if (! empty($postData['mail_smtp_port']) && ! ctype_digit((string) $postData['mail_smtp_port'])) {
            return redirect()->back()->withInput()->with('error', 'El puerto SMTP debe ser un número (normalmente 587 o 465).');
        }

        $whatsapp = preg_replace('/\D/', '', (string) ($postData['site_whatsapp'] ?? ''));
        if ($whatsapp !== '' && ! in_array(strlen($whatsapp), [10, 12], true)) {
            return redirect()->back()->withInput()->with('error', 'El WhatsApp debe tener 10 dígitos (o 12 con 52). Déjalo vacío para no mostrarlo.');
        }
        if (! empty($postData['site_email']) && ! filter_var($postData['site_email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'El correo del sitio no es válido.');
        }

        foreach ($postData as $key => $value) {
            if (! in_array($key, $permitidas, true)) {
                continue;
            }
            $value = trim((string) $value);

            $existingConfig = $configModel->where('config_key', $key)->first();
            
            if ($existingConfig) {
                // Si existe, la actualiza
                $configModel->update($existingConfig['id'], ['config_value' => $value]);
            } else {
                // Si no existe, la crea
                $configModel->insert([
                    'config_key' => $key, 
                    'config_value' => $value
                ]);
            }
        }

        return redirect()->to('admin/settings')->with('success', 'Configuración guardada correctamente.');
    }

    /** Envía un correo de prueba al administrador que lo solicita. */
    public function testEmail()
    {
        helper('sitio');
        $user = db_connect()->table('users')->select('email')->where('id', (int) session()->get('user_id'))->get()->getRowArray();
        if (! $user) {
            return redirect()->to('admin/settings#correo')->with('error', 'No se encontró tu usuario.');
        }

        [$ok, $detalle] = correo_enviar(
            $user['email'],
            'Prueba de correo · ' . sitio('site_name'),
            '<p>¡Listo! El correo del sistema está bien configurado.</p><p>Ya funcionará la recuperación de contraseña.</p>'
        );

        return redirect()->to('admin/settings#correo')->with($ok ? 'success' : 'error', $ok
            ? 'Correo de prueba enviado a ' . $user['email'] . '. Revisa tu bandeja (y spam).'
            : 'No se pudo enviar el correo de prueba: ' . $detalle);
    }
}
