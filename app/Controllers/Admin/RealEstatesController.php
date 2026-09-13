<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RealEstateModel;
use App\Models\ProyectosInmobiliariosModel;
use App\Models\RealEstatePhotosModel;
use App\Models\RealEstateAmenitiesModel;
use App\Models\ConfigurationModel;
use Dompdf\Dompdf; // Asegúrate de haber instalado dompdf/dompdf

class RealEstatesController extends BaseController
{
    /**
     * Convierte una liga de Google Maps con dirección en una liga con coordenadas cuando es posible.
     * Formato de salida esperado: https://maps.google.com/maps?q=lat,lng
     */
    private function normalizeGoogleMapsLink(?string $mapsLink, ?string $addressFallback = null): ?string
    {
        $mapsLink = trim((string) $mapsLink);
        if ($mapsLink === '') {
            return null;
        }

        // Si ya tiene coordenadas, devolverla tal cual.
        if ($this->extractCoordinatesFromMapsLink($mapsLink) !== null) {
            return $mapsLink;
        }

        $queryText = '';
        $query = parse_url($mapsLink, PHP_URL_QUERY);
        if (!empty($query)) {
            parse_str($query, $params);
            $queryText = trim((string) ($params['q'] ?? ($params['ll'] ?? '')));
        }

        if ($queryText === '') {
            $queryText = trim((string) $addressFallback);
        }

        if ($queryText === '') {
            return $mapsLink;
        }

        $coords = $this->geocodeAddressToCoordinates($queryText);
        if ($coords === null) {
            return $mapsLink;
        }

        return 'https://maps.google.com/maps?q=' . $coords;
    }

    /**
     * Extrae coordenadas de una liga de mapas.
     */
    private function extractCoordinatesFromMapsLink(string $mapsLink): ?string
    {
        if (preg_match('/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/', $mapsLink, $m)) {
            return $m[1] . ',' . $m[2];
        }

        $query = parse_url($mapsLink, PHP_URL_QUERY);
        if (!empty($query)) {
            parse_str($query, $params);
            $candidate = trim((string) ($params['q'] ?? ($params['ll'] ?? '')));
            if (preg_match('/^-?\d{1,3}\.\d+\s*,\s*-?\d{1,3}\.\d+$/', $candidate)) {
                return preg_replace('/\s+/', '', $candidate);
            }
        }

        if (preg_match('/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/', $mapsLink, $m)) {
            return $m[1] . ',' . $m[2];
        }

        return null;
    }

