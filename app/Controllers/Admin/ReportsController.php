<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Models\ClientFollowUpModel;
use App\Models\UserModel;
use App\Models\ZoneModel;
use App\Models\AcquisitionSourceModel;

class ReportsController extends BaseController
{
    protected $clientModel;
    protected $followUpModel;
    protected $userModel;
    protected $zoneModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->followUpModel = new ClientFollowUpModel();
        $this->userModel = new UserModel();
        $this->zoneModel = new ZoneModel();
    }

    /**
     * Reporte de seguimiento de prospectos
     * Muestra la última actividad y tiempo sin seguimiento
     */
    public function prospectsFollowUp()
    {
        // Obtener usuarios para el filtro
        $data['users'] = $this->userModel->findAll();
        
        return view('admin/reports/prospects_follow_up', $data);
    }

    /**
     * API para obtener datos del reporte de seguimiento
     */
    public function getProspectsFollowUpData()
    {
        // Validar que es una petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Petición inválida'
            ]);
        }

        // Obtener parámetros de filtro
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $userId = $this->request->getGet('user_id');
        $status = $this->request->getGet('status');

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('clients c');

            // Selección con subconsulta para obtener la última minuta
            $builder->select("
                c.id,
                c.full_name,
                c.phone,
                c.email,
                c.status,
                c.credit_amount,
                c.created_at as registration_date,
                u.full_name as asesor_name,
                z.name as zone_name,
                (SELECT MAX(contact_datetime) 
                 FROM client_follow_ups 
                 WHERE client_id = c.id) as last_follow_up_date,
                (SELECT discussion_detail 
                 FROM client_follow_ups 
                 WHERE client_id = c.id 
                 ORDER BY contact_datetime DESC 
                 LIMIT 1) as last_notes,
                CASE 
                    WHEN (SELECT MAX(contact_datetime) FROM client_follow_ups WHERE client_id = c.id) IS NOT NULL 
                    THEN (SELECT MAX(contact_datetime) FROM client_follow_ups WHERE client_id = c.id)
                    ELSE c.created_at
                END as last_activity_date
            ");

            $builder->join('users u', 'u.id = c.user_id', 'left');
            $builder->join('zones z', 'z.id = c.zone_id', 'left');

            // Excluir prospectos cerrados y descartados por defecto
            $builder->whereNotIn('c.status', ['Cierre', 'Descartado', 'Cancelado']);
            
            // Aplicar filtros
            if (!empty($userId) && $userId !== 'all') {
                $builder->where('c.user_id', $userId);
            }

            if (!empty($status) && $status !== 'all') {
                $builder->where('c.status', $status);
            }

            $builder->orderBy('c.id', 'DESC');

            $prospects = $builder->get()->getResultArray();
            
            // Filtrar por fechas en PHP si se especificaron
            if (!empty($startDate) && !empty($endDate)) {
                $prospects = array_filter($prospects, function($p) use ($startDate, $endDate) {
                    $activityDate = $p['last_activity_date'];
                    return $activityDate >= $startDate . ' 00:00:00' && $activityDate <= $endDate . ' 23:59:59';
                });
            }

            // Calcular días sin actividad para cada prospecto
            $now = new \DateTime();
            foreach ($prospects as &$prospect) {
                $lastActivityDate = new \DateTime($prospect['last_activity_date']);
                $interval = $now->diff($lastActivityDate);
                $prospect['days_without_activity'] = $interval->days;
                
                // Clasificar según días sin actividad
                if ($interval->days <= 7) {
                    $prospect['activity_status'] = 'recent';
                    $prospect['activity_label'] = 'Reciente';
                } elseif ($interval->days <= 30) {
                    $prospect['activity_status'] = 'moderate';
                    $prospect['activity_label'] = 'Atención';
                } elseif ($interval->days <= 90) {
                    $prospect['activity_status'] = 'old';
                    $prospect['activity_label'] = 'Requiere seguimiento';
                } else {
                    $prospect['activity_status'] = 'critical';
                    $prospect['activity_label'] = 'Sin seguimiento';
                }

                // Formatear última nota (solo primeros 100 caracteres)
                if (!empty($prospect['last_notes'])) {
                    $prospect['last_notes_short'] = strlen($prospect['last_notes']) > 100 
                        ? substr($prospect['last_notes'], 0, 100) . '...' 
                        : $prospect['last_notes'];
                } else {
                    $prospect['last_notes_short'] = 'Sin minutas registradas';
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $prospects,
                'total' => count($prospects)
            ]);

        } catch (\Exception $e) {
            log_message('error', '[getProspectsFollowUpData] Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener los datos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Exportar reporte a Excel/CSV
     */
    public function exportProspectsFollowUp()
    {
        // Similar a getProspectsFollowUpData pero genera un CSV
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $userId = $this->request->getGet('user_id');
        $status = $this->request->getGet('status');

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('clients c');

            $builder->select("
                c.full_name,
                c.phone,
                c.email,
                c.status,
                c.credit_amount,
                c.created_at as registration_date,
                u.full_name as asesor_name,
                z.name as zone_name,
                (SELECT MAX(contact_datetime) FROM client_follow_ups WHERE client_id = c.id) as last_follow_up_date,
                (SELECT discussion_detail FROM client_follow_ups WHERE client_id = c.id ORDER BY contact_datetime DESC LIMIT 1) as last_notes,
                CASE 
                    WHEN (SELECT MAX(contact_datetime) FROM client_follow_ups WHERE client_id = c.id) IS NOT NULL 
                    THEN (SELECT MAX(contact_datetime) FROM client_follow_ups WHERE client_id = c.id)
                    ELSE c.created_at
                END as last_activity_date,
                DATEDIFF(NOW(), CASE 
                    WHEN (SELECT MAX(contact_datetime) FROM client_follow_ups WHERE client_id = c.id) IS NOT NULL 
                    THEN (SELECT MAX(contact_datetime) FROM client_follow_ups WHERE client_id = c.id)
                    ELSE c.created_at
                END) as days_without_activity
            ");

            $builder->join('users u', 'u.id = c.user_id', 'left');
            $builder->join('zones z', 'z.id = c.zone_id', 'left');

            // Excluir prospectos cerrados y descartados
            $builder->whereNotIn('c.status', ['Cierre', 'Descartado', 'Cancelado']);

            if (!empty($userId) && $userId !== 'all') {
                $builder->where('c.user_id', $userId);
            }

            if (!empty($status) && $status !== 'all') {
                $builder->where('c.status', $status);
            }

            $builder->orderBy('c.id', 'DESC');
            $prospects = $builder->get()->getResultArray();
            
            // Filtrar por fechas en PHP si se especificaron
            if (!empty($startDate) && !empty($endDate)) {
                $prospects = array_filter($prospects, function($p) use ($startDate, $endDate) {
                    $activityDate = $p['last_activity_date'];
                    return $activityDate >= $startDate . ' 00:00:00' && $activityDate <= $endDate . ' 23:59:59';
                });
            }

            // Generar CSV
            $filename = 'reporte_seguimiento_' . date('Y-m-d_His') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($output, [
                'Prospecto',
                'Teléfono',
                'Email',
                'Estado',
                'Crédito',
                'Zona',
                'Asesor',
                'Fecha Registro',
                'Última Actividad',
                'Días sin Actividad',
                'Última Nota'
            ]);

            // Datos
            foreach ($prospects as $prospect) {
                fputcsv($output, [
                    $prospect['full_name'],
                    $prospect['phone'],
                    $prospect['email'],
                    $prospect['status'],
                    '$' . number_format($prospect['credit_amount'], 2),
                    $prospect['zone_name'] ?? 'N/A',
                    $prospect['asesor_name'] ?? 'Sin asignar',
                    $prospect['registration_date'],
                    $prospect['last_activity_date'],
                    $prospect['days_without_activity'],
                    $prospect['last_notes'] ?? 'Sin notas'
                ]);
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            log_message('error', '[exportProspectsFollowUp] Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * Reporte de pipeline de clientes: compró, en trámite, apartado, cancelados.
     */
    public function clientPipeline()
    {
        return view('admin/reports/client_pipeline', [
            'users' => $this->userModel->findAll(),
        ]);
    }

    public function getClientPipelineData()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Petición inválida']);
        }

        $userId         = $this->request->getGet('user_id');
        $pipelineStage  = $this->request->getGet('pipeline_stage');
        $startDate      = $this->request->getGet('start_date');
        $endDate        = $this->request->getGet('end_date');

        try {
            $rows = $this->fetchPipelineRows($userId, $pipelineStage, $startDate, $endDate);
            $cancellationStats = $this->fetchCancellationStats($userId, $startDate, $endDate);

            $summary = [
                'compro'      => 0,
                'en_tramite'  => 0,
                'apartado'    => 0,
                'cancelado'   => 0,
                'interesado'  => 0,
            ];

            foreach ($rows as &$row) {
                $row['pipeline_label'] = $this->pipelineLabel($row['pipeline_stage']);
                $summary[$row['pipeline_stage']] = ($summary[$row['pipeline_stage']] ?? 0) + 1;
            }

            return $this->response->setJSON([
                'success'            => true,
                'data'               => $rows,
                'summary'            => $summary,
                'cancellation_stats' => $cancellationStats,
                'total'              => count($rows),
            ]);
        } catch (\Exception $e) {
            log_message('error', '[getClientPipelineData] ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
            ]);
        }
    }

    public function exportClientPipeline()
    {
        $userId        = $this->request->getGet('user_id');
        $pipelineStage = $this->request->getGet('pipeline_stage');
        $startDate     = $this->request->getGet('start_date');
        $endDate       = $this->request->getGet('end_date');

        try {
            $rows = $this->fetchPipelineRows($userId, $pipelineStage, $startDate, $endDate);
            $filename = 'reporte_clientes_pipeline_' . date('Y-m-d_His') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, [
                'Cliente', 'Teléfono', 'Email', 'Etapa', 'Inmueble', 'Clave',
                'Asesor', 'Zona', 'Motivo cancelación', 'Notas cancelación', 'Fecha cancelación',
            ]);

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['full_name'],
                    $row['phone'],
                    $row['email'],
                    $this->pipelineLabel($row['pipeline_stage']),
                    $row['property_name'] ?? '',
                    $row['property_clave'] ?? '',
                    $row['asesor_name'] ?? '',
                    $row['zone_name'] ?? '',
                    $row['cancellation_reason'] ?? '',
                    $row['cancellation_notes'] ?? '',
                    $row['cancelled_at'] ?? '',
                ]);
            }

            fclose($output);
            exit;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchPipelineRows(?string $userId, ?string $pipelineStage, ?string $startDate, ?string $endDate): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('clients c');

        $builder->select("
            c.id as client_id,
            c.full_name,
            c.phone,
            c.email,
            c.status as client_status,
            u.full_name as asesor_name,
            z.name as zone_name,
            cri.id as interest_id,
            cri.status as interest_status,
            cri.apartado_date,
            re.name as property_name,
            re.clave as property_clave,
            re.status as property_status,
            ca.status as account_status,
            cr_cancel.name as cancellation_reason,
            COALESCE(cri.cancellation_notes, c.deactivation_notes) as cancellation_notes,
            COALESCE(cri.cancelled_at, c.deactivated_at) as cancelled_at,
            CASE
                WHEN cri.status = 'Vendido' OR (re.status = 'Vendido' AND re.sold_to_client_id = c.id) THEN 'compro'
                WHEN cri.status = 'Cancelado' OR c.status IN ('Descartado', 'Cancelado') THEN 'cancelado'
                WHEN cri.status = 'Apartado' AND ca.status = 'Activa' THEN 'en_tramite'
                WHEN cri.status = 'Apartado' THEN 'apartado'
                ELSE 'interesado'
            END AS pipeline_stage
        ", false);

        $builder->join('users u', 'u.id = c.user_id', 'left');
        $builder->join('zones z', 'z.id = c.zone_id', 'left');
        $builder->join(
            'client_real_estate_interests cri',
            'cri.id = (
                SELECT cri2.id FROM client_real_estate_interests cri2
                WHERE cri2.client_id = c.id
                ORDER BY FIELD(cri2.status, \'Vendido\', \'Apartado\', \'Cancelado\', \'Interesado\'), cri2.updated_at DESC
                LIMIT 1
            )',
            'left',
            false
        );
        $builder->join('real_estates re', 're.id = cri.real_estate_id', 'left');
        $builder->join('client_accounts ca', 'ca.real_estate_interest_id = cri.id AND ca.client_id = c.id', 'left');
        $builder->join(
            'cancellation_reasons cr_cancel',
            'cr_cancel.id = COALESCE(cri.cancellation_reason_id, c.deactivation_reason_id)',
            'left',
            false
        );

        $builder->whereIn('c.status', ['Cliente', 'Descartado', 'Cancelado', 'Seguimiento', 'Cierre']);
        $builder->where('(c.client_type = \'comprador\' OR c.client_type IS NULL OR c.client_type = \'\')');

        if (! empty($userId) && $userId !== 'all') {
            $builder->where('c.user_id', $userId);
        }

        if (! empty($startDate) && ! empty($endDate)) {
            $builder->groupStart()
                ->where('cri.apartado_date >=', $startDate . ' 00:00:00')
                ->where('cri.apartado_date <=', $endDate . ' 23:59:59')
                ->groupEnd();
        }

        $builder->orderBy('c.full_name', 'ASC');
        $rows = $builder->get()->getResultArray();

        if (! empty($pipelineStage) && $pipelineStage !== 'all') {
            $rows = array_values(array_filter($rows, static fn ($row) => ($row['pipeline_stage'] ?? '') === $pipelineStage));
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchCancellationStats(?string $userId, ?string $startDate, ?string $endDate): array
    {
        $db = \Config\Database::connect();
        $stats = [];

        $prospectBuilder = $db->table('clients c')
            ->select('cr.name as reason_name, COUNT(*) as total, \'Prospecto\' as source_type')
            ->join('cancellation_reasons cr', 'cr.id = c.deactivation_reason_id')
            ->whereIn('c.status', ['Descartado', 'Cancelado'])
            ->groupBy('cr.id, cr.name');

        if (! empty($userId) && $userId !== 'all') {
            $prospectBuilder->where('c.user_id', $userId);
        }
        if (! empty($startDate) && ! empty($endDate)) {
            $prospectBuilder->where('c.deactivated_at >=', $startDate . ' 00:00:00');
            $prospectBuilder->where('c.deactivated_at <=', $endDate . ' 23:59:59');
        }

        foreach ($prospectBuilder->get()->getResultArray() as $row) {
            $stats[] = $row;
        }

        $apartadoBuilder = $db->table('client_real_estate_interests cri')
            ->select('cr.name as reason_name, COUNT(*) as total, \'Apartado\' as source_type')
            ->join('cancellation_reasons cr', 'cr.id = cri.cancellation_reason_id')
            ->join('clients c', 'c.id = cri.client_id')
            ->where('cri.status', 'Cancelado')
            ->groupBy('cr.id, cr.name');

        if (! empty($userId) && $userId !== 'all') {
            $apartadoBuilder->where('c.user_id', $userId);
        }
        if (! empty($startDate) && ! empty($endDate)) {
            $apartadoBuilder->where('cri.cancelled_at >=', $startDate . ' 00:00:00');
            $apartadoBuilder->where('cri.cancelled_at <=', $endDate . ' 23:59:59');
        }

        foreach ($apartadoBuilder->get()->getResultArray() as $row) {
            $stats[] = $row;
        }

        usort($stats, static fn ($a, $b) => (int) $b['total'] <=> (int) $a['total']);

        return $stats;
    }

    private function pipelineLabel(string $stage): string
    {
        return match ($stage) {
            'compro'     => 'Compró casa',
            'en_tramite' => 'En trámite',
            'apartado'   => 'Apartado',
            'cancelado'  => 'Cancelado',
            default      => 'Interés',
        };
    }

    public function acquisitionSources()
    {
        return view('admin/reports/acquisition_sources', [
            'users' => $this->userModel->findAll(),
        ]);
    }

    public function getAcquisitionSourcesData()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Petición inválida']);
        }

        $userId    = $this->request->getGet('user_id');
        $startDate = $this->request->getGet('start_date') ?: null;
        $endDate   = $this->request->getGet('end_date') ?: null;

        try {
            $sourceModel = new AcquisitionSourceModel();
            $stats = $sourceModel->getReportStats($startDate, $endDate, $userId);

            $topSource = null;
            if (! empty($stats['by_source'])) {
                $topSource = $stats['by_source'][0]['source_name'] ?? null;
            }

            return $this->response->setJSON([
                'success'    => true,
                'by_source'  => $stats['by_source'],
                'by_channel' => $stats['by_channel'],
                'totals'     => $stats['totals'],
                'top_source' => $topSource,
            ]);
        } catch (\Exception $e) {
            log_message('error', '[getAcquisitionSourcesData] ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
            ]);
        }
    }
}
