<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DocumentTypeModel;

class DocumentTypesController extends BaseController
{
    /**
     * Muestra la lista de todos los tipos de documentos.
     *
     * @return string
     */
    public function index(): string
    {
        $model = new DocumentTypeModel();
        $data['document_types'] = $model->findAll();
        return view('admin/document_types/index', $data);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de documento.
     *
     * @return string
     */
    public function create(): string
    {
        helper(['form']);
        return view('admin/document_types/create');
    }

    /**
     * Guarda un nuevo tipo de documento en la base de datos.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function store()
    {
        helper(['form']);
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|min_length[3]',
        ];

        if ($this->validate($rules)) {
            $model = new DocumentTypeModel();
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'is_required' => $this->request->getPost('is_required') ? 1 : 0,
            ];
            $model->save($data);
            return redirect()->to('admin/document_types')->with('success', 'Tipo de documento agregado correctamente.');
        } else {
            return view('admin/document_types/create', ['validation' => $this->validator]);
        }
    }

    /**
     * Muestra el formulario para editar un tipo de documento existente.
     *
     * @param int $id El ID del tipo de documento a editar.
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function edit($id)
    {
        $model = new DocumentTypeModel();
        $data['document_type'] = $model->find($id);
        if (!$data['document_type']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se puede encontrar el tipo de documento con el ID: ' . $id);
        }
        return view('admin/document_types/edit', $data);
    }

    /**
     * Actualiza un tipo de documento existente en la base de datos.
     *
     * @param int $id El ID del tipo de documento a actualizar.
     * @return \CodeIgniter\HTTP\RedirectResponse|string
     */
    public function update($id)
    {
        helper(['form']);
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|min_length[3]',
        ];

        if ($this->validate($rules)) {
            $model = new DocumentTypeModel();
            $data = [
                'name' => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'is_required' => $this->request->getPost('is_required') ? 1 : 0,
            ];
            $model->update($id, $data);
            return redirect()->to('admin/document_types')->with('success', 'Tipo de documento actualizado correctamente.');
        } else {
            $data['document_type'] = (new DocumentTypeModel())->find($id);
            $data['validation'] = $this->validator;
            return view('admin/document_types/edit', $data);
        }
    }

    /**
     * Elimina un tipo de documento de la base de datos.
     *
     * @param int $id El ID del tipo de documento a eliminar.
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete($id): \CodeIgniter\HTTP\RedirectResponse
    {
        $model = new DocumentTypeModel();
        $model->delete($id);
        return redirect()->to('admin/document_types')->with('success', 'Tipo de documento eliminado correctamente.');
    }
}
