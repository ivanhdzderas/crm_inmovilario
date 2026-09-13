<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\ClientFollowUpModel;
use App\Models\CalendarActivityModel;
use App\Models\ZoneModel;
use App\Models\ClientRealEstateInterestModel;
use App\Models\ClientFuneralInterestModel;
use App\Models\FuneralProductModel;
use App\Models\FuneralFinancingPlanModel;
use App\Models\RealEstateModel;
use App\Models\ClientAccountModel;
use App\Models\ClientAccountMovementModel;
use App\Models\DocumentCatalogModel;
use App\Models\ClientDocumentCatalogModel;
use App\Models\ClientDocumentChecklistModel;
use App\Models\PaymentMethodModel;
use App\Models\CancellationReasonModel;
use App\Models\AcquisitionSourceModel;
use CodeIgniter\I18n\Time;

class Prospects extends BaseController
{
    protected $clientModel;
    protected $followUpModel;
    protected $activityModel;
    protected $zoneModel;
    protected $interestModel;
    protected $funeralInterestModel;
    protected $realEstateModel;
    protected $accountModel;
    protected $movementModel;
    protected $documentModel;
    protected $clientDocumentCatalogModel;
    protected $checklistModel;
    protected $db; // Para usar transacciones
    protected $paymentMethodModel;
    protected $paymentMethodIdToName = [];
    protected $paymentMethodNameToId = [];

    /**
     * Columna real para método de pago en la tabla clients.
     * Puede ser payment_method (esquema nuevo) o payment_method_id (esquema anterior).
     */
    protected $clientPaymentColumn = 'payment_method';

    public function __construct()
    {
        // Inicializar modelos
        $this->clientModel = new ClientModel();
        $this->followUpModel = new ClientFollowUpModel();
        $this->activityModel = new CalendarActivityModel();
        $this->zoneModel = new ZoneModel();
        $this->interestModel = new ClientRealEstateInterestModel();
        $this->funeralInterestModel = new ClientFuneralInterestModel();
        $this->realEstateModel = new RealEstateModel();
        $this->accountModel = new ClientAccountModel();
        $this->movementModel = new ClientAccountMovementModel();
        $this->documentModel = new DocumentCatalogModel();
        $this->clientDocumentCatalogModel = new ClientDocumentCatalogModel();
        $this->checklistModel = new ClientDocumentChecklistModel();
        $this->db = \Config\Database::connect();

        // Compatibilidad de esquema: si no existe payment_method, usar payment_method_id.
        if (!$this->db->fieldExists('payment_method', 'clients') && $this->db->fieldExists('payment_method_id', 'clients')) {
            $this->clientPaymentColumn = 'payment_method_id';
        }

        // Si existe catálogo de métodos de pago, precargar mapa id <-> nombre.
        if ($this->db->tableExists('payment_methods')) {
            $this->paymentMethodModel = new PaymentMethodModel();
            foreach ($this->paymentMethodModel->orderBy('name', 'ASC')->findAll() as $pm) {
                $id = (int) ($pm['id'] ?? 0);
                $name = trim((string) ($pm['name'] ?? ''));
                if ($id > 0 && $name !== '') {
                    $this->paymentMethodIdToName[$id] = $name;
                    $this->paymentMethodNameToId[strtolower($name)] = $id;
                }
            }
        }
    }

    private function getPaymentMethodOptions(): array
    {
        if (!empty($this->paymentMethodIdToName)) {
            return array_values($this->paymentMethodIdToName);
        }

        return $this->clientDocumentCatalogModel->getPaymentMethods();
    }

    private function getAcquisitionSourceFormData(): array
    {
        if (! $this->db->tableExists('acquisition_sources')) {
            return [
                'acquisitionSources' => [],
            ];
        }

        $sourceModel = new AcquisitionSourceModel();

        return [
            'acquisitionSources' => $sourceModel->getActiveOrdered(),
        ];
    }

    private function resolveAcquisitionFieldsFromPost(): array
    {
        if (! $this->db->fieldExists('acquisition_source_id', 'clients')) {
            return [];
        }

        $sourceId = $this->request->getPost('acquisition_source_id');
        $detail   = trim((string) $this->request->getPost('acquisition_detail'));

        return [
            'acquisition_source_id' => $sourceId ? (int) $sourceId : null,
            'acquisition_detail'    => $detail !== '' ? $detail : null,
        ];
    }

    private function markAsFuneralProspect(int $clientId): void
    {
        if ($this->db->fieldExists('is_funeral_prospect', 'clients')) {
            $this->clientModel->update($clientId, ['is_funeral_prospect' => 1]);
        }
    }

