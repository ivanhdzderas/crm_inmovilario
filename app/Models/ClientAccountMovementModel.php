<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientAccountMovementModel extends Model
{
    protected $table            = 'client_account_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'client_account_id',
        'document_catalog_id',
        'type',
        'amount',
        'description',
        'status',
        'receipt_number',
        'provider',
        'due_date',
        'completed_date',
        'notes',
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
     * Obtiene movimientos de una cuenta con detalles
     */
    public function getAccountMovements(int $accountId)
    {
        return $this->select('
                client_account_movements.*,
                document_catalog.name as document_name,
                document_catalog.category as document_category,
                users.full_name as created_by_name
            ')
            ->join('document_catalog', 'document_catalog.id = client_account_movements.document_catalog_id', 'left')
            ->join('users', 'users.id = client_account_movements.created_by_user_id', 'left')
            ->where('client_account_movements.client_account_id', $accountId)
            ->orderBy('client_account_movements.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Crea el movimiento de ingreso del apartado
     */
    public function createApartadoIngreso(int $accountId, float $amount, int $userId): bool
    {
        $data = [
            'client_account_id' => $accountId,
            'document_catalog_id' => null, // No es un trámite
            'type' => 'Ingreso',
            'amount' => $amount,
            'description' => 'Apartado inicial del inmueble (1%)',
            'status' => 'Completado',
            'completed_date' => date('Y-m-d H:i:s'),
            'created_by_user_id' => $userId,
        ];

        return $this->insert($data) !== false;
    }

    public function createFuneralApartadoIngreso(int $accountId, float $amount, int $userId, ?string $planName = null): bool
    {
        $label = $planName ? "Enganche / apartado ({$planName})" : 'Enganche / apartado funerario';

        return $this->insert([
            'client_account_id'   => $accountId,
            'document_catalog_id' => null,
            'type'                => 'Ingreso',
            'amount'              => $amount,
            'description'         => $label,
            'status'              => 'Completado',
            'completed_date'      => date('Y-m-d H:i:s'),
            'created_by_user_id'  => $userId,
        ]) !== false;
    }

    public function createFuneralFinancingBalance(int $accountId, float $amount, int $userId, array $plan): bool
    {
        if ($amount <= 0) {
            return true;
        }

        $freq = (int) ($plan['months'] ?? 0);
        $desc = 'Saldo por financiar (' . ($plan['name'] ?? 'plan') . ', ' . $freq . ' meses)';

        return $this->insert([
            'client_account_id'   => $accountId,
            'document_catalog_id' => null,
            'type'                => 'Egreso',
            'amount'              => $amount,
            'description'         => $desc,
            'status'              => 'Pendiente',
            'due_date'            => date('Y-m-d', strtotime('+30 days')),
            'created_by_user_id'  => $userId,
            'notes'               => 'Mensualidad ref. $' . number_format((float) ($plan['mensualidad'] ?? 0), 2),
        ]) !== false;
    }
    public function createRequiredDocumentExpenses(int $accountId, int $userId): bool
    {
        $documentModel = new \App\Models\DocumentCatalogModel();
        $requiredDocs = $documentModel->getRequired();

        if (empty($requiredDocs)) {
            return true; // No hay documentos requeridos
        }

        $movementsData = [];
        foreach ($requiredDocs as $doc) {
            $movementsData[] = [
                'client_account_id' => $accountId,
                'document_catalog_id' => $doc['id'],
                'type' => 'Egreso',
                'amount' => $doc['default_cost'],
                'description' => 'Trámite requerido: ' . $doc['name'],
                'status' => 'Pendiente',
                'due_date' => date('Y-m-d', strtotime('+30 days')), // 30 días por defecto
                'created_by_user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        return $this->insertBatch($movementsData) !== false;
    }

    /**
     * Actualiza el status de un movimiento
     */
    public function updateMovementStatus(int $movementId, string $status, array $additionalData = []): bool
    {
        $data = ['status' => $status];
        
        if ($status === 'Completado') {
            $data['completed_date'] = date('Y-m-d H:i:s');
        }

        if (!empty($additionalData)) {
            $data = array_merge($data, $additionalData);
        }

        $result = $this->update($movementId, $data);
        
        if ($result) {
            // Actualizar balance de la cuenta
            $movement = $this->find($movementId);
            if ($movement) {
                $accountModel = new \App\Models\ClientAccountModel();
                $accountModel->updateBalance($movement['client_account_id']);
            }
        }

        return $result;
    }

    /**
     * Crea cargo por diferencia entre crédito y precio del inmueble
     */
    public function createCreditDifferenceCharge(int $accountId, float $difference, int $userId): bool
    {
        $data = [
            'client_account_id' => $accountId,
            'document_catalog_id' => null, // No es un documento específico
            'type' => 'Egreso',
            'amount' => $difference,
            'description' => 'Diferencia entre precio del inmueble y monto de crédito autorizado',
            'status' => 'Pendiente',
            'receipt_number' => null,
            'provider' => 'Sistema',
            'due_date' => date('Y-m-d', strtotime('+30 days')), // 30 días para cubrir
            'completed_date' => null,
            'notes' => 'Monto adicional requerido para completar la compra del inmueble',
            'created_by_user_id' => $userId,
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Obtiene resumen de movimientos por cuenta
     */
    public function getAccountSummary(int $accountId): array
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('client_account_movements')
                   ->select('
                       type,
                       status,
                       COUNT(*) as count,
                       SUM(amount) as total_amount
                   ')
                   ->where('client_account_id', $accountId)
                   ->groupBy(['type', 'status'])
                   ->get();

        return $query->getResultArray();
    }

    /**
     * Obtiene movimientos vencidos
     */
    public function getOverdueMovements()
    {
        return $this->select('
                client_account_movements.*,
                document_catalog.name as document_name,
                clients.full_name as client_name,
                real_estates.clave as property_clave
            ')
            ->join('client_accounts', 'client_accounts.id = client_account_movements.client_account_id')
            ->join('clients', 'clients.id = client_accounts.client_id')
            ->join('real_estates', 'real_estates.id = client_accounts.real_estate_id')
            ->join('document_catalog', 'document_catalog.id = client_account_movements.document_catalog_id', 'left')
            ->where('client_account_movements.status', 'Pendiente')
            ->where('client_account_movements.due_date <', date('Y-m-d'))
            ->orderBy('client_account_movements.due_date', 'ASC')
            ->findAll();
    }
}