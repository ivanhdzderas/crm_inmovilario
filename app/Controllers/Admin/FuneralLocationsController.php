<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FuneralHomeModel;
use App\Models\FuneralLocationModel;
use App\Models\ZoneModel;

class FuneralLocationsController extends BaseController
{
    public function index(): string
    {
        $model = new FuneralLocationModel();

        return view('admin/funeral_locations/index', [
            'locations' => $model->select('funeral_locations.*, zones.name as zone_name')
                ->join('zones', 'zones.id = funeral_locations.zone_id', 'left')
                ->orderBy('funeral_locations.name', 'ASC')
                ->findAll(),
        ]);
    }

    public function create(): string
    {
        helper(['form']);

        return view('admin/funeral_locations/form', [
            'location'    => null,
            'zones'       => (new ZoneModel())->orderBy('name', 'ASC')->findAll(),
            'allHomes'    => (new FuneralHomeModel())->orderBy('name', 'ASC')->findAll(),
            'linkedHomes' => [],
        ]);
    }

    public function store()
    {
        $model = new FuneralLocationModel();
        $id = $model->insert([
            'name'        => $this->request->getPost('name'),
            'address'     => $this->request->getPost('address'),
            'zone_id'     => $this->request->getPost('zone_id') ?: null,
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ], true);

        $model->syncHomes((int) $id, $this->request->getPost('funeral_home_ids') ?? []);

        return redirect()->to('admin/funeral-locations')->with('success', 'Lugar de descanso creado.');
    }

    public function edit(int $id): string
    {
        $model = new FuneralLocationModel();
        $location = $model->find($id);
        if (! $location) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $linked = array_column($model->getHomesForLocation($id), 'id');

        helper(['form']);

        return view('admin/funeral_locations/form', [
            'location'    => $location,
            'zones'       => (new ZoneModel())->orderBy('name', 'ASC')->findAll(),
            'allHomes'    => (new FuneralHomeModel())->orderBy('name', 'ASC')->findAll(),
            'linkedHomes' => $linked,
        ]);
    }

    public function update(int $id)
    {
        $model = new FuneralLocationModel();
        if (! $model->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $model->update($id, [
            'name'        => $this->request->getPost('name'),
            'address'     => $this->request->getPost('address'),
            'zone_id'     => $this->request->getPost('zone_id') ?: null,
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        $model->syncHomes($id, $this->request->getPost('funeral_home_ids') ?? []);

        return redirect()->to('admin/funeral-locations')->with('success', 'Lugar actualizado.');
    }

    public function delete(int $id)
    {
        (new FuneralLocationModel())->delete($id);

        return redirect()->to('admin/funeral-locations')->with('success', 'Lugar eliminado.');
    }
}
