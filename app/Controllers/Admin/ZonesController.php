<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ZoneModel;

class ZonesController extends BaseController
{
    protected $zoneModel;

    public function __construct()
    {
        $this->zoneModel = new ZoneModel();
        helper('form'); // las vistas usan form_open()
    }

    /**
     * Mostrar lista de zonas
     */
    public function index()
    {
        $data = [
            'title' => 'Catálogo de Zonas',
            'zones' => $this->zoneModel->orderBy('name', 'ASC')->findAll()
        ];

        return view('admin/zones/index', $data);
    }

    /**
     * Mostrar formulario para crear nueva zona
     */
    public function create()
    {
        helper('form');

        $data = [
            'title' => 'Nueva Zona',
            'zone' => null,
            'validation' => null
        ];

        return view('admin/zones/form', $data);
    }

    /**
     * Guardar nueva zona
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|is_unique[zones.name]'
        ];

        if (!$this->validate($rules)) {
            $data = [
                'title' => 'Nueva Zona',
                'zone' => null,
                'validation' => $this->validator
            ];
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $this->validator->getErrors()['name'] ?? 'Error de validación.'
                ]);
            }

            return view('admin/zones/form', $data);
        }

        $data = [
            'name' => trim($this->request->getPost('name'))
        ];

        $newZoneId = $this->zoneModel->insert($data);

        if ($newZoneId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Zona creada exitosamente.',
                    'zone' => [
                        'id' => $newZoneId,
                        'name' => $data['name']
                    ]
                ]);
            }
            session()->setFlashdata('success', 'Zona creada exitosamente.');
            return redirect()->to(site_url('admin/zones'));
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Error al crear la zona.']);
            }
            session()->setFlashdata('error', 'Error al crear la zona.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Mostrar formulario para editar zona
     */
    public function edit($id)
    {
        $zone = $this->zoneModel->find($id);

        if (!$zone) {
            session()->setFlashdata('error', 'Zona no encontrada.');
            return redirect()->to(site_url('admin/zones'));
        }

        $data = [
            'title' => 'Editar Zona',
            'zone' => $zone,
            'validation' => null
        ];

        return view('admin/zones/form', $data);
    }

    /**
     * Actualizar zona existente
     */
    public function update($id)
    {
        $zone = $this->zoneModel->find($id);

        if (!$zone) {
            session()->setFlashdata('error', 'Zona no encontrada.');
            return redirect()->to(site_url('admin/zones'));
        }

        // Validar que el nombre no esté en uso por otra zona
        $newName = trim($this->request->getPost('name'));
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]'
        ];

        // Solo validar unicidad si el nombre cambió
        if (strtolower($newName) !== strtolower($zone['name'])) {
            $rules['name'] .= '|is_unique[zones.name]';
        }

        if (!$this->validate($rules)) {
            $data = [
                'title' => 'Editar Zona',
                'zone' => $zone,
                'validation' => $this->validator
            ];

            return view('admin/zones/form', $data);
        }

        $data = [
            'name' => $newName
        ];

        if ($this->zoneModel->update($id, $data)) {
            session()->setFlashdata('success', 'Zona actualizada exitosamente.');
            return redirect()->to(site_url('admin/zones'));
        } else {
            session()->setFlashdata('error', 'Error al actualizar la zona.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Eliminar zona (AJAX)
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(site_url('admin/zones'));
        }

        $zone = $this->zoneModel->find($id);

        if (!$zone) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ]);
        }

        // Verificar si la zona está siendo usada por algún cliente
        $clientModel = model('ClientModel');
        $clientsUsingZone = $clientModel->where('zone_id', $id)->countAllResults();

        if ($clientsUsingZone > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar la zona porque está siendo utilizada por ' . $clientsUsingZone . ' cliente(s).'
            ]);
        }

        // Verificar si la zona está siendo usada por algún inmueble
        $realEstateModel = model('RealEstateModel');
        if ($realEstateModel) {
            $propertiesUsingZone = $realEstateModel->where('zone_id', $id)->countAllResults();
            
            if ($propertiesUsingZone > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se puede eliminar la zona porque está siendo utilizada por ' . $propertiesUsingZone . ' inmueble(s).'
                ]);
            }
        }

        if ($this->zoneModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Zona eliminada exitosamente.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar la zona.'
            ]);
        }
    }

    /**
     * API para obtener zonas (usado en selects)
     */
    public function getZones()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(site_url('admin/zones'));
        }

        $zones = $this->zoneModel->orderBy('name', 'ASC')->findAll();

        return $this->response->setJSON([
            'success' => true,
            'zones' => $zones
        ]);
    }

    /**
     * Obtener contador de clientes por zona (AJAX)
     */
    public function getClientsCount($zoneId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to(site_url('admin/zones'));
        }

        $zone = $this->zoneModel->find($zoneId);
        if (!$zone) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Zona no encontrada.'
            ]);
        }

        $clientModel = model('ClientModel');
        $clientsCount = $clientModel->where('zone_id', $zoneId)->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'count' => $clientsCount,
            'zone_name' => $zone['name']
        ]);
    }
}