<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientRealEstateInterestModel extends Model
{
    protected $table            = 'client_real_estate_interests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'client_id',
        'real_estate_id', 
        'interest_level',
        'notes',
        'apartado_amount',
        'apartado_date',
        'apartado_receipt',
        'status',
        'cancellation_reason_id',
        'cancellation_notes',
        'cancelled_at',
        'cancelled_by_user_id',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene los intereses de inmuebles de un cliente con detalles del inmueble
     */
    public function getClientInterests(int $clientId)
    {
        return $this->select('
                client_real_estate_interests.*,
                real_estates.clave,
                real_estates.name as property_name,
                real_estates.price,
                real_estates.address,
                real_estates.status as property_status,
                real_estate_projects.name as project_name,
                zones.name as zone_name
            ')
            ->join('real_estates', 'real_estates.id = client_real_estate_interests.real_estate_id')
            ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
            ->join('zones', 'zones.id = real_estates.zone_id', 'left')
            ->where('client_real_estate_interests.client_id', $clientId)
            ->orderBy('client_real_estate_interests.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Verifica si se puede eliminar un interés (no tiene apartado)
     */
    public function canRemoveInterest(int $clientId, int $realEstateId): bool
    {
        $interest = $this->where(['client_id' => $clientId, 'real_estate_id' => $realEstateId])->first();
        
        if (!$interest) {
            return false; // No existe el interés
        }

        // No se puede eliminar si hay apartado pagado
        return empty($interest['apartado_amount']) || $interest['status'] === 'Cancelado';
    }

    /**
     * Calcula el monto del apartado (1% del precio del inmueble)
     */
    public function calculateApartadoAmount(int $realEstateId): float
    {
        $realEstateModel = new \App\Models\RealEstateModel();
        $property = $realEstateModel->find($realEstateId);
        
        if (!$property || !$property['price']) {
            return 0.0;
        }

        return round($property['price'] * 0.01, 2); // 1% del precio
    }

    /**
     * Registra el pago del apartado
     */
    public function registerApartado(int $clientId, int $realEstateId, string $receipt = null): bool
    {
        $apartadoAmount = $this->calculateApartadoAmount($realEstateId);
        
        if ($apartadoAmount <= 0) {
            return false;
        }

        $data = [
            'apartado_amount' => $apartadoAmount,
            'apartado_date' => date('Y-m-d H:i:s'),
            'apartado_receipt' => $receipt,
            'status' => 'Apartado',
        ];

        return $this->where(['client_id' => $clientId, 'real_estate_id' => $realEstateId])
                    ->set($data)
                    ->update();
    }
}