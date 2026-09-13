<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientDocumentCatalogModel extends Model
{
    protected $table            = 'client_document_catalog';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'description',
        'category',
        'document_type',
        'process_stage',
        'payment_method',
        'estimated_days',
        'requires_notary',
        'cost_estimate',
        'is_active',
        'sort_order',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene documentos activos por etapa del proceso
     */
    public function getByProcessStage(string $stage): array
    {
        return $this->where(['is_active' => 1, 'process_stage' => $stage])
                   ->orderBy('sort_order', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene documentos activos por categoría
     */
    public function getByCategory(string $category): array
    {
        return $this->where(['is_active' => 1, 'category' => $category])
                   ->orderBy('sort_order', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene todas las categorías disponibles
     */
    public function getCategories(): array
    {
        $db = \Config\Database::connect();
        
        $query = $db->table($this->table)
                   ->select('category')
                   ->where('is_active', 1)
                   ->groupBy('category')
                   ->orderBy('category', 'ASC')
                   ->get();
                   
        $results = $query->getResultArray();
        
        return array_column($results, 'category');
    }

    /**
     * Obtiene todas las etapas del proceso disponibles
     */
    public function getProcessStages(): array
    {
        // Retornar en orden lógico del proceso
        $allStages = ['Apartado', 'Escrituración', 'Entrega', 'Post-venta'];
        
        $db = \Config\Database::connect();
        
        $query = $db->table($this->table)
                   ->select('process_stage')
                   ->where('is_active', 1)
                   ->groupBy('process_stage')
                   ->get();
                   
        $existingStages = array_column($query->getResultArray(), 'process_stage');
        
        // Retornar solo las etapas que existen en el orden correcto
        return array_intersect($allStages, $existingStages);
    }

    /**
     * Obtiene documentos obligatorios para una etapa
     */
    public function getRequiredByStage(string $stage): array
    {
        return $this->where([
                    'is_active' => 1, 
                    'process_stage' => $stage,
                    'document_type' => 'Obligatorio'
                ])
                ->orderBy('sort_order', 'ASC')
                ->findAll();
    }

    /**
     * Calcula costo total estimado por etapa
     */
    public function getTotalCostByStage(string $stage): float
    {
        $result = $this->selectSum('cost_estimate')
                      ->where(['is_active' => 1, 'process_stage' => $stage])
                      ->first();
        
        return (float) ($result['cost_estimate'] ?? 0);
    }

    /**
     * Calcula días estimados totales por etapa
     */
    public function getTotalDaysByStage(string $stage): int
    {
        $result = $this->selectSum('estimated_days')
                      ->where(['is_active' => 1, 'process_stage' => $stage])
                      ->first();
        
        return (int) ($result['estimated_days'] ?? 0);
    }

    /**
     * Obtiene documentos con información de notario
     */
    public function getNotaryDocuments(): array
    {
        return $this->where(['is_active' => 1, 'requires_notary' => true])
                   ->orderBy('process_stage', 'ASC')
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Busca documentos por nombre o descripción
     */
    public function searchDocuments(string $query): array
    {
        return $this->where('is_active', 1)
                   ->groupStart()
                       ->like('name', $query)
                       ->orLike('description', $query)
                   ->groupEnd()
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene documentos por método de pago
     */
    public function getByPaymentMethod(string $paymentMethod): array
    {
        return $this->where(['is_active' => 1])
                   ->groupStart()
                       ->where('payment_method', $paymentMethod)
                       ->orWhere('payment_method', 'Todos')
                   ->groupEnd()
                   ->orderBy('process_stage', 'ASC')
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene documentos por etapa y método de pago
     */
    public function getByStageAndPaymentMethod(string $stage, string $paymentMethod): array
    {
        return $this->where(['is_active' => 1, 'process_stage' => $stage])
                   ->groupStart()
                       ->where('payment_method', $paymentMethod)
                       ->orWhere('payment_method', 'Todos')
                   ->groupEnd()
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene todos los métodos de pago disponibles
     */
    public function getPaymentMethods(): array
    {
        $db = \Config\Database::connect();
        
        $query = $db->table($this->table)
                   ->select('payment_method')
                   ->where('is_active', 1)
                   ->groupBy('payment_method')
                   ->orderBy('payment_method', 'ASC')
                   ->get();
                   
        $results = $query->getResultArray();
        
        return array_column($results, 'payment_method');
    }

    /**
     * Calcula costo total por método de pago y etapa
     */
    public function getTotalCostByPaymentMethodAndStage(string $paymentMethod, string $stage): float
    {
        $db = \Config\Database::connect();
        
        $query = $db->table($this->table)
                   ->selectSum('cost_estimate')
                   ->where(['is_active' => 1, 'process_stage' => $stage])
                   ->groupStart()
                       ->where('payment_method', $paymentMethod)
                       ->orWhere('payment_method', 'Todos')
                   ->groupEnd()
                   ->get();
                   
        $result = $query->getRowArray();
        
        return (float) ($result['cost_estimate'] ?? 0);
    }

    /**
     * Obtiene estadísticas por método de pago
     */
    public function getStatsByPaymentMethod(string $paymentMethod): array
    {
        $documents = $this->getByPaymentMethod($paymentMethod);
        
        $stats = [
            'total' => count($documents),
            'obligatorios' => 0,
            'opcionales' => 0,
            'condicionales' => 0,
            'con_notario' => 0,
            'costo_total' => 0,
            'dias_max' => 0
        ];
        
        foreach ($documents as $doc) {
            if ($doc['document_type'] === 'Obligatorio') $stats['obligatorios']++;
            if ($doc['document_type'] === 'Opcional') $stats['opcionales']++;
            if ($doc['document_type'] === 'Condicional') $stats['condicionales']++;
            if ($doc['requires_notary']) $stats['con_notario']++;
            
            $stats['costo_total'] += $doc['cost_estimate'];
            $stats['dias_max'] = max($stats['dias_max'], $doc['estimated_days']);
        }
        
        return $stats;
    }

    /**
     * Obtiene todos los tipos de crédito disponibles dinámicamente
     */
    public function getCreditTypes(): array
    {
        $result = $this->select('payment_method, COUNT(*) as document_count')
                       ->where('is_active', 1)
                       ->where('payment_method !=', 'Todos')
                       ->groupBy('payment_method')
                       ->orderBy('payment_method')
                       ->findAll();
        
        $creditTypes = [];
        foreach ($result as $row) {
            $creditTypes[] = [
                'name' => $row['payment_method'],
                'document_count' => $row['document_count']
            ];
        }
        
        return $creditTypes;
    }

    /**
     * Obtiene documentos de un tipo de crédito específico
     */
    public function getDocumentsByCreditType(string $creditType): array
    {
        return $this->where('payment_method', $creditType)
                    ->where('is_active', 1)
                    ->orderBy('process_stage')
                    ->orderBy('sort_order')
                    ->findAll();
    }

    /**
     * Verifica si un tipo de crédito ya existe
     */
    public function creditTypeExists(string $creditType): bool
    {
        return $this->where('payment_method', $creditType)->countAllResults() > 0;
    }

    /**
     * Crea un nuevo tipo de crédito con un documento placeholder
     */
    public function createCreditType(string $creditTypeName): bool
    {
        $data = [
            'name' => 'Documento Base - ' . $creditTypeName,
            'description' => 'Documento inicial para ' . $creditTypeName,
            'category' => 'General',
            'document_type' => 'Obligatorio',
            'process_stage' => 'Apartado',
            'payment_method' => $creditTypeName,
            'requires_notary' => 0,
            'cost_estimate' => 0,
            'is_active' => 1,
            'sort_order' => 1
        ];
        
        return $this->insert($data) !== false;
    }

    /**
     * Elimina un tipo de crédito y todos sus documentos asociados
     */
    public function deleteCreditTypeAndDocuments(string $creditType): bool
    {
        return $this->where('payment_method', $creditType)->delete();
    }

    /**
     * Obtiene el siguiente número de orden para un tipo de crédito
     */
    public function getNextSortOrder(string $creditType): int
    {
        $maxOrder = $this->where('payment_method', $creditType)
                         ->selectMax('sort_order')
                         ->first();
        
        return ($maxOrder['sort_order'] ?? 0) + 1;
    }

    /**
     * Actualiza el orden de documentos
     */
    public function updateDocumentOrder(array $documentIds): bool
    {
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            foreach ($documentIds as $index => $documentId) {
                $this->update($documentId, ['sort_order' => $index + 1]);
            }
            
            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }
}