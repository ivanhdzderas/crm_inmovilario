<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientDocumentChecklistModel extends Model
{
    protected $table            = 'client_document_checklist';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'client_account_id',
        'client_document_catalog_id',
        'status',
        'completed_date',
        'file_path',
        'notes',
        'actual_cost',
        'assigned_to_user_id',
        'created_by_user_id',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene checklist organizado por método de pago y etapa de proceso
     */
    public function getAccountChecklistOrganized(int $accountId): array
    {
        $checklist = $this->select('
                client_document_checklist.*,
                client_document_catalog.name as document_name,
                client_document_catalog.description as document_description,
                client_document_catalog.category,
                client_document_catalog.document_type,
                client_document_catalog.process_stage,
                client_document_catalog.payment_method,
                client_document_catalog.requires_notary,
                client_document_catalog.cost_estimate,
                client_document_catalog.sort_order
            ')
            ->join('client_document_catalog', 'client_document_catalog.id = client_document_checklist.client_document_catalog_id')
            ->where('client_document_checklist.client_account_id', $accountId)
            ->orderBy('client_document_catalog.payment_method, client_document_catalog.process_stage, client_document_catalog.sort_order')
            ->findAll();

        // Organizar por método de pago y etapa
        $organized = [];
        foreach ($checklist as $item) {
            $paymentMethod = $item['payment_method'] ?? 'General';
            $stage = $item['process_stage'];
            $organized[$paymentMethod][$stage][] = $item;
        }

        return $organized;
    }

    /**
     * Obtiene estadísticas simples del checklist
     */
    public function getChecklistStats(int $accountId): array
    {
        $total = $this->where('client_account_id', $accountId)->countAllResults();
        $completed = $this->where('client_account_id', $accountId)
                          ->where('status', 'Completado')
                          ->countAllResults();
        $pending = $this->where('client_account_id', $accountId)
                        ->whereIn('status', ['Pendiente', 'En Proceso'])
                        ->countAllResults();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'progress_percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0
        ];
    }

    /**
     * Crea checklist inicial automáticamente basado en método de pago
     */
    public function createInitialChecklist(int $accountId, string $paymentMethod = null): bool
    {
        $catalogModel = new ClientDocumentCatalogModel();
        
        // Obtener documentos según el método de pago
        if ($paymentMethod && $paymentMethod !== 'Todos') {
            $documents = $catalogModel->whereIn('payment_method', ['Todos', $paymentMethod])
                                    ->where('is_active', 1)
                                    ->orderBy('payment_method DESC, sort_order')
                                    ->findAll();
        } else {
            $documents = $catalogModel->where('payment_method', 'Todos')
                                    ->where('is_active', 1)
                                    ->orderBy('sort_order')
                                    ->findAll();
        }

        if (empty($documents)) {
            return true;
        }

        // Crear entradas del checklist
        $checklistData = [];
        foreach ($documents as $document) {
            $checklistData[] = [
                'client_account_id' => $accountId,
                'client_document_catalog_id' => $document['id'],
                'status' => 'Pendiente',
                'actual_cost' => $document['cost_estimate'],
                'created_by_user_id' => session()->get('user_id') ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        return $this->insertBatch($checklistData) !== false;
    }

    /**
     * Actualiza el estado de un documento
     */
    public function updateDocumentStatus(int $checklistId, string $status, array $additionalData = []): bool
    {
        $updateData = array_merge(['status' => $status], $additionalData);
        
        if ($status === 'Completado') {
            $updateData['completed_date'] = date('Y-m-d H:i:s');
        }

        return $this->update($checklistId, $updateData);
    }

    /**
     * Obtiene resumen por método de pago
     */
    public function getPaymentMethodSummary(int $accountId): array
    {
        return $this->select('
                client_document_catalog.payment_method,
                COUNT(*) as total,
                SUM(CASE WHEN client_document_checklist.status = "Completado" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN client_document_checklist.status IN ("Pendiente", "En Proceso") THEN 1 ELSE 0 END) as pending
            ')
            ->join('client_document_catalog', 'client_document_catalog.id = client_document_checklist.client_document_catalog_id')
            ->where('client_account_id', $accountId)
            ->groupBy('client_document_catalog.payment_method')
            ->orderBy('client_document_catalog.payment_method')
            ->findAll();
    }

    /**
     * Obtiene tipos de crédito dinámicamente desde el catálogo
     */
    public function getAvailablePaymentMethods(): array
    {
        $catalogModel = new ClientDocumentCatalogModel();
        
        return $catalogModel->select('payment_method')
                          ->distinct()
                          ->where('is_active', 1)
                          ->whereNotNull('payment_method')
                          ->orderBy('payment_method')
                          ->findColumn('payment_method');
    }
}