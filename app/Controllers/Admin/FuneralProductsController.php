<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\FuneralFinancingPlanModel;
use App\Models\FuneralHomeModel;
use App\Models\FuneralLocationModel;
use App\Models\FuneralProductModel;
use App\Models\FuneralProductTypeModel;

class FuneralProductsController extends BaseController
{
    public function index(): string
    {
        return view('admin/funeral_products/index', [
            'products' => (new FuneralProductModel())->getAdminList(),
        ]);
    }

    public function create(): string
    {
        return $this->form(null, []);
    }

    public function store()
    {
        $productModel = new FuneralProductModel();
        $id = $productModel->insert([
            'clave'               => $this->request->getPost('clave'),
            'name'                => $this->request->getPost('name'),
            'funeral_location_id' => $this->request->getPost('funeral_location_id'),
            'funeral_home_id'     => $this->request->getPost('funeral_home_id'),
            'product_type_id'     => $this->request->getPost('product_type_id') ?: null,
            'base_price'          => $this->request->getPost('base_price') ?: 0,
            'description'         => $this->request->getPost('description'),
            'status'              => $this->request->getPost('status') ?: 'Disponible',
        ], true);

        $this->savePlans((int) $id);

        return redirect()->to('admin/funeral-products')->with('success', 'Paquete funerario creado.');
    }

    public function edit(int $id): string
    {
        $product = (new FuneralProductModel())->find($id);
        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $plans = (new FuneralFinancingPlanModel())->getByProduct($id);

        return $this->form($product, $plans);
    }

    public function update(int $id)
    {
        $productModel = new FuneralProductModel();
        if (! $productModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $productModel->update($id, [
            'clave'               => $this->request->getPost('clave'),
            'name'                => $this->request->getPost('name'),
            'funeral_location_id' => $this->request->getPost('funeral_location_id'),
            'funeral_home_id'     => $this->request->getPost('funeral_home_id'),
            'product_type_id'     => $this->request->getPost('product_type_id') ?: null,
            'base_price'          => $this->request->getPost('base_price') ?: 0,
            'description'         => $this->request->getPost('description'),
            'status'              => $this->request->getPost('status') ?: 'Disponible',
        ]);

        $this->savePlans($id);

        return redirect()->to('admin/funeral-products')->with('success', 'Paquete actualizado.');
    }

    public function delete(int $id)
    {
        (new FuneralProductModel())->delete($id);

        return redirect()->to('admin/funeral-products')->with('success', 'Paquete eliminado.');
    }

    public function homesByLocation(int $locationId)
    {
        return $this->response->setJSON([
            'success' => true,
            'homes'   => (new FuneralHomeModel())->getByLocation($locationId),
        ]);
    }

    private function form(?array $product, array $plans): string
    {
        helper(['form']);

        return view('admin/funeral_products/form', [
            'product'      => $product,
            'plans'        => $plans,
            'locations'    => (new FuneralLocationModel())->orderBy('name', 'ASC')->findAll(),
            'homes'        => (new FuneralHomeModel())->getActivas(),
            'productTypes' => (new FuneralProductTypeModel())->getAllOrdered(),
        ]);
    }

    private function savePlans(int $productId): void
    {
        $names = $this->request->getPost('plan_name') ?? [];
        $enganches = $this->request->getPost('plan_enganche') ?? [];
        $mensualidades = $this->request->getPost('plan_mensualidad') ?? [];
        $months = $this->request->getPost('plan_months') ?? [];

        $plans = [];
        foreach ($names as $i => $name) {
            if (trim((string) $name) === '') {
                continue;
            }
            $plans[] = [
                'name'        => $name,
                'enganche'    => $enganches[$i] ?? 0,
                'mensualidad' => $mensualidades[$i] ?? 0,
                'months'      => $months[$i] ?? 12,
                'sort_order'  => ($i + 1) * 10,
            ];
        }

        (new FuneralFinancingPlanModel())->replaceForProduct($productId, $plans);
    }
}