    private function getFuneralPackagesLabel(int $clientId): string
    {
        if (! $this->db->tableExists('client_funeral_interests') || ! $this->db->tableExists('funeral_products')) {
            return '';
        }

        $row = $this->db->query('
            SELECT GROUP_CONCAT(DISTINCT fp.name ORDER BY fp.name SEPARATOR \', \') AS funeral_packages
            FROM client_funeral_interests cfi
            INNER JOIN funeral_products fp ON fp.id = cfi.funeral_product_id
            WHERE cfi.client_id = ?
        ', [$clientId])->getRowArray();

        return trim((string) ($row['funeral_packages'] ?? ''));
    }

    private function buildFuneralPackagesListCellHtml(?string $packages): string
    {
        if ($packages !== null && $packages !== '') {
            return '<p class="text-sm mb-0">' . esc($packages) . '</p>';
        }

        return '<span class="text-muted small">Sin paquete aún</span>';
    }

    /** @return array{clientId: int, funeralPackages: string, funeralPackagesHtml: string} */
    private function funeralListSyncPayload(int $clientId): array
    {
        $packages = $this->getFuneralPackagesLabel($clientId);

        return [
            'clientId'            => $clientId,
            'funeralPackages'     => $packages,
            'funeralPackagesHtml' => $this->buildFuneralPackagesListCellHtml($packages),
        ];
    }

    private function resolvePaymentMethodLabel($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $id = (int) $value;
            if (isset($this->paymentMethodIdToName[$id])) {
                return $this->paymentMethodIdToName[$id];
            }

            $fallbackMap = [1 => 'Infonavit', 2 => 'Fovissste', 3 => 'Contado', 4 => 'Credito Bancario'];
            return $fallbackMap[$id] ?? null;
        }

        return (string) $value;
    }

    private function resolvePaymentMethodValueForStorage($postedValue, $currentValue = null)
    {
        $postedValue = trim((string) $postedValue);

        if ($postedValue === '') {
            return null;
        }

        // Esquema nuevo: guardar texto directamente.
        if ($this->clientPaymentColumn === 'payment_method') {
            return $postedValue;
        }

        // Esquema anterior: guardar ID del catálogo.
        if (is_numeric($postedValue)) {
            return (int) $postedValue;
        }

        $normalized = strtolower($postedValue);
        if (isset($this->paymentMethodNameToId[$normalized])) {
            return $this->paymentMethodNameToId[$normalized];
        }

        $fallbackMap = [
            'infonavit' => 1,
            'fovissste' => 2,
            'contado' => 3,
            'credito bancario' => 4,
            'crédito bancario' => 4,
            'bancario' => 4,
        ];

        return $fallbackMap[$normalized] ?? $currentValue;
    }

    private function normalizeProspectPaymentMethod(array $prospect): array
    {
        $raw = $prospect['payment_method'] ?? ($prospect['payment_method_id'] ?? null);
        $prospect['payment_method'] = $this->resolvePaymentMethodLabel($raw);

        return $prospect;
    }

    /**
     * Muestra la lista principal de prospectos.
     */
    public function index()
    {
        $type     = $this->request->getGet('type');
        $interest = $this->request->getGet('interest');

        $data['prospects'] = $this->clientModel->getProspectsList($type, $interest);
        $data['listTitle'] = match ($interest) {
            'funerario' => 'Compradores funerarios',
            default     => match ($type) {
                'comprador' => 'Compradores (inmuebles)',
                'vendedor'  => 'Propietarios',
                default     => 'Gestión de Prospectos (CRM)',
            },
        };
        $data['listFilter'] = $interest ?: $type;
        $data['isFunerarioList'] = ($interest === 'funerario');

        return view('prospects/index', $data);
    }
    
    /**
     * Muestra el formulario para crear un nuevo prospecto.
     */
    public function create()
    {
        // Cargar helper de formulario para que form_open() esté disponible en la vista
        helper(['form']);

        $funerarioMode = $this->request->getGet('interest') === 'funerario';

        // Obtener datos necesarios para los selects del formulario
        $data = [
            'zones' => $this->zoneModel->findAll(), // Usando el ZoneModel corregido
            'payment_methods' => $this->getPaymentMethodOptions(),
            'statuses' => ['Nuevo', 'Seguimiento', 'Cierre', 'Cancelado'],
            'client_types' => ['comprador', 'vendedor'],
            'funerarioMode' => $funerarioMode,
        ];

        return view('prospects/create', array_merge($data, $this->getAcquisitionSourceFormData()));
    }

    /**
     * Guarda el nuevo prospecto.
     */
    public function store()
    {
        $funerarioMode = $this->request->getPost('prospect_context') === 'funerario';

        // NOTA: 'payment_method_id' en la base de datos es un varchar, se valida como 'required'.
        $rules = [
            'full_name' => 'required|min_length[3]|max_length[100]',
            'email'     => 'permit_empty|valid_email|is_unique[clients.email]',
            'phone'     => 'required|min_length[10]|max_length[20]',
            'client_type' => 'required|in_list[comprador,vendedor]',
            'credit_amount' => 'permit_empty', // Permitimos vacío
            'zone_id' => 'permit_empty|is_natural_no_zero', // Permitimos vacío, pero si existe debe ser número
            'payment_method' => 'permit_empty',
            'status' => 'required',
        ];

        if ($this->db->fieldExists('acquisition_source_id', 'clients')) {
            $rules['acquisition_source_id'] = 'permit_empty|is_natural_no_zero';
            $rules['acquisition_detail'] = 'permit_empty|max_length[255]';
        }

        if (!$this->validate($rules)) {
            // Manejo de errores de validación
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Procesar crédito: si está vacío o trae formato de moneda, lo limpiamos.
        // Si queda vacío, asignamos 0 para evitar error en BD.
        $creditRaw = $this->request->getPost('credit_amount');
        $creditAmount = 0;
        if (!empty($creditRaw)) {
            $creditAmount = preg_replace('/[^0-9.]/', '', $creditRaw);
        }

        $data = [
            // CORRECCIÓN: usar user_id desde la sesión (evita dependencia de helper auth())
            'user_id'           => session()->get('user_id'),
            'full_name'         => $this->request->getPost('full_name'),
            'client_type'       => $funerarioMode ? 'comprador' : ($this->request->getPost('client_type') ?: 'comprador'),
            'email'             => $this->request->getPost('email'),
            'phone'             => $this->request->getPost('phone'),
            'credit_amount'     => $funerarioMode ? 0 : $creditAmount,
            'zone_id'           => $funerarioMode ? null : ($this->request->getPost('zone_id') ?: null),
            'status'            => $funerarioMode ? 'Nuevo' : $this->request->getPost('status'),
        ];

        if ($this->db->fieldExists('is_funeral_prospect', 'clients')) {
            $data['is_funeral_prospect'] = $funerarioMode ? 1 : 0;
        }

        $data = array_merge($data, $this->resolveAcquisitionFieldsFromPost());

        if ($funerarioMode) {
            $data[$this->clientPaymentColumn] = null;
        } else {
            // Guardar método de pago en la columna disponible del esquema actual.
            $data[$this->clientPaymentColumn] = $this->resolvePaymentMethodValueForStorage($this->request->getPost('payment_method'));
        }

        if ($this->clientModel->insert($data)) {
            if ($funerarioMode) {
                $this->markAsFuneralProspect((int) $this->clientModel->getInsertID());
            }

            $redirectUrl = $funerarioMode
                ? site_url('prospects?interest=funerario')
                : site_url('prospects');

            return redirect()->to($redirectUrl)->with('success', $funerarioMode ? 'Comprador funerario registrado.' : 'Prospecto creado exitosamente.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Hubo un error al guardar el prospecto.');
        }
    }

    /**
     * Muestra el detalle del prospecto y su historial de seguimiento.
     * @param int $clientId
     */
    public function view(int $clientId)
    {
        // 1. Obtener el prospecto
        $prospect = $this->clientModel->find($clientId);

        // Normalizar para las vistas: siempre exponer payment_method.
        if (is_array($prospect)) {
            $prospect = $this->normalizeProspectPaymentMethod($prospect);
        }

        if (!$prospect) {
            return redirect()->to(site_url('prospects'))->with('error', 'Prospecto no encontrado.');
        }

        if (! empty($prospect['deactivation_reason_id'])) {
            $reason = (new CancellationReasonModel())->find((int) $prospect['deactivation_reason_id']);
            $prospect['deactivation_reason_name'] = $reason['name'] ?? null;
        }

        if (! empty($prospect['acquisition_source_id']) && $this->db->tableExists('acquisition_sources')) {
            $source = (new AcquisitionSourceModel())->find((int) $prospect['acquisition_source_id']);
            $prospect['acquisition_source_name'] = $source['name'] ?? null;
            $prospect['acquisition_source_icon'] = $source['icon_class'] ?? null;
            $prospect['acquisition_channel_type'] = $source['channel_type'] ?? null;
        }

        // 2. Obtener el historial de seguimiento (Minutas)
        try {
            $followUps = $this->followUpModel->getFollowUpsByClient($clientId);
        } catch (\Exception $e) {
            log_message('error', 'Error getting follow ups for client ' . $clientId . ': ' . $e->getMessage());
            $followUps = [];
        }

        // 3. Obtener intereses de inmuebles
        try {
            $propertyInterests = $this->interestModel->getClientInterests($clientId);
        } catch (\Exception $e) {
            log_message('error', 'Error getting property interests for client ' . $clientId . ': ' . $e->getMessage());
            $propertyInterests = [];
        }

        // 3b. Intereses funerarios (reutiliza catálogo existente)
        try {
            $funeralInterests = $this->db->tableExists('client_funeral_interests')
                ? $this->funeralInterestModel->getClientInterests($clientId)
                : [];
        } catch (\Exception $e) {
            log_message('error', 'Error getting funeral interests for client ' . $clientId . ': ' . $e->getMessage());
            $funeralInterests = [];
        }

        // 4. Obtener estado de cuenta del cliente (si es cliente)
        $clientAccount = null;
        $accountMovements = [];
        $funeralAccount = null;
        $funeralAccountMovements = [];
        if ($prospect['status'] === 'Cliente' || ! empty($funeralInterests)) {
            $clientAccount = $this->accountModel->getClientActiveAccount($clientId);
            if ($clientAccount) {
                $accountMovements = $this->movementModel->getAccountMovements($clientAccount['id']);
            }
            if ($this->db->fieldExists('account_type', 'client_accounts')) {
                $funeralAccount = $this->accountModel->getClientFuneralActiveAccount($clientId);
                if ($funeralAccount) {
                    $funeralAccountMovements = $this->movementModel->getAccountMovements($funeralAccount['id']);
                }
            }
        }

        // 5. Obtener inmuebles disponibles para asignar
        $availableProperties = $this->realEstateModel->select('real_estates.*, real_estate_projects.name as project_name')
                                                     ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
                                                     ->whereIn('real_estates.status', ['Disponible', 'Nuevo']) // Excluir Apartado y Vendido
                                                     ->findAll();

        $availableFuneralProducts = [];
        if ($this->db->tableExists('funeral_products')) {
            $availableFuneralProducts = (new FuneralProductModel())->getPublicCatalog();
        }

        // 5. Obtener el nombre del asesor y la zona
        $advisorModel = model('UserModel'); // Asumiendo que existe un 'UserModel' para obtener nombres
        $advisor_name = $advisorModel->find($prospect['user_id'])['full_name'] ?? 'N/A';
        
        // Cargar helper de formulario para que form_open() esté disponible en la vista de detalle
        helper(['form']);

        $data = [
            'prospect' => $prospect,
            'followUps' => $followUps,
            'propertyInterests' => $propertyInterests,
            'funeralInterests' => $funeralInterests,
            'availableFuneralProducts' => $availableFuneralProducts,
            'funerarioModuleEnabled' => $this->db->tableExists('client_funeral_interests'),
            'clientAccount' => $clientAccount,
            'accountMovements' => $accountMovements,
            'funeralAccount' => $funeralAccount,
            'funeralAccountMovements' => $funeralAccountMovements,
            'availableProperties' => $availableProperties,
            'advisor_name' => $advisor_name,
            'zone_name' => $this->zoneModel->find($prospect['zone_id'])['name'] ?? 'N/A', 
            // Datos para el formulario de seguimiento
            'contact_methods' => ['Llamada', 'WhatsApp', 'Email', 'Reunión Presencial', 'Otro'],
            'activity_types' => ['Llamada', 'Reunión', 'Cita Infonavit', 'Envío Documentos', 'Otro'],            
            'title' => 'Detalle del Prospecto',
            'prospectCancellationReasons' => (new CancellationReasonModel())->getActiveByCategory('prospect'),
            'apartadoCancellationReasons' => (new CancellationReasonModel())->getActiveByCategory('apartado'),
        ];

        return view('prospects/view', array_merge($data, $this->getAcquisitionSourceFormData()));
    }

    /**
     * Muestra el formulario de edición para un prospecto (para offcanvas)
     */
    public function edit(int $clientId)
    {
        $prospect = $this->clientModel->find($clientId);
        if (!$prospect) {
            return redirect()->to(site_url('prospects'))->with('error', 'Prospecto no encontrado.');
        }

        // CORRECCIÓN: Asegurar que payment_method exista para evitar error "Undefined array key"
        // Si existe el campo antiguo 'payment_method_id' pero no el nuevo, lo usamos como fallback
        $prospect = $this->normalizeProspectPaymentMethod($prospect);

        helper(['form']);

        $data = [
            'prospect' => $prospect,
            'zones' => $this->zoneModel->findAll(),
            'payment_methods' => $this->getPaymentMethodOptions(),
            'statuses' => ['Nuevo', 'Seguimiento', 'Cierre', 'Cancelado', 'Cliente'],
            'client_types' => ['comprador', 'vendedor'],
        ];

        return view('prospects/edit', array_merge($data, $this->getAcquisitionSourceFormData()));
    }

    /**
     * Procesa la actualización de un prospecto existente
     */
    public function update(int $clientId)
    {
        $prospect = $this->clientModel->find($clientId);
        if (!$prospect) {
            return redirect()->to(site_url('prospects'))->with('error', 'Prospecto no encontrado.');
        }

        // Si el formulario no envía full_name (o llega vacío), conservar el nombre actual.
        $fullNamePost = trim((string) $this->request->getPost('full_name'));
        if ($fullNamePost === '') {
            $fullNamePost = $prospect['full_name'] ?? '';
        }

        // Reglas básicas (email allow same value)
        $emailPost = $this->request->getPost('email');
        $rules = [
            'full_name' => 'permit_empty|min_length[3]|max_length[100]',
            'email'     => 'permit_empty|valid_email',
            'phone'     => 'required|min_length[10]|max_length[20]',
            'client_type' => 'required|in_list[comprador,vendedor]',
            'credit_amount' => 'permit_empty',
            'zone_id' => 'permit_empty|is_natural_no_zero',
            'payment_method' => 'permit_empty',
            'status' => 'required',
        ];

        if ($this->db->fieldExists('acquisition_source_id', 'clients')) {
            $rules['acquisition_source_id'] = 'permit_empty|is_natural_no_zero';
            $rules['acquisition_detail'] = 'permit_empty|max_length[255]';
        }

        // Si el email cambió, verificar unicidad
        if ($emailPost !== ($prospect['email'] ?? null)) {
            $rules['email'] .= '|is_unique[clients.email]';
        }

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        // Sanitizar crédito (quitar separadores)
        $creditRaw = $this->request->getPost('credit_amount');
        $creditSanitized = '';
        if ($creditRaw !== null && $creditRaw !== '') {
            $creditSanitized = preg_replace('/[^0-9.\\-]/', '', $creditRaw);
        }

        $data = [
            'full_name' => $fullNamePost,
            'client_type' => $this->request->getPost('client_type') ?: 'comprador',
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'credit_amount' => $creditSanitized !== '' ? $creditSanitized : null,
            'zone_id' => $this->request->getPost('zone_id') ?: null,
            'status' => $this->request->getPost('status'),
        ];

        $data = array_merge($data, $this->resolveAcquisitionFieldsFromPost());

        // Guardar método de pago en la columna disponible del esquema actual.
        $data[$this->clientPaymentColumn] = $this->resolvePaymentMethodValueForStorage(
            $this->request->getPost('payment_method'),
            $prospect[$this->clientPaymentColumn] ?? null
        );

        if ($this->clientModel->update($clientId, $data) === false) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Error al actualizar el prospecto.']]);
            }
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el prospecto.');
        }

        // Build updated row data for DataTable when requested via AJAX
        if ($this->request->isAJAX()) {
            // Reload minimal related values
            $zone = $this->zoneModel->find($data['zone_id']) ?? null;
            $zone_name = $zone['name'] ?? 'N/A';

            $advisorModel = model('UserModel');
            $user = $advisorModel->find($prospect['user_id']) ?? null;
            $user_name = $user['full_name'] ?? 'N/A';

            $acquisitionHtml = '<span class="text-muted small">—</span>';
            if (! empty($data['acquisition_source_id']) && $this->db->tableExists('acquisition_sources')) {
                $source = (new AcquisitionSourceModel())->find((int) $data['acquisition_source_id']);
                if ($source) {
                    $icon = ! empty($source['icon_class']) ? '<i class="' . esc($source['icon_class']) . ' me-1"></i>' : '';
                    $acquisitionHtml = '<p class="text-sm mb-0">' . $icon . esc($source['name']) . '</p>';
                    if (! empty($data['acquisition_detail'])) {
                        $acquisitionHtml .= '<small class="text-muted">' . esc($data['acquisition_detail']) . '</small>';
                    }
                }
            }

            // Format credit for display
            $creditDisplay = '$' . number_format($data['credit_amount'] ?? 0, 2);

            // Status badge
            $badge_color = 'secondary';
            $text_color = 'text-white';
            if ($data['status'] == 'Nuevo') { $badge_color = 'primary'; }          /* Azul Pacífico */
            else if ($data['status'] == 'Cierre') { $badge_color = 'success'; }    /* Dorado Oscuro */
            else if (in_array($data['status'], ['Descartado', 'Cancelado'], true)) { $badge_color = 'secondary'; } 
            else if ($data['status'] == 'Seguimiento') { $badge_color = 'warning'; } /* Dorado */
            else if ($data['status'] == 'Cliente') { $badge_color = 'info'; }      /* Azul Secundario */

            $statusHtml = '<span class="badge bg-' . $badge_color . ' ' . $text_color . ' text-xs font-weight-bold p-2 rounded-pill">' . esc($data['status']) . '</span>';

            // Actions column (same button as original)
            $actions = '<button type="button" data-id="' . $clientId . '" class="btn btn-sm btn-info text-white rounded-pill px-3 btn-offcanvas-detail" title="Ver Detalle y Seguimiento"><i class="fa fa-clipboard-list me-1"></i> Detalle</button>';

            // Build HTML cells matching the table columns
            $rowHtml = [];
            $rowHtml[] = '<p class="text-sm font-weight-bold mb-0">#' . $clientId . '</p>';
            $rowHtml[] = '<div class="d-flex px-2 py-1"><div class="d-flex flex-column justify-content-center"><h6 class="mb-0 text-sm">' . esc($data['full_name']) . '</h6><p class="text-xs text-secondary mb-0">' . esc($data['email']) . '</p></div></div>';
            $rowHtml[] = '<p class="text-sm font-weight-bold mb-0 text-success">' . $creditDisplay . '</p>';
            $rowHtml[] = '<p class="text-sm mb-0 text-secondary">' . esc($zone_name) . '</p>';
            $rowHtml[] = $acquisitionHtml;
            $rowHtml[] = '<p class="text-sm mb-0 text-dark">' . esc($user_name) . '</p>';
            $rowHtml[] = $statusHtml;
            $rowHtml[] = $actions;

            return $this->response->setJSON(['success' => true, 'rowId' => $clientId, 'rowHtml' => $rowHtml]);
        }

        return redirect()->to(site_url('prospects/view/' . $clientId))->with('success', 'Prospecto actualizado correctamente.');
    }
    
