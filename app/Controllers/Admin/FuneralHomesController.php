<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FuneralHomeModel;

class FuneralHomesController extends BaseController
{
    public function index(): string
    {
        return view('admin/funeral_homes/index', [
            'homes' => (new FuneralHomeModel())->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function create(): string
    {
        helper(['form']);

        return view('admin/funeral_homes/form', ['home' => null]);
    }

    public function store()
    {
        (new FuneralHomeModel())->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('admin/funeral-homes')->with('success', 'Funeraria creada.');
    }

    public function edit(int $id): string
    {
        $home = (new FuneralHomeModel())->find($id);
        if (! $home) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        helper(['form']);

        return view('admin/funeral_homes/form', ['home' => $home]);
    }

    public function update(int $id)
    {
        (new FuneralHomeModel())->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('admin/funeral-homes')->with('success', 'Funeraria actualizada.');
    }

    public function delete(int $id)
    {
        (new FuneralHomeModel())->delete($id);

        return redirect()->to('admin/funeral-homes')->with('success', 'Funeraria eliminada.');
    }
}
