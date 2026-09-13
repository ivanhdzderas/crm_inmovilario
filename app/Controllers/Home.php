<?php

namespace App\Controllers;

use App\Helpers\CatalogoHelper;
use App\Models\ClientDocumentCatalogModel;
use App\Models\ProyectosInmobiliariosModel;
use App\Models\RealEstateAmenitiesModel;
use App\Models\RealEstateModel;
use App\Models\RealEstatePhotosModel;
use App\Models\ZoneModel;
use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): string
    {
        $realEstateModel = new RealEstateModel();
        $zoneModel = new ZoneModel();
        $docCatalogModel = new ClientDocumentCatalogModel();
        $photoModel = new RealEstatePhotosModel();

        $filters = [
            'zone_id'        => $this->request->getGet('zone_id'),
            'min_price'      => $this->request->getGet('min_price'),
            'max_price'      => $this->request->getGet('max_price'),
            'payment_method' => $this->request->getGet('payment_method'),
        ];

        $results = $realEstateModel->getPublicCatalog($filters);

        foreach ($results as $key => $property) {
            $mainPhoto = $photoModel->where('real_estate_id', $property['id'])->orderBy('id', 'ASC')->first();
            $results[$key]['main_photo_path'] = $mainPhoto ? $mainPhoto['file_path'] : null;
            $results[$key]['project_slug'] = CatalogoHelper::slug($property['project_name'] ?? 'general');
            $results[$key]['slug'] = CatalogoHelper::slug($property['name'] ?? 'inmueble');
            $results[$key]['payment_methods_list'] = CatalogoHelper::parsePaymentMethods($property['payment_methods'] ?? null);
        }

        return view('home', [
            'realEstates'      => $results,
            'zones'            => $zoneModel->asArray()->orderBy('name', 'ASC')->findAll() ?: [],
            'payment_methods'  => $docCatalogModel->getPaymentMethods(),
            'current_filters'  => $filters,
        ]);
    }

    public function aboutUs()
    {
        return view('about_us', [
            'title' => 'Sobre Nosotros - Pacific Inmobiliaria',
        ]);
    }

    /** Modo catálogo / tablet */
    public function catalogo(): string
    {
        $propiedades = $this->enriquecerLista((new RealEstateModel())->getPublicCatalog());

        return view('catalogo/layout', [
            'title'        => 'Catálogo | Pacific Inmobiliaria',
            'content'      => view('catalogo/index', ['propiedades' => $propiedades]),
            'modoCatalogo' => true,
        ]);
    }

    public function catalogoInmueble(string $slugProyecto, string $slugInmueble): string|RedirectResponse
    {
        $propiedad = $this->resolverInmueble($slugProyecto, $slugInmueble);

        if ($redirect = $this->redirigirUrlCanonicaCatalogo($propiedad, $slugProyecto, $slugInmueble)) {
            return $redirect;
        }

        $detalle   = $this->cargarDetalle((int) $propiedad['id']);

        $todas  = $this->enriquecerLista((new RealEstateModel())->getPublicCatalog());
        $indice = $this->indiceLista($todas, $propiedad);

        return view('catalogo/layout', [
            'title'        => ($detalle['name'] ?? 'Inmueble') . ' | Catálogo',
            'content'      => view('catalogo/inmueble', [
                'realEstate' => $detalle,
                'anterior'   => $indice > 0 ? $todas[$indice - 1] : null,
                'siguiente'  => $indice < count($todas) - 1 ? $todas[$indice + 1] : null,
            ]),
            'modoCatalogo' => true,
        ]);
    }

    public function desarrollo(string $slugProyecto): string|RedirectResponse
    {
        $proyectoModel = new ProyectosInmobiliariosModel();
        $proyecto = $proyectoModel->getBySlug($slugProyecto);

        if ($proyecto === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $proyecto['slug'] = CatalogoHelper::slug($proyecto['name']);

        if (CatalogoHelper::requiereRedireccionCanonica($slugProyecto, $proyecto['slug'])) {
            return redirect()->to(CatalogoHelper::urlDesarrollo($proyecto), 301);
        }

        $propiedades = $this->enriquecerLista(
            (new RealEstateModel())->getPublicCatalog(['project_id' => $proyecto['id']])
        );

        return view('catalogo/desarrollo', [
            'title'       => $proyecto['name'] . ' | Pacific Inmobiliaria',
            'proyecto'    => $proyecto,
            'propiedades' => $propiedades,
        ]);
    }

    public function fichaDesarrollo(string $slugProyecto, string $slugInmueble): string|RedirectResponse
    {
        $propiedad = $this->resolverInmueble($slugProyecto, $slugInmueble);

        if ($redirect = $this->redirigirUrlCanonicaDesarrollo($propiedad, $slugProyecto, $slugInmueble)) {
            return $redirect;
        }

        return view('real_estates/public_technical_sheet', [
            'realEstate' => $this->cargarDetalle((int) $propiedad['id']),
        ]);
    }

    public function publicTechnicalSheet($id = null)
    {
        if ($id === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $realEstate = (new RealEstateModel())->getRealEstateWithDetails($id);

        if (! $realEstate) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $detalle = $this->cargarDetalle((int) $realEstate['id']);

        if (! empty($detalle['project_name'])) {
            return redirect()->to(CatalogoHelper::urlInmueble($detalle));
        }

        return view('real_estates/public_technical_sheet', ['realEstate' => $detalle]);
    }

    private function cargarDetalle(int $id): array
    {
        $realEstate = (new RealEstateModel())->getRealEstateWithDetails($id);

        if (! $realEstate) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $realEstate['amenities'] = (new RealEstateAmenitiesModel())->where('real_estate_id', $id)->findAll();
        $realEstate['photos'] = (new RealEstatePhotosModel())->where('real_estate_id', $id)->findAll();
        $realEstate['payment_methods_list'] = CatalogoHelper::parsePaymentMethods($realEstate['payment_methods'] ?? null);
        $realEstate['project_slug'] = CatalogoHelper::slug($realEstate['project_name'] ?? 'general');
        $realEstate['slug'] = CatalogoHelper::slug($realEstate['name'] ?? 'inmueble');

        return $realEstate;
    }

    private function resolverInmueble(string $slugProyecto, string $slugInmueble): array
    {
        $slugProyectoNorm = CatalogoHelper::normalizeSegment($slugProyecto);
        $slugInmuebleNorm = CatalogoHelper::normalizeSegment($slugInmueble);

        $realEstateModel = new RealEstateModel();
        $proyectoModel   = new ProyectosInmobiliariosModel();
        $proyecto        = $proyectoModel->getBySlug($slugProyectoNorm);

        if ($proyecto !== null) {
            foreach ($realEstateModel->getPublicCatalog(['project_id' => $proyecto['id']]) as $item) {
                if ($this->coincideSlugInmueble($item, $slugInmuebleNorm)) {
                    return $item;
                }
            }
        }

        foreach ($realEstateModel->getPublicCatalog() as $item) {
            if (! $this->coincideSlugInmueble($item, $slugInmuebleNorm)) {
                continue;
            }

            $projectSlug = CatalogoHelper::slug($item['project_name'] ?? 'general');
            if ($projectSlug === $slugProyectoNorm || $this->slugProyectoEquivalente($projectSlug, $slugProyectoNorm)) {
                return $item;
            }
        }

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    private function redirigirUrlCanonicaDesarrollo(array $propiedad, string $slugProyecto, string $slugInmueble): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $propiedad['project_slug'] = CatalogoHelper::slug($propiedad['project_name'] ?? 'general');
        $propiedad['slug'] = CatalogoHelper::slug($propiedad['name'] ?? 'inmueble');

        if (
            CatalogoHelper::requiereRedireccionCanonica($slugProyecto, $propiedad['project_slug'])
            || CatalogoHelper::requiereRedireccionCanonica($slugInmueble, $propiedad['slug'])
        ) {
            return redirect()->to(CatalogoHelper::urlInmueble($propiedad), 301);
        }

        return null;
    }

    private function redirigirUrlCanonicaCatalogo(array $propiedad, string $slugProyecto, string $slugInmueble): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $propiedad['project_slug'] = CatalogoHelper::slug($propiedad['project_name'] ?? 'general');
        $propiedad['slug'] = CatalogoHelper::slug($propiedad['name'] ?? 'inmueble');

        if (
            CatalogoHelper::requiereRedireccionCanonica($slugProyecto, $propiedad['project_slug'])
            || CatalogoHelper::requiereRedireccionCanonica($slugInmueble, $propiedad['slug'])
        ) {
            return redirect()->to(CatalogoHelper::urlCatalogoInmueble($propiedad), 301);
        }

        return null;
    }

    private function coincideSlugInmueble(array $item, string $slugInmueble): bool
    {
        if (CatalogoHelper::coincideSlug($item['name'] ?? '', $slugInmueble)) {
            return true;
        }

        if (! empty($item['clave']) && CatalogoHelper::coincideSlug($item['clave'], $slugInmueble)) {
            return true;
        }

        return false;
    }

    private function slugProyectoEquivalente(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $prefijo = 'desarrollo-';

        return ($prefijo . $a === $b) || ($a === $prefijo . $b);
    }

    /**
     * @param list<array<string, mixed>> $lista
     * @return list<array<string, mixed>>
     */
    private function enriquecerLista(array $lista): array
    {
        $photoModel = new RealEstatePhotosModel();

        foreach ($lista as &$item) {
            $item['project_slug'] = CatalogoHelper::slug($item['project_name'] ?? 'general');
            $item['slug'] = CatalogoHelper::slug($item['name'] ?? 'inmueble');
            $item['payment_methods_list'] = CatalogoHelper::parsePaymentMethods($item['payment_methods'] ?? null);

            $foto = $photoModel->where('real_estate_id', $item['id'])->orderBy('id', 'ASC')->first();
            $item['main_photo_path'] = $foto['file_path'] ?? null;
        }

        return $lista;
    }

    /** @param list<array<string, mixed>> $lista */
    private function indiceLista(array $lista, array $actual): int
    {
        foreach ($lista as $i => $item) {
            if ((int) $item['id'] === (int) $actual['id']) {
                return $i;
            }
        }

        return 0;
    }
}
