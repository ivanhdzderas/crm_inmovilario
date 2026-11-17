<?php

namespace App\Controllers;

use App\Helpers\CatalogoFunerarioHelper;
use App\Models\FuneralFinancingPlanModel;
use App\Models\FuneralHomeModel;
use App\Models\FuneralLocationModel;
use App\Models\FuneralProductModel;
use App\Models\FuneralProductTypeModel;

class FuneralCatalog extends BaseController
{
    public function index(): string
    {
        helper('funerario');

        $productModel = new FuneralProductModel();
        $filters = [
            'location_id'     => $this->request->getGet('location_id'),
            'home_id'         => $this->request->getGet('home_id'),
            'product_type_id' => $this->request->getGet('type_id'),
        ];

        $productos = $productModel->getPublicCatalog($filters);
        $planModel = new FuneralFinancingPlanModel();
        foreach ($productos as &$p) {
            $p['plans'] = $planModel->getByProduct((int) $p['id'], true);
            $p['min_mensualidad'] = null;
            foreach ($p['plans'] as $plan) {
                if ($p['min_mensualidad'] === null || $plan['mensualidad'] < $p['min_mensualidad']) {
                    $p['min_mensualidad'] = $plan['mensualidad'];
                }
            }
        }

        return view('funerario/index', [
            'title'      => 'Previsión funeraria | ' . sitio('site_name'),
            'productos'  => $productos,
            'locations'  => (new FuneralLocationModel())->getPublicos(),
            'homes'      => (new FuneralHomeModel())->getActivas(),
            'types'      => (new FuneralProductTypeModel())->getAllOrdered(),
            'filters'    => $filters,
        ]);
    }

    public function lugar(string $slug): string
    {
        helper('funerario');

        $locationModel = new FuneralLocationModel();
        $lugar = $locationModel->getBySlug($slug);
        if (! $lugar) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $funerarias = $locationModel->getHomesForLocation((int) $lugar['id']);
        $productos  = (new FuneralProductModel())->getPublicCatalog(['location_id' => $lugar['id']]);
        $planModel  = new FuneralFinancingPlanModel();
        foreach ($productos as &$p) {
            $p['plans'] = $planModel->getByProduct((int) $p['id'], true);
        }

        return view('funerario/lugar', [
            'title'      => $lugar['name'] . ' | Funerario',
            'lugar'      => $lugar,
            'funerarias' => $funerarias,
            'productos'  => $productos,
        ]);
    }

    public function producto(string $slugLugar, string $slugFuneraria, string $slugProducto): string
    {
        helper('funerario');

        $productModel = new FuneralProductModel();
        $producto = $productModel->findPublicBySlugs($slugLugar, $slugFuneraria, $slugProducto);
        if (! $producto) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $planes = (new FuneralFinancingPlanModel())->getByProduct((int) $producto['id'], true);
        foreach ($planes as &$plan) {
            $plan['total_estimado'] = CatalogoFunerarioHelper::totalPlan($plan);
        }

        return view('funerario/producto', [
            'title'    => $producto['name'] . ' | ' . sitio('site_name'),
            'ogImagen' => ! empty($producto['image_path']) ? base_url($producto['image_path']) : null,
            'producto' => $producto,
            'planes'   => $planes,
        ]);
    }
}
