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
    /** Portada: presenta las dos líneas (inmuebles y previsión funeraria) con destacados. */
    public function index()
    {
        // Compatibilidad: los filtros antes vivían en la portada
        $query = $this->request->getUri()->getQuery();
        if ($query !== '' && array_intersect(array_keys($this->request->getGet() ?? []), ['zone_id', 'min_price', 'max_price', 'payment_method'])) {
            return redirect()->to(site_url('inmuebles') . '?' . $query, 301);
        }

        $inmuebles = $this->enriquecerLista((new RealEstateModel())->getPublicCatalog());
        usort($inmuebles, static fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));

        $paquetes = array_slice((new \App\Models\FuneralProductModel())->getPublicCatalog([]), 0, 50);
        $planModel = new \App\Models\FuneralFinancingPlanModel();
        foreach (array_slice($paquetes, 0, 3) as $i => $p) {
            $paquetes[$i]['plans'] = $planModel->getByProduct((int) $p['id'], true);
        }

        return view('home', [
            'title'          => sitio('site_name') . ' | Inmuebles y previsión funeraria',
            'destacados'     => array_slice($inmuebles, 0, 6),
            'totalInmuebles' => count($inmuebles),
            'paquetes'       => array_slice($paquetes, 0, 3),
            'totalPaquetes'  => count($paquetes),
            'zonas'          => (new ZoneModel())->asArray()->orderBy('name', 'ASC')->findAll() ?: [],
        ]);
    }

    /** Listado de inmuebles con filtros. */
    public function inmuebles(): string
    {
        $filtros = [
            'zone_id'        => (int) $this->request->getGet('zone_id') ?: null,
            'project_id'     => (int) $this->request->getGet('project_id') ?: null,
            'min_price'      => is_numeric($this->request->getGet('min_price')) ? (float) $this->request->getGet('min_price') : null,
            'max_price'      => is_numeric($this->request->getGet('max_price')) ? (float) $this->request->getGet('max_price') : null,
            // Se filtra por el nombre de la forma de pago (así se guarda en el inmueble)
            'payment_method' => trim((string) $this->request->getGet('payment_method')) ?: null,
        ];
        $orden = (string) $this->request->getGet('orden');

        $lista = $this->enriquecerLista((new RealEstateModel())->getPublicCatalog(array_filter($filtros, static fn ($v) => $v !== null)));
        if ($orden === 'precio_asc') {
            usort($lista, static fn ($a, $b) => $a['price'] <=> $b['price']);
        } elseif ($orden === 'precio_desc') {
            usort($lista, static fn ($a, $b) => $b['price'] <=> $a['price']);
        } elseif ($orden === 'recientes') {
            usort($lista, static fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));
        }

        $formas = array_values(array_filter(
            (new ClientDocumentCatalogModel())->getPaymentMethods(),
            static fn ($m) => mb_strtolower((string) $m) !== 'todos'
        ));

        return view('inmuebles', [
            'title'      => 'Inmuebles en venta | ' . sitio('site_name'),
            'inmuebles'  => $lista,
            'zonas'      => (new ZoneModel())->asArray()->orderBy('name', 'ASC')->findAll() ?: [],
            'proyectos'  => (new ProyectosInmobiliariosModel())->asArray()->orderBy('name', 'ASC')->findAll() ?: [],
            'formasPago' => $formas,
            'filtros'    => $filtros,
            'orden'      => $orden,
        ]);
    }

    public function aboutUs()
    {
        return view('about_us', [
            'title' => 'Nosotros | ' . sitio('site_name'),
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

        return $this->vistaFicha($this->cargarDetalle((int) $propiedad['id']));
    }

    public function publicTechnicalSheet($id = null)
    {
        if ($id === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Solo inmuebles en venta (no vendidos ni dados de baja)
        $realEstate = (new RealEstateModel())->getPublicCatalogQuery()->where('real_estates.id', (int) $id)->first();

        if (! $realEstate) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $detalle = $this->cargarDetalle((int) $realEstate['id']);

        if (! empty($detalle['project_name'])) {
            return redirect()->to(CatalogoHelper::urlInmueble($detalle));
        }

        return $this->vistaFicha($detalle);
    }

    /** Ficha pública con título, descripción y foto para compartir en redes. */
    private function vistaFicha(array $detalle): string
    {
        $foto = $detalle['photos'][0]['file_path'] ?? null;

        return view('real_estates/public_technical_sheet', [
            'realEstate'  => $detalle,
            'title'       => $detalle['name'] . ' | ' . sitio('site_name'),
            'descripcion' => character_limiter(strip_tags((string) ($detalle['description_general'] ?? '')), 155) ?: null,
            'ogImagen'    => $foto ? base_url($foto) : null,
        ]);
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