   /**
     * Guarda una minuta de seguimiento.
     * @param int $clientId
     */
    public function saveFollowUp(int $clientId)
    {
        $scheduleNext = (bool) $this->request->getPost('schedule_next_meeting');

        // 1. Reglas de Validación
        $rules = [
            'discussion_detail' => 'required|max_length[500]',
            'contact_method'    => 'required',
            'contact_datetime'  => 'required|valid_date',
            'personal_notes'    => 'permit_empty|max_length[1000]',
        ];

        if ($scheduleNext) {
            $rules['activity_title']      = 'required|max_length[255]';
            $rules['activity_start_time'] = 'required|valid_date[Y-m-d H:i]';
            $rules['activity_end_time']   = 'permit_empty|valid_date[Y-m-d H:i]';
            $rules['activity_type']       = 'required|in_list[Llamada,Reunión,Cita Infonavit,Envío Documentos,Otro]';
            $rules['activity_description']= 'permit_empty|max_length[500]';
        }

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        try {
            date_default_timezone_set(config('App')->appTimezone);

            $this->db->transBegin();

            $currentUserId = session()->get('user_id');
            $advisorId = $currentUserId;

            $activityId = null;
            $scheduledActivity = null;

            if ($scheduleNext) {
                $activityId = $this->createFollowUpActivity($clientId, $currentUserId);
                if (! $activityId) {
                    throw new \Exception('No se pudo agendar la reunión de seguimiento.', 3);
                }
                $scheduledActivity = $this->activityModel->find($activityId);
            }

            $followUpData = [
                'client_id'         => $clientId,
                'user_id'           => $currentUserId,
                'advisor_id'        => $advisorId,
                'contact_method'    => $this->request->getPost('contact_method'),
                'discussion_detail' => $this->request->getPost('discussion_detail'),
                'contact_datetime'  => $this->request->getPost('contact_datetime'),
                'personal_notes'    => $this->request->getPost('personal_notes'),
                'activity_id'       => $activityId,
            ];

            $insertedFollow = $this->followUpModel->insert($followUpData);
            $followUpId = $this->followUpModel->getInsertID();

            if ($insertedFollow === false || ! $followUpId) {
                $dbError = $this->db->error();
                throw new \Exception("Error al guardar la minuta. DB Code: {$dbError['code']}", 2);
            }

            $interestResult = $this->processFollowUpInterests($clientId);

            $prospectRow = $this->clientModel->find($clientId);
            if (($prospectRow['status'] ?? '') !== 'Cliente') {
                $this->clientModel->update($clientId, ['status' => 'Seguimiento']);
            }

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error al guardar el seguimiento completo.');
            }

            $this->db->transCommit();

            $newFollowUp = $this->followUpModel->find($followUpId);

            if ($this->request->isAJAX()) {
                // --- Generación de HTML para respuesta AJAX ---
                $advisorModel = model('UserModel');
                
                // Buscar el advisor que guardó la minuta
                $advisor = $advisorModel->find($advisorId); 
                $advisor_name = 'Asesor Desconocido';
                
                if ($advisor && !empty($advisor['full_name'])) {
                    $advisor_name = $advisor['full_name'];
                } elseif ($advisor && !empty($advisor['username'])) {
                    $advisor_name = $advisor['username'];
                } elseif ($advisor && !empty($advisor['email'])) {
                    $advisor_name = explode('@', $advisor['email'])[0];
                }

                // Usar la fecha del contacto si está disponible, sino usar created_at
                $contact_date = $newFollowUp['contact_datetime'] ?? $newFollowUp['created_at'] ?? date('Y-m-d H:i:s');
                $display_date = date('d/m/Y H:i', strtotime($contact_date));
                $contact_method = esc($newFollowUp['contact_method']);
                $discussion = nl2br(esc($newFollowUp['discussion_detail']));

                $followUpHtml = '<div class="timeline-item mb-3">'
                    . '<div class="timeline-badge bg-primary"><i class="fa fa-comment-dots"></i></div>'
                    . '<div class="timeline-panel card">'
                    . '<div class="card-header p-2 bg-light border-bottom border-warning">'
                    . '<small class="text-muted float-end">' . $display_date . '</small>'
                    . '<p class="mb-0 text-dark"><span class="badge bg-warning text-dark me-1">' . $contact_method . '</span> por ' . esc($advisor_name) . '</p>'
                    . '</div>'
                    . '<div class="card-body p-2"><p class="mb-1 text-secondary">' . $discussion . '</p>';

                // Agregar observaciones personales si existen
                if (!empty($newFollowUp['personal_notes'])) {
                    $personal_notes = nl2br(esc($newFollowUp['personal_notes']));
                    $uniqueId = 'personal_notes_' . $followUpId;
                    $followUpHtml .= '<div class="mt-2">'
                        . '<a data-toggle="collapse" href="#' . $uniqueId . '" role="button" aria-expanded="false" class="text-decoration-none">'
                        . '<small class="text-muted"><i class="fas fa-user-secret mr-1"></i><strong>Ver observaciones personales</strong> <i class="fas fa-chevron-down ml-1"></i></small>'
                        . '</a>'
                        . '<div class="collapse mt-2" id="' . $uniqueId . '">'
                        . '<div class="p-2 bg-light border-left border-warning rounded-sm">'
                        . '<small class="text-dark"><i class="fas fa-lock mr-1 text-warning"></i>' . $personal_notes . '</small>'
                        . '</div></div></div>';
                }

                if (! empty($newFollowUp['activity_id'])) {
                    $activityLabel = esc($scheduledActivity['title'] ?? 'Seguimiento agendado');
                    $activityWhen  = ! empty($scheduledActivity['start_time'])
                        ? date('d/m/Y H:i', strtotime($scheduledActivity['start_time']))
                        : '';
                    $followUpHtml .= '<div class="mt-2 p-2 bg-info bg-opacity-10 border border-info rounded-sm">'
                        . '<small class="text-dark font-weight-bold"><i class="fa fa-calendar-check me-1"></i> '
                        . 'Siguiente seguimiento: ' . $activityLabel
                        . ($activityWhen ? ' (' . $activityWhen . ')' : '')
                        . '</small></div>';
                }

                $followUpHtml .= '</div></div></div>';

                return $this->response->setJSON([
                    'success' => true,
                    'followUpHtml' => $followUpHtml,
                    'followUpId' => $followUpId,
                    'activityScheduled' => ! empty($activityId),
                    'propertiesAdded' => $interestResult['propertiesAdded'],
                    'funeralsAdded' => $interestResult['funeralsAdded'],
                    'newPropertyHtml' => $interestResult['newPropertyHtml'],
                    'newFuneralHtml' => $interestResult['newFuneralHtml'],
                    'totalInterests' => $interestResult['totalInterests'],
                    'totalPropertyInterests' => $interestResult['totalPropertyInterests'],
                    'totalFuneralInterests' => $interestResult['totalFuneralInterests'],
                ]);
            }

            return redirect()->to(site_url('prospects/view/' . $clientId))->with('success', 'Seguimiento guardado correctamente.');

        } catch (\Exception $e) {
            if ($this->db->transStatus() !== false) {
                $this->db->transRollback();
            }
            // Log completo con traza para debugging
            log_message('error', '[saveFollowUp] Excepción: ' . $e->getMessage() . '. Archivo: ' . $e->getFile() . ' Linea: ' . $e->getLine());

            // Preparar respuesta AJAX con el error real
            $dbError = $this->db->error();
            $lastQuery = null;
            try {
                 if (method_exists($this->db, 'getLastQuery')) {
                    $lastQuery = (string) $this->db->getLastQuery();
                }
            } catch (\Throwable $t) { /* ignored */ }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false, 
                    'errors' => ['error' => 'Excepción Crítica: ' . $e->getMessage()],
                    'db_error' => $dbError,
                    'last_query' => $lastQuery,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            return redirect()->back()->with('error', 'Excepción Crítica: ' . $e->getMessage());
        }
    }

    // ===== MÉTODOS PARA GESTIÓN DE INTERESES EN INMUEBLES =====

    /**
     * Agregar interés en un inmueble
     */
    public function addPropertyInterest(int $clientId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $rules = [
            'real_estate_id' => 'required|is_natural_no_zero',
            'interest_level' => 'required|in_list[Alto,Medio,Bajo]',
            'notes' => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => $this->validator->getErrors()
            ]);
        }

        $realEstateId = $this->request->getPost('real_estate_id');
        
        // Verificar que el inmueble existe y está disponible
        $property = $this->realEstateModel->find($realEstateId);
        if (!$property) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'El inmueble seleccionado no existe.']
            ]);
        }

        if ($property['status'] === 'Vendido') {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'El inmueble ya está vendido.']
            ]);
        }

        // Verificar que no existe ya un interés para este cliente-inmueble
        $existing = $this->interestModel->where([
            'client_id' => $clientId, 
            'real_estate_id' => $realEstateId
        ])->first();

        if ($existing) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'Ya existe un interés registrado para este inmueble.']
            ]);
        }

        $data = [
            'client_id' => $clientId,
            'real_estate_id' => $realEstateId,
            'interest_level' => $this->request->getPost('interest_level'),
            'notes' => $this->request->getPost('notes'),
            'status' => 'Interesado',
        ];

        if ($this->interestModel->insert($data)) {
            // Obtener los datos completos para el HTML de respuesta
            $interest = $this->interestModel->getClientInterests($clientId);
            $newInterest = end($interest); // Último insertado

            $apartadoAmount = $this->interestModel->calculateApartadoAmount($realEstateId);

            $html = $this->buildInterestHtml($newInterest, $apartadoAmount);

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Interés agregado correctamente.',
                'html' => $html
            ]);
        }

        return $this->response->setJSON([
            'success' => false, 
            'errors' => ['error' => 'Error al guardar el interés.']
        ]);
    }

    /**
     * Quitar interés de un inmueble
     */
    public function removePropertyInterest(int $clientId, int $interestId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $interest = $this->interestModel->find($interestId);
        
        if (!$interest || $interest['client_id'] != $clientId) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'Interés no encontrado.']
            ]);
        }

        // Verificar si se puede eliminar (no hay apartado)
        if (!$this->interestModel->canRemoveInterest($clientId, $interest['real_estate_id'])) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'No se puede eliminar. Ya hay apartado pagado para este inmueble.']
            ]);
        }

        if ($this->interestModel->delete($interestId)) {
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Interés eliminado correctamente.'
            ]);
        }

        return $this->response->setJSON([
            'success' => false, 
            'errors' => ['error' => 'Error al eliminar el interés.']
        ]);
    }

    /**
     * Registrar apartado para un inmueble
     */
    public function registerApartado(int $clientId, int $interestId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $rules = [
            'apartado_receipt' => 'permit_empty|max_length[255]',
            'accepted_difference' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => $this->validator->getErrors()
            ]);
        }

        $interest = $this->interestModel->find($interestId);
        
        if (!$interest || $interest['client_id'] != $clientId) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'Interés no encontrado.']
            ]);
        }

        if (!empty($interest['apartado_amount'])) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'Ya existe un apartado registrado para este inmueble.']
            ]);
        }

        // Obtener datos del cliente y del inmueble
        $client = $this->clientModel->find($clientId);
        $realEstate = $this->realEstateModel->find($interest['real_estate_id']);
        
        if (!$client || !$realEstate) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'Datos no encontrados.']
            ]);
        }

        // VALIDAR DIFERENCIA ENTRE PRECIO Y CRÉDITO
        $propertyPrice = (float) $realEstate['price'];
        $clientCredit = (float) ($client['credit_amount'] ?? 0);
        $difference = $propertyPrice - $clientCredit;
        
        // Si hay diferencia y no se ha confirmado, enviar alerta
        if ($difference > 0 && !$this->request->getPost('accepted_difference')) {
            return $this->response->setJSON([
                'success' => false,
                'requires_confirmation' => true,
                'difference_data' => [
                    'property_price' => $propertyPrice,
                    'client_credit' => $clientCredit,
                    'difference' => $difference,
                    'formatted_price' => '$' . number_format($propertyPrice, 2),
                    'formatted_credit' => '$' . number_format($clientCredit, 2),
                    'formatted_difference' => '$' . number_format($difference, 2)
                ],
                'message' => 'El precio del inmueble excede el monto del crédito autorizado.'
            ]);
        }

        // INICIAR TRANSACCIÓN PARA OPERACIONES MÚLTIPLES
        $this->db->transBegin();

        try {
            $receipt = $this->request->getPost('apartado_receipt');
            
            // 1. Registrar el apartado
            if (!$this->interestModel->registerApartado($clientId, $interest['real_estate_id'], $receipt)) {
                throw new \Exception('Error al registrar el apartado');
            }

            // 2. Cambiar estado del inmueble a 'Apartado' para que no aparezca disponible para otros
            $this->realEstateModel->update($interest['real_estate_id'], ['status' => 'Apartado']);

            // 3. Eliminar todos los OTROS intereses de este cliente (mantener solo el apartado)
            $this->interestModel->where('client_id', $clientId)
                               ->where('id !=', $interestId)
                               ->delete();

            // 4. Eliminar intereses de OTROS clientes en este mismo inmueble
            $this->interestModel->where('real_estate_id', $interest['real_estate_id'])
                               ->where('client_id !=', $clientId)
                               ->delete();

            // 5. CREAR ESTADO DE CUENTA DEL CLIENTE (Prospecto → Cliente)
            $apartadoAmount = $this->interestModel->calculateApartadoAmount($interest['real_estate_id']);
            
            // Crear cuenta
            if (!$this->accountModel->createAccountFromApartado($clientId, $interest['real_estate_id'], $interestId, $apartadoAmount)) {
                throw new \Exception('Error al crear el estado de cuenta');
            }
            
            $accountId = $this->accountModel->getInsertID();
            $currentUserId = session()->get('user_id');
            
            // CREAR CHECKLIST INICIAL DE DOCUMENTOS PARA APARTADO
            // Obtener el método de pago del cliente para crear checklist específico
            $clientData = $this->clientModel->find($clientId);
            $rawPaymentMethod = $clientData['payment_method'] ?? ($clientData['payment_method_id'] ?? null);
            $paymentMethod = $this->resolvePaymentMethodLabel($rawPaymentMethod) ?? 'Todos';
            
            if (!$this->checklistModel->createInitialChecklist($accountId, $paymentMethod)) {
                throw new \Exception('Error al crear el checklist de documentos');
            }
            
            // Crear movimiento de ingreso (apartado)
            if (!$this->movementModel->createApartadoIngreso($accountId, $apartadoAmount, $currentUserId)) {
                throw new \Exception('Error al registrar el ingreso del apartado');
            }
            
            // Crear movimientos de egreso para trámites requeridos
            if (!$this->movementModel->createRequiredDocumentExpenses($accountId, $currentUserId)) {
                throw new \Exception('Error al crear los gastos de trámites');
            }
            
            // REGISTRAR DIFERENCIA CRÉDITO vs PRECIO (si aplica)
            if ($difference > 0 && $this->request->getPost('accepted_difference')) {
                if (!$this->movementModel->createCreditDifferenceCharge($accountId, $difference, $currentUserId)) {
                    throw new \Exception('Error al registrar la diferencia de crédito');
                }
            }
            
            // Actualizar balance inicial
            $this->accountModel->updateBalance($accountId);

            // 6. Cambiar status del cliente de "Prospecto" a "Cliente"
            $this->clientModel->update($clientId, ['status' => 'Cliente']);

            // Confirmar transacción
            $this->db->transCommit();

            // Obtener datos actualizados del apartado registrado
            $updatedInterest = $this->interestModel->getClientInterests($clientId);
            $current = null;
            foreach ($updatedInterest as $item) {
                if ($item['id'] == $interestId) {
                    $current = $item;
                    break;
                }
            }

            $html = $this->buildInterestHtml($current, $current['apartado_amount'] ?? 0);

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Apartado registrado correctamente. Se eliminaron automáticamente los otros intereses.',
                'html' => $html,
                'removeOthers' => true // Flag para el frontend
            ]);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[registerApartado] Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false, 
                'errors' => ['error' => 'Error al procesar el apartado: ' . $e->getMessage()]
            ]);
        }
    }

    /**
     * Construir HTML para mostrar un interés de inmueble
     */
    private function buildInterestHtml(array $interest, float $apartadoAmount): string
    {
        $statusBadge = '';
        switch ($interest['status']) {
            case 'Interesado':
                $statusBadge = '<span class="badge bg-info text-white">Interesado</span>';
                break;
            case 'Apartado':
                $statusBadge = '<span class="badge bg-warning text-dark">Apartado</span>';
                break;
            case 'Vendido':
                $statusBadge = '<span class="badge bg-success">Vendido</span>';
                break;
            case 'Cancelado':
                $statusBadge = '<span class="badge bg-danger">Cancelado</span>';
                break;
        }

        $levelBadge = '';
        switch ($interest['interest_level']) {
            case 'Alto':
                $levelBadge = '<span class="badge bg-danger">Alto</span>';
                break;
            case 'Medio':
                $levelBadge = '<span class="badge bg-warning text-dark">Medio</span>';
                break;
            case 'Bajo':
                $levelBadge = '<span class="badge bg-secondary">Bajo</span>';
                break;
        }

        $price = '$' . number_format($interest['price'] ?? 0, 2);
        $apartadoDisplay = '$' . number_format($apartadoAmount, 2);

        $removeBtn = '';
        if ($interest['status'] === 'Interesado' && empty($interest['apartado_amount'])) {
            $removeBtn = '<button class="btn btn-sm btn-outline-danger btn-remove-interest" data-interest-id="' . $interest['id'] . '"><i class="fa fa-times"></i></button>';
        }

        $apartadoBtn = '';
        if ($interest['status'] === 'Interesado' && empty($interest['apartado_amount'])) {
            $apartadoBtn = '<button class="btn btn-sm btn-warning btn-register-apartado" data-interest-id="' . $interest['id'] . '">Apartar (' . $apartadoDisplay . ')</button>';
        }

        $cancelApartadoBtn = '';
        if ($interest['status'] === 'Apartado') {
            $cancelApartadoBtn = '<button class="btn btn-sm btn-outline-danger btn-cancel-apartado" data-interest-id="' . $interest['id'] . '"><i class="fa fa-ban me-1"></i> Cancelar apartado</button>';
        }

        $apartadoInfo = '';
        if (!empty($interest['apartado_amount'])) {
            $apartadoDate = date('d/m/Y', strtotime($interest['apartado_date']));
            $apartadoInfo = '<div class="mt-1"><small class="text-success"><i class="fa fa-check-circle"></i> Apartado: ' . $apartadoDisplay . ' el ' . $apartadoDate;
            if (!empty($interest['apartado_receipt'])) {
                $apartadoInfo .= ' (Recibo: ' . esc($interest['apartado_receipt']) . ')';
            }
            $apartadoInfo .= '</small></div>';
        }

        return '<div class="card mb-2 interest-item" data-interest-id="' . $interest['id'] . '">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">' . esc($interest['property_name']) . ' <small class="text-muted">(' . esc($interest['clave']) . ')</small></h6>
                        <p class="mb-1 text-sm"><i class="fa fa-map-marker-alt text-primary me-1"></i>' . esc($interest['address']) . '</p>
                        <p class="mb-1"><strong>Precio:</strong> ' . $price . ' | <strong>Interés:</strong> ' . $levelBadge . ' | ' . $statusBadge . '</p>
                        ' . ($interest['notes'] ? '<p class="mb-1 text-sm text-muted">' . esc($interest['notes']) . '</p>' : '') . '
                        ' . $apartadoInfo . '
                    </div>
                    <div class="ms-2">
                        ' . $removeBtn . '
                    </div>
                </div>
                ' . ($apartadoBtn || $cancelApartadoBtn ? '<div class="mt-2 d-flex gap-2 flex-wrap">' . $apartadoBtn . $cancelApartadoBtn . '</div>' : '') . '
            </div>
        </div>';
    }

    /**
     * API para obtener clientes activos (para selects de agenda)
     */
    public function getActiveClients()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        
        // Obtener clientes del asesor actual que estén activos
        $clients = $this->clientModel->where('user_id', $userId)
                                    ->whereIn('status', ['Nuevo', 'Seguimiento', 'Cliente'])
                                    ->orderBy('full_name', 'ASC')
                                    ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'clients' => $clients
        ]);
    }

    /**
     * Registrar intereses en inmuebles y paquetes funerarios desde el formulario unificado de seguimiento.
     */
    private function processFollowUpInterests(int $clientId): array
    {
        $level            = $this->request->getPost('interest_level') ?: 'Medio';
        $notes            = trim((string) $this->request->getPost('interest_notes')) ?: null;

        $propertyIds = array_unique(array_filter(array_map('intval', (array) $this->request->getPost('real_estate_ids'))));
        $funeralIds  = array_unique(array_filter(array_map('intval', (array) $this->request->getPost('funeral_product_ids'))));

        $newPropertyHtml = [];
        $newFuneralHtml  = [];
        $propertiesAdded = 0;
        $funeralsAdded   = 0;

        $prospect = $this->clientModel->find($clientId);

        if (($prospect['status'] ?? '') !== 'Cliente') {
            foreach ($propertyIds as $realEstateId) {
                if ($this->interestModel->where(['client_id' => $clientId, 'real_estate_id' => $realEstateId])->first()) {
                    continue;
                }

                $property = $this->realEstateModel->find($realEstateId);
                if (! $property || $property['status'] === 'Vendido') {
                    continue;
                }

                if ($this->interestModel->insert([
                    'client_id'        => $clientId,
                    'real_estate_id'   => $realEstateId,
                    'interest_level'   => $level,
                    'notes'            => $notes,
                    'status'           => 'Interesado',
                ])) {
                    $interests = $this->interestModel->getClientInterests($clientId);
                    $newInterest = end($interests);
                    $apartadoAmount = $this->interestModel->calculateApartadoAmount($realEstateId);
                    $newPropertyHtml[] = $this->buildInterestHtml($newInterest, $apartadoAmount);
                    $propertiesAdded++;
                }
            }
        }

        if ($this->db->tableExists('client_funeral_interests')) {
            foreach ($funeralIds as $productId) {
                if ($this->funeralInterestModel->where(['client_id' => $clientId, 'funeral_product_id' => $productId])->first()) {
                    continue;
                }

                $product = (new FuneralProductModel())->find($productId);
                if (! $product || $product['status'] !== 'Disponible') {
                    continue;
                }

                if ($this->funeralInterestModel->insert([
                    'client_id'          => $clientId,
                    'funeral_product_id' => $productId,
                    'interest_level'     => $level,
                    'notes'              => $notes,
                    'status'             => 'En análisis',
                ])) {
                    $this->markAsFuneralProspect($clientId);
                    $interests = $this->funeralInterestModel->getClientInterests($clientId);
                    $newInterest = end($interests);
                    $newFuneralHtml[] = $this->buildFuneralInterestHtml($newInterest);
                    $funeralsAdded++;
                }
            }
        }

        $propertyCount = count($this->interestModel->getClientInterests($clientId));
        $totalFuneralInterests = 0;
        if ($this->db->tableExists('client_funeral_interests')) {
            $totalFuneralInterests = count($this->funeralInterestModel->getClientInterests($clientId));
        }

        if ($funeralsAdded > 0) {
            $this->markAsFuneralProspect($clientId);
        }

        return [
            'propertiesAdded' => $propertiesAdded,
            'funeralsAdded'   => $funeralsAdded,
            'newPropertyHtml' => $newPropertyHtml,
            'newFuneralHtml'  => $newFuneralHtml,
            'totalInterests'  => $propertyCount + $totalFuneralInterests,
            'totalPropertyInterests' => $propertyCount,
            'totalFuneralInterests' => $totalFuneralInterests,
        ];
    }

    /**
     * Crear actividad de agenda vinculada a una minuta (seguimiento opcional).
     */
    private function createFollowUpActivity(int $clientId, int $userId): ?int
    {
        $startTime = $this->normalizeDateTimeLocal($this->request->getPost('activity_start_time'));
        $endTime   = $this->normalizeDateTimeLocal($this->request->getPost('activity_end_time'));
        $activityType = (string) $this->request->getPost('activity_type');

        if ($startTime && ! $endTime) {
            $minutes = ($activityType === 'Reunión') ? 60 : 30;
            $endTime = date('Y-m-d H:i:s', strtotime($startTime . ' +' . $minutes . ' minutes'));
        }

        $activityData = [
            'user_id'     => $userId,
            'client_id'   => $clientId,
            'title'       => trim((string) $this->request->getPost('activity_title')),
            'description' => trim((string) $this->request->getPost('activity_description')),
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'type'        => $activityType,
            'status'      => 'Pendiente',
        ];

        $activityId = $this->activityModel->insert($activityData);
        if (! $activityId) {
            log_message('error', '[createFollowUpActivity] Insert failed: ' . json_encode($this->activityModel->errors()) . ' data=' . json_encode($activityData));

            return null;
        }

        $this->syncActivityWithGoogle((int) $activityId, $userId);

        return (int) $activityId;
    }

    private function normalizeDateTimeLocal(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = str_replace('T', ' ', trim($value));
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        return $value;
    }

    /**
     * Sincronizar una actividad específica con Google Calendar incluyendo información del cliente
     */
    private function syncActivityWithGoogle($activityId, $userId)
    {
        try {
            // Verificar si el usuario tiene Google Calendar conectado
            $userModel = model('UserModel');
            $user = $userModel->find($userId);
            
            if (!$user || !$user['google_calendar_connected']) {
                log_message('info', "Usuario $userId no tiene Google Calendar conectado, omitiendo sincronización");
                return false;
            }

            // Obtener la actividad recién creada
            $activity = $this->activityModel->find($activityId);
            if (!$activity) {
                log_message('error', "Actividad $activityId no encontrada para sincronización");
                return false;
            }

            // Enriquecer información de la actividad con datos del cliente
            $enrichedActivity = $activity;
            
            if (!empty($activity['client_id'])) {
                $client = $this->clientModel->find($activity['client_id']);
                if ($client) {
                    // Enriquecer título
                    $originalTitle = $activity['title'];
                    if (stripos($originalTitle, $client['full_name']) === false) {
                        $enrichedActivity['title'] = $originalTitle . ' - ' . $client['full_name'];
                    }
                    
                    // Enriquecer descripción con información completa del cliente
                    $clientInfo = "📋 CLIENTE: " . $client['full_name'] . "\n";
                    $clientInfo .= "📞 Teléfono: " . $client['phone'] . "\n";
                    $clientInfo .= "📧 Email: " . $client['email'] . "\n";
                    
                    if (!empty($client['credit_amount'])) {
                        $clientInfo .= "💰 Crédito: $" . number_format($client['credit_amount'], 2) . "\n";
                    }
                    
                    // Obtener zona del cliente
                    $zone = $this->zoneModel->find($client['zone_id']);
                    if ($zone) {
                        $clientInfo .= "🗺️ Zona: " . $zone['name'] . "\n";
                    }
                    
                    // Método de pago si está disponible
                    if (!empty($client['payment_method_id'])) {
                        $clientInfo .= "💳 Método de Pago: " . $client['payment_method_id'] . "\n";
                    }
                    
                    $clientInfo .= "\n📝 DESCRIPCIÓN ORIGINAL:\n" . ($activity['description'] ?: 'Sin descripción adicional.');
                    $enrichedActivity['description'] = $clientInfo;
                }
            }

            // Inicializar Google Calendar y sincronizar con datos enriquecidos
            $googleCalendar = new \App\Libraries\GoogleCalendar($userId);
            if ($googleCalendar->isConnected()) {
                $result = $googleCalendar->syncEventToGoogle($enrichedActivity);
                
                if ($result) {
                    log_message('info', "Actividad $activityId sincronizada exitosamente con Google Calendar (enriquecida con datos del cliente)");
                    return true;
                } else {
                    log_message('warning', "No se pudo sincronizar la actividad $activityId con Google Calendar");
                    return false;
                }
            }
            
        } catch (\Exception $e) {
            log_message('error', "Error sincronizando actividad $activityId con Google Calendar: " . $e->getMessage());
            return false;
        }
        
        return false;
    }

    /**
     * Actualizar una minuta de seguimiento existente
     * @param int $followUpId
     */
    public function updateFollowUp(int $followUpId)
    {
        // Validar que la minuta existe y pertenece al usuario actual
        $followUp = $this->followUpModel->find($followUpId);
        
        if (!$followUp) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'La minuta no existe o no tienes permisos para editarla.'
            ]);
        }

        // Verificar permisos (solo el autor puede editar)
        $currentUserId = session()->get('user_id');
        if ($followUp['user_id'] != $currentUserId) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'No tienes permisos para editar esta minuta.'
            ]);
        }

        // Reglas de validación
        $rules = [
            'discussion_detail' => 'required|max_length[500]',
            'contact_method'    => 'required',
            'contact_datetime'  => 'required|valid_date',
            'personal_notes'    => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false, 
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            // Asegurar zona horaria correcta para fechas
            date_default_timezone_set(config('App')->appTimezone);
            
            $updateData = [
                'discussion_detail' => $this->request->getPost('discussion_detail'),
                'contact_method'    => $this->request->getPost('contact_method'),
                'contact_datetime'  => $this->request->getPost('contact_datetime'),
                'personal_notes'    => $this->request->getPost('personal_notes'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $updated = $this->followUpModel->update($followUpId, $updateData);

            if ($updated) {
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Minuta actualizada correctamente.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Error al actualizar la minuta.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', '[updateFollowUp] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar una minuta de seguimiento
     * @param int $followUpId
     */
    public function deleteFollowUp(int $followUpId)
    {
        // Validar que la minuta existe y pertenece al usuario actual
        $followUp = $this->followUpModel->find($followUpId);
        
        if (!$followUp) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'La minuta no existe o no tienes permisos para eliminarla.'
            ]);
        }

        // Verificar permisos (solo el autor puede eliminar)
        $currentUserId = session()->get('user_id');
        if ($followUp['user_id'] != $currentUserId) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'No tienes permisos para eliminar esta minuta.'
            ]);
        }

        try {
            // Si la minuta tiene una actividad asociada, preguntar qué hacer
            if (!empty($followUp['activity_id'])) {
                // Por ahora solo eliminamos la minuta, no la actividad
                // En el futuro se podría agregar lógica para preguntar al usuario
                log_message('info', "Eliminando minuta {$followUpId} que tenía actividad asociada {$followUp['activity_id']}");
            }

            $deleted = $this->followUpModel->delete($followUpId);

            if ($deleted) {
                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Minuta eliminada correctamente.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Error al eliminar la minuta.'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', '[deleteFollowUp] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Desactiva/Descarta un prospecto con razón
     * @param int $clientId
     */
    public function deactivate(int $clientId)
    {
        // Validar que es una petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Petición inválida'
            ]);
        }

        // Obtener los datos JSON del body
        $json = $this->request->getJSON(true);
        $reasonId = (int) ($json['reason_id'] ?? 0);
        $notes = trim($json['notes'] ?? '');

        if ($reasonId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes seleccionar un motivo de cancelación'
            ]);
        }

        $reasonModel = new CancellationReasonModel();
        $reason = $reasonModel->find($reasonId);
        if (! $reason || ! in_array($reason['category'], ['prospect', 'both'], true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Motivo de cancelación inválido'
            ]);
        }

        // Verificar que el prospecto existe
        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Prospecto no encontrado'
            ]);
        }

        try {
            // Actualizar el estado del prospecto
            $updateData = [
                'status' => 'Cancelado',
                'deactivation_reason_id' => $reasonId,
                'deactivation_notes' => $notes !== '' ? $notes : null,
                'deactivated_at' => date('Y-m-d H:i:s'),
            ];
            
            $this->clientModel->update($clientId, $updateData);

            $followUpData = [
                'client_id' => $clientId,
                'user_id' => session()->get('user_id'),
                'contact_datetime' => date('Y-m-d H:i:s'),
                'discussion_detail' => "🚫 PROSPECTO CANCELADO\n\nMotivo: {$reason['name']}\n" .
                          ($notes !== '' ? "\nNotas: {$notes}" : ''),
                'activity_id' => null
            ];
            
            $this->followUpModel->insert($followUpData);

            log_message('info', "Prospecto {$clientId} cancelado. Motivo ID: {$reasonId}");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Prospecto cancelado correctamente'
            ]);

        } catch (\Exception $e) {
            log_message('error', '[deactivate] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al descartar el prospecto: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelApartado(int $clientId, int $interestId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        $json = $this->request->getJSON(true);
        $reasonId = (int) ($json['reason_id'] ?? 0);
        $notes = trim($json['notes'] ?? '');

        if ($reasonId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes seleccionar un motivo de cancelación',
            ]);
        }

        $reasonModel = new CancellationReasonModel();
        $reason = $reasonModel->find($reasonId);
        if (! $reason || ! in_array($reason['category'], ['apartado', 'both'], true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Motivo de cancelación inválido',
            ]);
        }

        $interest = $this->interestModel->find($interestId);
        if (! $interest || (int) $interest['client_id'] !== $clientId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Interés no encontrado']);
        }

        if ($interest['status'] !== 'Apartado') {
            return $this->response->setJSON(['success' => false, 'message' => 'Este interés no tiene apartado activo']);
        }

        $this->db->transBegin();

        try {
            $this->interestModel->update($interestId, [
                'status' => 'Cancelado',
                'cancellation_reason_id' => $reasonId,
                'cancellation_notes' => $notes !== '' ? $notes : null,
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancelled_by_user_id' => session()->get('user_id'),
            ]);

            $this->realEstateModel->update($interest['real_estate_id'], ['status' => 'Disponible']);

            $account = $this->accountModel->where('real_estate_interest_id', $interestId)
                ->where('client_id', $clientId)
                ->first();
            if ($account) {
                $this->accountModel->update($account['id'], [
                    'status' => 'Cerrada',
                    'notes' => trim(($account['notes'] ?? '') . "\nCuenta cerrada por cancelación de apartado."),
                ]);
            }

            $this->clientModel->update($clientId, ['status' => 'Seguimiento']);

            $this->followUpModel->insert([
                'client_id' => $clientId,
                'user_id' => session()->get('user_id'),
                'contact_datetime' => date('Y-m-d H:i:s'),
                'discussion_detail' => "🚫 APARTADO CANCELADO\n\nMotivo: {$reason['name']}\n" .
                    ($notes !== '' ? "\nNotas: {$notes}" : ''),
            ]);

            $this->db->transCommit();

            $updatedInterest = null;
            foreach ($this->interestModel->getClientInterests($clientId) as $item) {
                if ((int) $item['id'] === $interestId) {
                    $updatedInterest = $item;
                    break;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Apartado cancelado correctamente',
                'html' => $updatedInterest ? $this->buildInterestHtml($updatedInterest, $updatedInterest['apartado_amount'] ?? 0) : '',
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[cancelApartado] ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cancelar apartado: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Planes de financiamiento de un producto funerario (para formulario de prospecto).
     */
    public function getFuneralProductPlans(int $productId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        $product = (new FuneralProductModel())->find($productId);
        if (! $product || $product['status'] !== 'Disponible') {
            return $this->response->setJSON(['success' => false, 'plans' => []]);
        }

        $plans = (new FuneralFinancingPlanModel())->getByProduct($productId, true);
        foreach ($plans as &$plan) {
            $plan['total_estimado'] = \App\Helpers\CatalogoFunerarioHelper::totalPlan($plan);
        }

        return $this->response->setJSON(['success' => true, 'plans' => $plans, 'product' => $product]);
    }

    public function addFuneralInterest(int $clientId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        if (! $this->db->tableExists('client_funeral_interests')) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Módulo funerario no migrado.']]);
        }

        $rules = [
            'funeral_product_id'  => 'required|is_natural_no_zero',
            'interest_level'      => 'required|in_list[Alto,Medio,Bajo]',
            'notes'               => 'permit_empty|max_length[500]',
            'plan_ids'            => 'permit_empty',
            'selected_plan_id'    => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $productId = (int) $this->request->getPost('funeral_product_id');
        $product   = (new FuneralProductModel())->find($productId);
        if (! $product || $product['status'] !== 'Disponible') {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Paquete no disponible.']]);
        }

        if ($this->funeralInterestModel->where(['client_id' => $clientId, 'funeral_product_id' => $productId])->first()) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Ya hay interés registrado en este paquete.']]);
        }

        $planIds        = array_map('intval', (array) $this->request->getPost('plan_ids'));
        $selectedPlanId = $this->request->getPost('selected_plan_id') ? (int) $this->request->getPost('selected_plan_id') : null;

        if ($selectedPlanId && ! in_array($selectedPlanId, $planIds, true)) {
            $planIds[] = $selectedPlanId;
        }

        try {
            $err = $this->validateFuneralPlans($productId, $planIds, $selectedPlanId);
            if ($err) {
                return $this->response->setJSON(['success' => false, 'errors' => ['error' => $err]]);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => $e->getMessage()]]);
        }

        $data = [
            'client_id'                  => $clientId,
            'funeral_product_id'         => $productId,
            'interest_level'             => $this->request->getPost('interest_level'),
            'selected_financing_plan_id' => $selectedPlanId,
            'notes'                      => $this->request->getPost('notes'),
            'status'                     => $this->funeralInterestModel->resolveStatus(['selected_financing_plan_id' => $selectedPlanId], $planIds),
        ];

        if ($this->funeralInterestModel->insert($data)) {
            $this->markAsFuneralProspect($clientId);
            $interestId = (int) $this->funeralInterestModel->getInsertID();
            $this->funeralInterestModel->syncConsideredPlans($interestId, $planIds);
            $interests = $this->funeralInterestModel->getClientInterests($clientId);
            $interest = null;
            foreach ($interests as $row) {
                if ((int) $row['id'] === $interestId) {
                    $interest = $row;
                    break;
                }
            }

            return $this->response->setJSON(array_merge([
                'success' => true,
                'message' => 'Interés funerario registrado.',
                'html'    => $this->buildFuneralInterestHtml($interest),
            ], $this->funeralListSyncPayload($clientId)));
        }

        return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'No se pudo guardar el interés.']]);
    }

    public function updateFuneralInterest(int $clientId, int $interestId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        $interest = $this->funeralInterestModel->find($interestId);
        if (! $interest || (int) $interest['client_id'] !== $clientId) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Interés no encontrado.']]);
        }

        if (! $this->funeralInterestModel->canEdit($interestId)) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'No se puede editar un interés apartado o vendido.']]);
        }

        $rules = [
            'interest_level'    => 'permit_empty|in_list[Alto,Medio,Bajo]',
            'notes'             => 'permit_empty|max_length[500]',
            'plan_ids'          => 'permit_empty',
            'selected_plan_id'  => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $planIds        = array_map('intval', (array) $this->request->getPost('plan_ids'));
        $selectedPlanId = $this->request->getPost('selected_plan_id') ? (int) $this->request->getPost('selected_plan_id') : null;

        if ($selectedPlanId && ! in_array($selectedPlanId, $planIds, true)) {
            $planIds[] = $selectedPlanId;
        }

        try {
            $err = $this->validateFuneralPlans((int) $interest['funeral_product_id'], $planIds, $selectedPlanId);
            if ($err) {
                return $this->response->setJSON(['success' => false, 'errors' => ['error' => $err]]);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => $e->getMessage()]]);
        }

        $update = [
            'interest_level'             => $this->request->getPost('interest_level') ?: $interest['interest_level'],
            'selected_financing_plan_id' => $selectedPlanId,
            'notes'                      => $this->request->getPost('notes'),
            'status'                     => $this->funeralInterestModel->resolveStatus(['selected_financing_plan_id' => $selectedPlanId], $planIds, $interest),
        ];

        if ($this->funeralInterestModel->update($interestId, $update)) {
            $this->funeralInterestModel->syncConsideredPlans($interestId, $planIds);
            $updated = null;
            foreach ($this->funeralInterestModel->getClientInterests($clientId) as $row) {
                if ((int) $row['id'] === $interestId) {
                    $updated = $row;
                    break;
                }
            }

            return $this->response->setJSON(array_merge([
                'success' => true,
                'message' => 'Interés funerario actualizado.',
                'html'    => $updated ? $this->buildFuneralInterestHtml($updated) : '',
            ], $this->funeralListSyncPayload($clientId)));
        }

        return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'No se pudo actualizar.']]);
    }

    public function removeFuneralInterest(int $clientId, int $interestId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        $interest = $this->funeralInterestModel->find($interestId);
        if (! $interest || (int) $interest['client_id'] !== $clientId) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Interés no encontrado.']]);
        }

        if (! $this->funeralInterestModel->canRemove($interestId)) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'No se puede eliminar un interés ya definido.']]);
        }

        if ($this->funeralInterestModel->delete($interestId)) {
            return $this->response->setJSON(array_merge([
                'success' => true,
                'message' => 'Interés funerario eliminado.',
            ], $this->funeralListSyncPayload($clientId)));
        }

        return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Error al eliminar.']]);
    }

    private function validateFuneralPlans(int $productId, array $planIds, ?int $selectedPlanId): ?string
    {
        if (empty($planIds) && ! $selectedPlanId) {
            return null;
        }

        $validIds = array_map('intval', array_column((new FuneralFinancingPlanModel())->getByProduct($productId, true), 'id'));
        foreach ($planIds as $planId) {
            if ($planId && ! in_array($planId, $validIds, true)) {
                return 'Plan de financiamiento inválido.';
            }
        }
        if ($selectedPlanId && ! in_array($selectedPlanId, $validIds, true)) {
            return 'Plan seleccionado inválido.';
        }

        return null;
    }

    public function registerFuneralApartado(int $clientId, int $interestId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        $interest = $this->funeralInterestModel->find($interestId);
        if (! $interest || (int) $interest['client_id'] !== $clientId) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Interés no encontrado.']]);
        }

        if (! empty($interest['apartado_amount']) || $interest['status'] === 'Apartado') {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Ya hay apartado registrado.']]);
        }

        if (empty($interest['selected_financing_plan_id'])) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Debes elegir un plan de financiamiento antes de apartar.']]);
        }

        $product = (new FuneralProductModel())->find((int) $interest['funeral_product_id']);
        $plan    = (new FuneralFinancingPlanModel())->find((int) $interest['selected_financing_plan_id']);
        if (! $product || $product['status'] !== 'Disponible' || ! $plan) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Paquete o plan no disponible.']]);
        }

        $apartadoAmount = $this->funeralInterestModel->calculateApartadoAmount($interestId);
        if ($apartadoAmount <= 0) {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'El enganche del plan debe ser mayor a cero.']]);
        }

        $this->db->transBegin();
        try {
            $receipt = $this->request->getPost('apartado_receipt');

            if (! $this->funeralInterestModel->registerApartado($interestId, $receipt)) {
                throw new \Exception('Error al registrar apartado funerario');
            }

            (new FuneralProductModel())->update((int) $interest['funeral_product_id'], ['status' => 'No disponible']);

            $this->funeralInterestModel->where('client_id', $clientId)->where('id !=', $interestId)->delete();
            $this->funeralInterestModel->where('funeral_product_id', $interest['funeral_product_id'])
                ->where('client_id !=', $clientId)->delete();

            if (! $this->accountModel->createFuneralAccountFromApartado($clientId, (int) $interest['funeral_product_id'], $interestId, $apartadoAmount)) {
                throw new \Exception('Error al crear estado de cuenta funerario');
            }

            $accountId     = (int) $this->accountModel->getInsertID();
            $currentUserId = (int) session()->get('user_id');

            if (! $this->movementModel->createFuneralApartadoIngreso($accountId, $apartadoAmount, $currentUserId, $plan['name'] ?? null)) {
                throw new \Exception('Error al registrar ingreso del enganche');
            }

            $saldoFinanciar = max(0, \App\Helpers\CatalogoFunerarioHelper::totalPlan($plan) - $apartadoAmount);
            if (! $this->movementModel->createFuneralFinancingBalance($accountId, $saldoFinanciar, $currentUserId, $plan)) {
                throw new \Exception('Error al registrar saldo por financiar');
            }

            $this->accountModel->updateBalance($accountId);

            $client = $this->clientModel->find($clientId);
            if ($client && $client['status'] !== 'Cliente') {
                $this->clientModel->update($clientId, ['status' => 'Cliente']);
            }

            $this->followUpModel->insert([
                'client_id'          => $clientId,
                'user_id'            => $currentUserId,
                'contact_datetime'   => date('Y-m-d H:i:s'),
                'discussion_detail'  => "✅ APARTADO FUNERARIO\n\nPaquete: {$product['name']}\nPlan: {$plan['name']}\nEnganche: $" . number_format($apartadoAmount, 2),
            ]);

            $this->db->transCommit();

            $updated = null;
            foreach ($this->funeralInterestModel->getClientInterests($clientId) as $row) {
                if ((int) $row['id'] === $interestId) {
                    $updated = $row;
                    break;
                }
            }

            return $this->response->setJSON([
                'success'      => true,
                'message'      => 'Apartado funerario registrado. Estado de cuenta creado.',
                'html'         => $updated ? $this->buildFuneralInterestHtml($updated) : '',
                'reload'       => true,
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[registerFuneralApartado] ' . $e->getMessage());

            return $this->response->setJSON(['success' => false, 'errors' => ['error' => $e->getMessage()]]);
        }
    }

    public function cancelFuneralApartado(int $clientId, int $interestId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        $json     = $this->request->getJSON(true);
        $reasonId = (int) ($json['reason_id'] ?? 0);
        $notes    = trim($json['notes'] ?? '');

        if ($reasonId <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Debes seleccionar un motivo de cancelación']);
        }

        $reason = (new CancellationReasonModel())->find($reasonId);
        if (! $reason || ! in_array($reason['category'], ['apartado', 'both'], true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Motivo inválido']);
        }

        $interest = $this->funeralInterestModel->find($interestId);
        if (! $interest || (int) $interest['client_id'] !== $clientId || $interest['status'] !== 'Apartado') {
            return $this->response->setJSON(['success' => false, 'message' => 'Apartado funerario no encontrado']);
        }

        $this->db->transBegin();
        try {
            $this->funeralInterestModel->update($interestId, [
                'status'                 => 'Cancelado',
                'cancellation_reason_id' => $reasonId,
                'cancellation_notes'     => $notes !== '' ? $notes : null,
                'cancelled_at'           => date('Y-m-d H:i:s'),
                'cancelled_by_user_id'   => session()->get('user_id'),
            ]);

            (new FuneralProductModel())->update((int) $interest['funeral_product_id'], ['status' => 'Disponible']);

            $account = $this->accountModel->where('funeral_interest_id', $interestId)->where('client_id', $clientId)->first();
            if ($account) {
                $this->accountModel->update($account['id'], [
                    'status' => 'Cerrada',
                    'notes'  => trim(($account['notes'] ?? '') . "\nCuenta funeraria cerrada por cancelación de apartado."),
                ]);
            }

            if (! $this->accountModel->getClientActiveAccount($clientId)) {
                $this->clientModel->update($clientId, ['status' => 'Seguimiento']);
            }

            $this->followUpModel->insert([
                'client_id'         => $clientId,
                'user_id'           => session()->get('user_id'),
                'contact_datetime'  => date('Y-m-d H:i:s'),
                'discussion_detail' => "🚫 APARTADO FUNERARIO CANCELADO\n\nMotivo: {$reason['name']}\n" . ($notes !== '' ? "\nNotas: {$notes}" : ''),
            ]);

            $this->db->transCommit();

            $updated = null;
            foreach ($this->funeralInterestModel->getClientInterests($clientId) as $row) {
                if ((int) $row['id'] === $interestId) {
                    $updated = $row;
                    break;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Apartado funerario cancelado',
                'html'    => $updated ? $this->buildFuneralInterestHtml($updated) : '',
                'reload'  => true,
            ]);
        } catch (\Exception $e) {
            $this->db->transRollback();

            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markFuneralSold(int $clientId, int $interestId)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->back();
        }

        $interest = $this->funeralInterestModel->find($interestId);
        if (! $interest || (int) $interest['client_id'] !== $clientId || $interest['status'] !== 'Apartado') {
            return $this->response->setJSON(['success' => false, 'errors' => ['error' => 'Solo se puede marcar vendido un apartado activo.']]);
        }

        $this->funeralInterestModel->update($interestId, ['status' => 'Vendido']);

        $account = $this->accountModel->where('funeral_interest_id', $interestId)->where('client_id', $clientId)->first();
        if ($account && $account['status'] === 'Activa') {
            $this->accountModel->update($account['id'], [
                'status' => 'Cerrada',
                'notes'  => trim(($account['notes'] ?? '') . "\nVenta funeraria completada."),
            ]);
        }

        $updated = null;
        foreach ($this->funeralInterestModel->getClientInterests($clientId) as $row) {
            if ((int) $row['id'] === $interestId) {
                $updated = $row;
                break;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Paquete funerario marcado como vendido.',
            'html'    => $updated ? $this->buildFuneralInterestHtml($updated) : '',
        ]);
    }

    private function buildFuneralInterestHtml(array $interest): string
    {
        return view('prospects/partials/funeral_interest_card', ['interest' => $interest]);
    }
}