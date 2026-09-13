<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CancellationReasonModel;

class CancellationReasonsController extends BaseController
{
    protected CancellationReasonModel $reasonModel;

    public function __construct()
    {
        $this->reasonModel = new CancellationReasonModel();
    }

    public function index(): string
    {
        return view('admin/cancellation_reasons/index', [
            'reasons' => $this->reasonModel->getAllGrouped(),
        ]);
    }

    public function create(): string
    {
        helper(['form']);

        return view('admin/cancellation_reasons/create');
    }

    public function store()
    {
        helper(['form']);

        if (! $this->validate([
            'name'     => 'required|min_length[3]|max_length[150]',
            'category' => 'required|in_list[prospect,apartado,both]',
        ])) {
            return view('admin/cancellation_reasons/create', ['validation' => $this->validator]);
        }

        $this->reasonModel->insert([
            'name'       => trim($this->request->getPost('name')),
            'category'   => $this->request->getPost('category'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
        ]);

        return redirect()->to('admin/cancellation-reasons')->with('success', 'Motivo agregado correctamente.');
    }

    public function edit(int $id): string
    {
        helper(['form']);
        $reason = $this->reasonModel->find($id);

        if (! $reason) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/cancellation_reasons/edit', ['reason' => $reason]);
    }

    public function update(int $id)
    {
        helper(['form']);
        $reason = $this->reasonModel->find($id);

        if (! $reason) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate([
            'name'     => 'required|min_length[3]|max_length[150]',
            'category' => 'required|in_list[prospect,apartado,both]',
        ])) {
            return view('admin/cancellation_reasons/edit', [
                'reason'     => $reason,
                'validation' => $this->validator,
            ]);
        }

        $this->reasonModel->update($id, [
            'name'       => trim($this->request->getPost('name')),
            'category'   => $this->request->getPost('category'),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?: 0),
        ]);

        return redirect()->to('admin/cancellation-reasons')->with('success', 'Motivo actualizado correctamente.');
    }

    public function delete(int $id)
    {
        $this->reasonModel->delete($id);

        return redirect()->to('admin/cancellation-reasons')->with('success', 'Motivo eliminado.');
    }
}
