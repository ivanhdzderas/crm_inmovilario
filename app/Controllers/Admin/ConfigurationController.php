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
        
        foreach ($postData as $key => $value) {
            // Ignorar el token CSRF si está presente
            if ($key === csrf_token()) {
                continue;
            }
            
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
