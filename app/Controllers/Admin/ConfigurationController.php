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
        ];

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
}