    /**
     * Geocodifica una dirección con Nominatim (OpenStreetMap).
     */
    private function geocodeAddressToCoordinates(string $address): ?string
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 5,
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Tony-CRM/1.0 (Geocoding Real Estates)',
                    'Accept' => 'application/json',
                ],
            ]);

            $response = $client->get('https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ],
            ]);

            if ((int) $response->getStatusCode() !== 200) {
                return null;
            }

            $payload = json_decode((string) $response->getBody(), true);
            if (!is_array($payload) || empty($payload[0]['lat']) || empty($payload[0]['lon'])) {
                return null;
            }

            $lat = trim((string) $payload[0]['lat']);
            $lon = trim((string) $payload[0]['lon']);

            if ($lat === '' || $lon === '') {
                return null;
            }

            return $lat . ',' . $lon;
        } catch (\Throwable $e) {
            log_message('error', 'Geocoding maps link falló: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Filtra fotos válidas y pone al inicio la portada elegida por índice.
     */
    private function getOrderedUploadedPhotos(array $uploadedPhotos, ?int $coverIndex = null): array
    {
        $validPhotos = [];

        foreach ($uploadedPhotos as $index => $file) {
            if (
                $file
                && $file->isValid()
                && !$file->hasMoved()
                && strpos((string) $file->getMimeType(), 'image/') === 0
            ) {
                $validPhotos[$index] = $file;
            }
        }

        if (empty($validPhotos)) {
            return [];
        }

        if ($coverIndex !== null && array_key_exists($coverIndex, $validPhotos)) {
            $coverPhoto = $validPhotos[$coverIndex];
            unset($validPhotos[$coverIndex]);
            return array_merge([$coverPhoto], array_values($validPhotos));
        }

        return array_values($validPhotos);
    }

    /**
     * Guarda fotos del inmueble respetando el orden para portada.
     */
    private function saveUploadedPhotos(int $realEstateId, array $uploadedPhotos, ?int $coverIndex = null): void
    {
        if (empty($uploadedPhotos)) {
            return;
        }

        if (!is_dir('uploads/real_estate_photos')) {
            mkdir('uploads/real_estate_photos', 0755, true);
        }

        $realEstatePhotosModel = new \App\Models\RealEstatePhotosModel();
        $orderedPhotos = $this->getOrderedUploadedPhotos($uploadedPhotos, $coverIndex);

        foreach ($orderedPhotos as $file) {
            $newName = $file->getRandomName();
            $file->move('uploads/real_estate_photos', $newName);

            $realEstatePhotosModel->insert([
                'real_estate_id' => $realEstateId,
                'file_path'      => 'uploads/real_estate_photos/' . $newName,
            ]);
        }
    }

    /**
     * Aplica borrado y reordenamiento de fotos existentes sin cambiar estructura de BD.
     */
    private function manageExistingPhotos(int $realEstateId, array $orderedPhotoIds = [], array $photoIdsToDelete = []): void
    {
        $realEstatePhotosModel = new \App\Models\RealEstatePhotosModel();
        $existingPhotos = $realEstatePhotosModel
            ->where('real_estate_id', $realEstateId)
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($existingPhotos)) {
            return;
        }

        $photosById = [];
        $currentOrderIds = [];
        foreach ($existingPhotos as $photo) {
            $photoId = (int) $photo['id'];
            $photosById[$photoId] = $photo;
            $currentOrderIds[] = $photoId;
        }

        $validDeleteIds = [];
        foreach ($photoIdsToDelete as $photoId) {
            $photoId = (int) $photoId;
            if (isset($photosById[$photoId])) {
                $validDeleteIds[] = $photoId;
            }
        }

        if (!empty($validDeleteIds)) {
            foreach ($validDeleteIds as $photoId) {
                $photo = $photosById[$photoId];
                $filePath = ROOTPATH . 'public/' . $photo['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $realEstatePhotosModel->delete($photoId);
                unset($photosById[$photoId]);
            }

            $currentOrderIds = array_values(array_filter(
                $currentOrderIds,
                static fn ($id) => isset($photosById[(int) $id])
            ));
        }

        if (empty($photosById)) {
            return;
        }

        if (empty($orderedPhotoIds)) {
            return;
        }

        $validOrderedIds = [];
        foreach ($orderedPhotoIds as $photoId) {
            $photoId = (int) $photoId;
            if (isset($photosById[$photoId]) && !in_array($photoId, $validOrderedIds, true)) {
                $validOrderedIds[] = $photoId;
            }
        }

        foreach ($currentOrderIds as $photoId) {
            if (!in_array($photoId, $validOrderedIds, true)) {
                $validOrderedIds[] = $photoId;
            }
        }

        if ($validOrderedIds === $currentOrderIds) {
            return;
        }

        $orderedPaths = [];
        foreach ($validOrderedIds as $photoId) {
            $orderedPaths[] = $photosById[$photoId]['file_path'];
        }

        $realEstatePhotosModel->where('real_estate_id', $realEstateId)->delete();
        foreach ($orderedPaths as $filePath) {
            $realEstatePhotosModel->insert([
                'real_estate_id' => $realEstateId,
                'file_path'      => $filePath,
            ]);
        }
    }

    public function index()
    {
        $realEstateModel = new RealEstateModel();
        // Obtenemos todos los inmuebles con sus proyectos asociados
        $realEstates = $realEstateModel->getAllWithProjects();

        // Instanciamos el modelo de fotos para obtener las imágenes
        $realEstatePhotosModel = new RealEstatePhotosModel();

        // Iteramos sobre cada inmueble para obtener la primera foto
        foreach ($realEstates as &$realEstate) {
            $mainPhoto = $realEstatePhotosModel
                ->where('real_estate_id', $realEstate['id'])
                ->orderBy('id', 'ASC')
                ->first();
            
            // Agregamos la ruta de la foto principal al array del inmueble
            if ($mainPhoto) {
                $realEstate['main_photo_path'] = $mainPhoto['file_path'];
            } else {
                $realEstate['main_photo_path'] = null;
            }
        }
        
        $data['realEstates'] = $realEstates;
        
        return view('admin/real_estates/index', $data);
    }
    
    public function new()
    {
        helper(['form', 'url']); // Cargar 'url' para usar url_title() en la vista
        
        $proyectosModel = new ProyectosInmobiliariosModel();
        $data['proyectos'] = $proyectosModel->findAll();
        
        $zoneModel = new \App\Models\ZoneModel();
        $data['zones'] = $zoneModel->findAll();

        $docCatalogModel = new \App\Models\ClientDocumentCatalogModel();
        $data['payment_methods_catalog'] = $docCatalogModel->getPaymentMethods();
        
        // Cargar usuarios para el dropdown de quién trajo el inmueble
        $userModel = new \App\Models\UserModel();
        $data['users'] = $userModel->findAll();
        
        return view('admin/real_estates/new', $data);
    }
    
    public function create()
    {
        helper(['form']);
        $rules = [
            'clave' => 'required|is_unique[real_estates.clave]',
            'name'  => 'required',
            'price' => 'required|numeric',
        ];

        if ($this->validate($rules)) {
            $realEstateModel = new RealEstateModel();
            
            $paymentMethods = $this->request->getPost('payment_methods');
            $googleMapsLink = $this->normalizeGoogleMapsLink(
                $this->request->getPost('google_maps_link'),
                $this->request->getPost('address')
            );

            $data = [
                'clave'               => $this->request->getPost('clave'),
                'name'                => $this->request->getPost('name'),
                'description_general' => $this->request->getPost('description_general'),
                'is_new'              => $this->request->getPost('is_new') ? 1 : 0,
                'price'               => $this->request->getPost('price'),
                'payment_methods'     => !empty($paymentMethods) ? json_encode($paymentMethods) : null,
                'google_maps_link'    => $googleMapsLink,
                'project_id'          => $this->request->getPost('project_id'),
                'zone_id'             => $this->request->getPost('zone_id') ?: null,
                'created_by_user_id'  => session()->get('user_id'),
                'updated_by_user_id'  => session()->get('user_id'),
            ];
            
            $newRealEstateId = $realEstateModel->insert($data, true);
            $amenitiesModel = new \App\Models\RealEstateAmenitiesModel();
            $amenityTypes = $this->request->getPost('amenity_type');
            $amenityValues = $this->request->getPost('amenity_value');

            if (!empty($amenityTypes) && is_array($amenityTypes)) {
                foreach ($amenityTypes as $key => $type) {
                    if (!empty($type) && isset($amenityValues[$key]) && !empty($amenityValues[$key])) {
                        $amenityData = [
                            'real_estate_id' => $newRealEstateId,
                            'amenity_type'   => $type,
                            'value'          => $amenityValues[$key],
                        ];
                        $amenitiesModel->insert($amenityData);
                    }
                }
            }

            // Procesamiento de imágenes (Aseguramos carpeta y validamos tipo)
            $files = $this->request->getFiles();
            if ($files && isset($files['photos'])) {
                $coverPhotoIndexRaw = $this->request->getPost('cover_photo_index');
                $coverPhotoIndex = is_numeric($coverPhotoIndexRaw) ? (int) $coverPhotoIndexRaw : null;
                $uploadedPhotos = is_array($files['photos']) ? $files['photos'] : [$files['photos']];
                $this->saveUploadedPhotos($newRealEstateId, $uploadedPhotos, $coverPhotoIndex);
            }

            return redirect()->to('admin/real_estates')->with('success', 'Inmueble agregado correctamente.');
        } else {
            $data['proyectos'] = (new \App\Models\ProyectosInmobiliariosModel())->findAll();
            $data['zones'] = (new \App\Models\ZoneModel())->findAll();
            $data['payment_methods_catalog'] = (new \App\Models\ClientDocumentCatalogModel())->getPaymentMethods();
            $data['users'] = (new \App\Models\UserModel())->findAll();
            $data['validation'] = $this->validator;
            return view('admin/real_estates/new', $data);
        }
    }
    
    public function edit($id = null)
    {
        helper(['form', 'url']); // Cargar 'url' para usar url_title() en la vista
        
        $realEstatesModel = new \App\Models\RealEstateModel();
        $proyectosModel = new \App\Models\ProyectosInmobiliariosModel();
        
        $data['realEstate'] = $realEstatesModel->find($id);
        
        if (empty($data['realEstate'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se puede encontrar el inmueble con el ID: ' . $id);
        }
        $data['proyectos'] = $proyectosModel->findAll();
        
        $zoneModel = new \App\Models\ZoneModel();
        $data['zones'] = $zoneModel->findAll();

        $docCatalogModel = new \App\Models\ClientDocumentCatalogModel();
        $data['payment_methods_catalog'] = $docCatalogModel->getPaymentMethods();
        
        // Cargar usuarios para el dropdown de quién trajo el inmueble
        $userModel = new \App\Models\UserModel();
        $data['users'] = $userModel->findAll();
        
        $realEstatePhotosModel = new \App\Models\RealEstatePhotosModel();
        $data['photos'] = $realEstatePhotosModel->where('real_estate_id', $id)->orderBy('id', 'ASC')->findAll();
        
        $amenitiesModel = new \App\Models\RealEstateAmenitiesModel();
        $data['amenities'] = $amenitiesModel->where('real_estate_id', $id)->findAll();
        
        return view('admin/real_estates/edit', $data);
    }
    
    public function delete_photo($id = null)
    {
        $realEstatePhotosModel = new \App\Models\RealEstatePhotosModel();
        $photo = $realEstatePhotosModel->find($id);

        if ($photo) {
            $filePath = ROOTPATH . 'public/' . $photo['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $realEstatePhotosModel->delete($id);
            return redirect()->back()->with('success', 'Foto eliminada correctamente.');
        }
        return redirect()->back()->with('error', 'No se pudo encontrar la foto.');
    }
    
    public function update($id = null)
    {
        $realEstateModel = new RealEstateModel();
        $realEstate = $realEstateModel->find($id);

        if (!$realEstate) {
            return redirect()->to('admin/real_estates')->with('error', 'Inmueble no encontrado.');
        }

        helper(['form']);
        
        $rules = [
            'id'    => 'required|numeric',
            'clave' => "required|is_unique[real_estates.clave,id,{$id}]",
            'name'  => 'required',
            'price' => 'required|numeric',
        ];

        if ($this->validate($rules)) {
            $paymentMethods = $this->request->getPost('payment_methods');
            $googleMapsLink = $this->normalizeGoogleMapsLink(
                $this->request->getPost('google_maps_link'),
                $this->request->getPost('address')
            );

            $data = [
                'clave'               => $this->request->getPost('clave'),
                'name'                => $this->request->getPost('name'),
                'description_general' => $this->request->getPost('description_general'),
                'is_new'              => $this->request->getPost('is_new') ? 1 : 0,
                'status'              => $this->request->getPost('status'),
                'price'               => $this->request->getPost('price'),
                'payment_methods'     => !empty($paymentMethods) ? json_encode($paymentMethods) : null,
                'google_maps_link'    => $googleMapsLink,
                'project_id'          => $this->request->getPost('project_id'),
                'zone_id'             => $this->request->getPost('zone_id') ?: null,
                'updated_by_user_id'  => session()->get('user_id'),
            ];

            $realEstateModel->update($id, $data);

            // Lógica para guardar las amenidades
            $amenitiesModel = new \App\Models\RealEstateAmenitiesModel();
            $amenitiesModel->where('real_estate_id', $id)->delete();
            $amenityTypes = $this->request->getPost('amenity_type');
            $amenityValues = $this->request->getPost('amenity_value');

            if (!empty($amenityTypes) && is_array($amenityTypes)) {
                foreach ($amenityTypes as $key => $type) {
                    if (!empty($type) && isset($amenityValues[$key]) && !empty($amenityValues[$key])) {
                        $amenityData = [
                            'real_estate_id' => $id,
                            'amenity_type'   => $type,
                            'value'          => $amenityValues[$key],
                        ];
                        $amenitiesModel->insert($amenityData);
                    }
                }
            }

            $existingPhotoOrderRaw = (string) $this->request->getPost('existing_photo_order');
            $orderedPhotoIds = array_values(array_filter(array_map('trim', explode(',', $existingPhotoOrderRaw)), static fn ($v) => $v !== ''));

            $photosToDeleteRaw = (string) $this->request->getPost('photos_to_delete');
            $photoIdsToDelete = array_values(array_filter(array_map('trim', explode(',', $photosToDeleteRaw)), static fn ($v) => $v !== ''));

            $this->manageExistingPhotos((int) $id, $orderedPhotoIds, $photoIdsToDelete);
            
            // Lógica para manejar la subida de fotos
            $files = $this->request->getFiles();
            if ($files && isset($files['photos'])) {
                $coverPhotoIndexRaw = $this->request->getPost('cover_photo_index');
                $coverPhotoIndex = is_numeric($coverPhotoIndexRaw) ? (int) $coverPhotoIndexRaw : null;
                $uploadedPhotos = is_array($files['photos']) ? $files['photos'] : [$files['photos']];
                $this->saveUploadedPhotos((int) $id, $uploadedPhotos, $coverPhotoIndex);
            }

            return redirect()->to('admin/real_estates')->with('success', 'Inmueble actualizado correctamente.');
        } else {
            $data['realEstate'] = $realEstate;
            $data['proyectos'] = (new \App\Models\ProyectosInmobiliariosModel())->findAll();
            $data['payment_methods_catalog'] = (new \App\Models\ClientDocumentCatalogModel())->getPaymentMethods();
            $data['zones'] = (new \App\Models\ZoneModel())->findAll();
            $data['users'] = (new \App\Models\UserModel())->findAll();
            $data['photos'] = (new \App\Models\RealEstatePhotosModel())->where('real_estate_id', $id)->orderBy('id', 'ASC')->findAll();
            $data['amenities'] = (new \App\Models\RealEstateAmenitiesModel())->where('real_estate_id', $id)->findAll();
            $data['validation'] = $this->validator;
            
            return view('admin/real_estates/edit', $data);
        }
    }
    
    public function delete($id)
    {
        $realEstateModel = new RealEstateModel();
        $realEstateModel->delete($id);
        
        return redirect()->to('admin/real_estates')->with('success', 'Inmueble eliminado correctamente.');
    }
    
    /**
     * Muestra la ficha técnica de un inmueble.
     *
     * @param int $id El ID del inmueble.
     * @return string
     */
    public function technicalSheet(int $id): string
    {
        $realEstateModel = new RealEstateModel();
        $realEstatePhotosModel = new RealEstatePhotosModel();
        $amenitiesModel = new RealEstateAmenitiesModel();

        // Obtiene los datos del inmueble, uniéndolos con el proyecto
        $realEstate = $realEstateModel->select('real_estates.*, real_estate_projects.name as project_name')
            ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
            ->where('real_estates.id', $id)
            ->first();

        if (empty($realEstate)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se puede encontrar el inmueble con el ID: ' . $id);
        }
        
        // Obtiene las fotos y amenidades
        $realEstate['photos'] = $realEstatePhotosModel->where('real_estate_id', $id)->findAll();
        $realEstate['amenities'] = $amenitiesModel->where('real_estate_id', $id)->findAll();

        $data['realEstate'] = $realEstate;
        return view('admin/real_estates/technical_sheet', $data);
    }
    
    /**
     * Genera y descarga la ficha técnica de un inmueble en formato PDF.
     *
     * @param int $id El ID del inmueble.
     * @return void
     */
    public function downloadTechnicalSheet(int $id)
    {
        // Obtiene los datos del inmueble
        $realEstateModel = new RealEstateModel();
        $realEstatePhotosModel = new RealEstatePhotosModel();
        $amenitiesModel = new RealEstateAmenitiesModel();
        $userModel = new \App\Models\UserModel();
        $configModel = new ConfigurationModel();
        
        $realEstate = $realEstateModel->select('real_estates.*, real_estate_projects.name as project_name, real_estate_projects.description as project_description, real_estate_projects.location as project_location')
            ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
            ->where('real_estates.id', $id)
            ->first();

        if (empty($realEstate)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('No se puede encontrar el inmueble con el ID: ' . $id);
        }
        
        // Obtiene fotos y amenidades para el PDF
        $realEstate['photos'] = $realEstatePhotosModel->where('real_estate_id', $id)->findAll();
        $realEstate['amenities'] = $amenitiesModel->where('real_estate_id', $id)->findAll();
        
        // Obtener datos del asesor
        $agent = $userModel->find($realEstate['created_by_user_id']);

        // Obtener datos de configuración de la empresa
        $companyConfigs = $configModel->whereIn('config_key', [
            'company_name',
            'company_website',
            'company_phone',
            'company_email',
            'company_address',
        ])->findAll();
        $companySettings = [];
        foreach ($companyConfigs as $config) {
            $companySettings[$config['config_key']] = $config['config_value'];
        }

        $data['realEstate'] = $realEstate;
        $data['agent'] = $agent;
        
        // Pasar configuraciones a la vista con valores por defecto
        $data['company_name'] = $companySettings['company_name'] ?? 'Tu Inmobiliaria';
        $data['company_website'] = $companySettings['company_website'] ?? 'www.tusitio.com';
        $data['company_phone'] = $companySettings['company_phone'] ?? ($agent['phone'] ?? '');
        $data['company_email'] = $companySettings['company_email'] ?? ($agent['email'] ?? '');
        $data['company_address'] = $companySettings['company_address'] ?? ($realEstate['address'] ?? '');

        // Determinar si se incluye el branding (header/footer con datos del asesor y QR)
        $branding = $this->request->getGet('branding');
        $data['show_branding'] = ($branding === null || $branding === '1');

        // Generar URL para el código QR que apunta a la ficha pública
        $data['qrCodeUrl'] = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode(site_url('ficha-tecnica/' . $id));
        
        // Carga la vista para el PDF, sin el template principal
        $html = view('admin/real_estates/pdf/technical_sheet_pdf', $data);
        
        // Instancia de Dompdf
        $options = new \Dompdf\Options();
        // Habilitar carga de imágenes remotas (para el QR code) y locales
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Genera el nombre del archivo
        $filename = 'ficha_tecnica_' . $realEstate['clave'] . '.pdf';
        
        // Limpia cualquier output buffering previo para evitar corrupción del PDF en móviles
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $pdfContent = $dompdf->output();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $pdfContent;
        exit;
    }
    
    /**
     * Encuentra prospectos que coinciden con un inmueble específico.
     * Criterios de matching:
     * - Misma zona de interés
     * - Monto de crédito compatible con el precio del inmueble (±20%)
     * - Método de pago compatible
     *
     * @param int $id El ID del inmueble.
     * @return string JSON con los prospectos que coinciden
     */
    public function getMatchingProspects($id)
    {
        $realEstateModel = new RealEstateModel();
        $realEstate = $realEstateModel->find($id);
        
        if (empty($realEstate)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Inmueble no encontrado'
            ]);
        }
        
        $clientModel = new \App\Models\ClientModel();
        
        // Construimos la consulta con los criterios de matching
        $builder = $clientModel->builder();
        $builder->select('clients.*, zones.name as zone_name, users.full_name as asesor_name');
        $builder->join('zones', 'zones.id = clients.zone_id', 'left');
        $builder->join('users', 'users.id = clients.user_id', 'left');
        
        // Criterio 1: Misma zona
        if (!empty($realEstate['zone_id'])) {
            $builder->where('clients.zone_id', $realEstate['zone_id']);
        }
        
        // Criterio 2: Monto de crédito compatible (±20% del precio)
        $price = floatval($realEstate['price']);
        $minCredit = $price * 0.8;  // 20% menos
        $maxCredit = $price * 1.2;  // 20% más
        $builder->where('clients.credit_amount >=', $minCredit);
        $builder->where('clients.credit_amount <=', $maxCredit);
        
        // Criterio 3: Método de pago compatible
        // payment_methods en real_estates es una cadena JSON con array de IDs
        if (!empty($realEstate['payment_methods'])) {
            $paymentMethods = json_decode($realEstate['payment_methods'], true);
            if (is_array($paymentMethods) && count($paymentMethods) > 0) {
                $builder->whereIn('clients.payment_method', $paymentMethods);
            }
        }
        
        // Solo prospectos activos
        $builder->where('clients.status', 'activo');
        
        $builder->orderBy('clients.created_at', 'DESC');
        
        $matchingProspects = $builder->get()->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'real_estate' => [
                'clave' => $realEstate['clave'],
                'name' => $realEstate['name'],
                'price' => $realEstate['price'],
                'zone_id' => $realEstate['zone_id']
            ],
            'prospects' => $matchingProspects,
            'total' => count($matchingProspects)
        ]);
    }
}
