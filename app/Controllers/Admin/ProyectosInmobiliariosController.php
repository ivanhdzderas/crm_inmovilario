<?php

namespace App\Controllers\Admin; // <--- CAMBIO AQUÍ
use App\Controllers\BaseController; 

use App\Models\ProyectosInmobiliariosModel;
use CodeIgniter\Controller;

class ProyectosInmobiliariosController extends BaseController
{
    protected $proyectosModel;

    public function __construct()
    {
        $this->proyectosModel = new ProyectosInmobiliariosModel();
    }

    public function index()
    {
        $data['proyectos'] = $this->proyectosModel->findAll();
        return view('admin/proyectos/index', $data);
    }

    public function create()
    {
        return view('admin/proyectos/create');
    }

    public function store()
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'location'    => $this->request->getPost('location')
        ];

        $this->proyectosModel->insert($data);
        return redirect()->to(base_url('admin/proyectos'))->with('success', 'Proyecto creado exitosamente.');
    }

    public function storeAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Solicitud inválida.'
            ]);
        }

        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));
        $location = trim((string) $this->request->getPost('location'));

        if ($name === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El nombre del proyecto es obligatorio.'
            ]);
        }

        $data = [
            'name'        => $name,
            'description' => $description,
            'location'    => $location,
        ];

        $newId = $this->proyectosModel->insert($data, true);
        if (!$newId) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'No se pudo crear el proyecto.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Proyecto creado exitosamente.',
            'project' => [
                'id' => (int) $newId,
                'name' => $name,
                'description' => $description,
                'location' => $location,
            ],
        ]);
    }
    
    // En tu controlador ProyectosInmobiliariosController.php
    public function edit($id = null)
    {
        $proyecto = $this->proyectosModel->find($id);

        if (empty($proyecto)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se puede encontrar el proyecto con el ID: ' . $id);
        }

        $data['proyecto'] = $proyecto;

        return view('admin/proyectos/edit', $data);
    }
    
    public function update($id = null)
    {
        $data = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'location'    => $this->request->getPost('location')
        ];

        $this->proyectosModel->update($id, $data);
        return redirect()->to(base_url('admin/proyectos'))->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function delete($id = null)
    {
        $this->proyectosModel->delete($id);
        return redirect()->to(base_url('proyectos'))->with('success', 'Proyecto eliminado.');
    }
    // En tu controlador ProyectosInmobiliariosController.php
    public function show($id = null)
    {
        $proyecto = $this->proyectosModel->find($id);

        if (empty($proyecto)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se puede encontrar el proyecto con el ID: ' . $id);
        }

        // Cargar modelo de inmuebles
        $realEstateModel = model('RealEstateModel');
        $realEstatePhotosModel = model('RealEstatePhotosModel');
        
        // Obtener inmuebles del proyecto
        $inmuebles = $realEstateModel->where('project_id', $id)->findAll();
        
        // Para cada inmueble, obtener su primera foto
        foreach ($inmuebles as &$inmueble) {
            $fotos = $realEstatePhotosModel->where('real_estate_id', $inmueble['id'])
                                          ->orderBy('id', 'ASC')
                                          ->findAll();
            $inmueble['primera_foto'] = !empty($fotos) ? $fotos[0] : null;
            $inmueble['total_fotos'] = count($fotos);
        }

        $data = [
            'proyecto' => $proyecto,
            'inmuebles' => $inmuebles
        ];

        return view('admin/proyectos/show', $data);
    }
}