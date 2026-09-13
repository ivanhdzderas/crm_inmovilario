<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AcquisitionSourceModel;

class AcquisitionSourcesController extends BaseController
{
    protected AcquisitionSourceModel $sourceModel;

    public function __construct()
    {
        $this->sourceModel = new AcquisitionSourceModel();
    }

    public function index(): string
    {
        return view('admin/acquisition_sources/index', [
            'sources' => $this->sourceModel->orderBy('sort_order', 'ASC')->findAll(),
            'channelLabels' => AcquisitionSourceModel::CHANNEL_LABELS,
        ]);
    }

    public function create(): string
    {
        helper(['form']);

        return view('admin/acquisition_sources/form', [
            'source' => null,
            'channelLabels' => AcquisitionSourceModel::CHANNEL_LABELS,
        ]);
    }

    public function store()
    {
        helper(['form']);

        if (! $this->validate([
            'name'         => 'required|min_length[2]|max_length[120]|is_unique[acquisition_sources.name]',
            'channel_type' => 'required|in_list[social,web,referral,offline,other]',
        ])) {
            return view('admin/acquisition_sources/form', [
                'source' => null,
                'channelLabels' => AcquisitionSourceModel::CHANNEL_LABELS,
                'validation' => $this->validator,
            ]);
        }

        $this->sourceModel->insert([
            'name'         => trim($this->request->getPost('name')),
            'channel_type' => $this->request->getPost('channel_type'),
            'icon_class'   => trim($this->request->getPost('icon_class') ?? '') ?: null,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order'   => (int) ($this->request->getPost('sort_order') ?: 0),
        ]);

        return redirect()->to('admin/acquisition-sources')->with('success', 'Canal de origen agregado.');
    }

    public function edit(int $id): string
    {
        helper(['form']);
        $source = $this->sourceModel->find($id);
        if (! $source) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/acquisition_sources/form', [
            'source' => $source,
            'channelLabels' => AcquisitionSourceModel::CHANNEL_LABELS,
        ]);
    }

    public function update(int $id)
    {
        helper(['form']);
        $source = $this->sourceModel->find($id);
        if (! $source) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'name'         => "required|min_length[2]|max_length[120]|is_unique[acquisition_sources.name,id,{$id}]",
            'channel_type' => 'required|in_list[social,web,referral,offline,other]',
        ];

        if (! $this->validate($rules)) {
            return view('admin/acquisition_sources/form', [
                'source' => $source,
                'channelLabels' => AcquisitionSourceModel::CHANNEL_LABELS,
                'validation' => $this->validator,
            ]);
        }

        $this->sourceModel->update($id, [
            'name'         => trim($this->request->getPost('name')),
            'channel_type' => $this->request->getPost('channel_type'),
            'icon_class'   => trim($this->request->getPost('icon_class') ?? '') ?: null,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
            'sort_order'   => (int) ($this->request->getPost('sort_order') ?: 0),
        ]);

        return redirect()->to('admin/acquisition-sources')->with('success', 'Canal actualizado.');
    }

    public function delete(int $id)
    {
        $this->sourceModel->delete($id);

        return redirect()->to('admin/acquisition-sources')->with('success', 'Canal eliminado.');
    }
}
