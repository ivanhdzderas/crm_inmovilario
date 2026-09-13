<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ConfigurationModel;

class ConfigController extends BaseController
{
    public function documentRequirement()
    {
        helper('form'); // Cargar el helper de formulario

        $configModel = new ConfigurationModel();
        $data['title'] = 'Configurar Requisitos de Documentos';

        if ($this->request->getMethod() === 'post') {
            $newValue = $this->request->getPost('requirement_type');

            // Validar el valor
            if (!in_array($newValue, ['all', 'new', 'used'])) {
                return redirect()->back()->with('error', 'Valor no válido.');
            }

            // Buscar si la configuración ya existe
            $config = $configModel->where('config_key', 'document_requirement_type')->first();

            if ($config) {
                // Actualizar
                $configModel->update($config['id'], ['config_value' => $newValue]);
            } else {
                // Insertar
                $configModel->insert([
                    'config_key' => 'document_requirement_type',
                    'config_value' => $newValue
                ]);
            }

            return redirect()->to('admin/config/document_requirement')->with('success', 'Configuración guardada correctamente.');
        }

        // Para el método GET, obtener el valor actual
        $config = $configModel->where('config_key', 'document_requirement_type')->first();
        $data['currentValue'] = $config['config_value'] ?? 'all';

        return view('admin/configuration/document_requirement', $data);
    }
}