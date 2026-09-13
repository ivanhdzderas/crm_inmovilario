<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientFuneralInterestModel extends Model
{
    public const PAYMENT_FREQUENCIES = ['Semanal', 'Quincenal', 'Mensual', 'Fin de mes'];

    protected $table         = 'client_funeral_interests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'client_id',
        'funeral_product_id',
        'interest_level',
        'payment_frequency',
        'selected_financing_plan_id',
        'notes',
        'status',
        'apartado_amount',
        'apartado_date',
        'apartado_receipt',
        'cancellation_reason_id',
        'cancellation_notes',
        'cancelled_at',
        'cancelled_by_user_id',
    ];
    protected $useTimestamps = true;

    public function getClientInterests(int $clientId): array
    {
        $rows = $this->select('
                client_funeral_interests.*,
                funeral_products.clave,
                funeral_products.name as product_name,
                funeral_products.base_price,
                funeral_products.slug as product_slug,
                funeral_locations.name as location_name,
                funeral_locations.slug as location_slug,
                funeral_homes.name as home_name,
                funeral_homes.slug as home_slug,
                funeral_product_types.name as type_name,
                selected_plan.name as selected_plan_name,
                selected_plan.enganche as selected_enganche,
                selected_plan.mensualidad as selected_mensualidad,
                selected_plan.months as selected_months
            ')
            ->join('funeral_products', 'funeral_products.id = client_funeral_interests.funeral_product_id')
            ->join('funeral_locations', 'funeral_locations.id = funeral_products.funeral_location_id')
            ->join('funeral_homes', 'funeral_homes.id = funeral_products.funeral_home_id')
            ->join('funeral_product_types', 'funeral_product_types.id = funeral_products.product_type_id', 'left')
            ->join('funeral_financing_plans as selected_plan', 'selected_plan.id = client_funeral_interests.selected_financing_plan_id', 'left')
            ->where('client_funeral_interests.client_id', $clientId)
            ->orderBy('client_funeral_interests.created_at', 'DESC')
            ->findAll() ?: [];

        foreach ($rows as &$row) {
            $row['considered_plans'] = $this->getConsideredPlans((int) $row['id']);
        }

        return $rows;
    }

    public function getConsideredPlans(int $interestId): array
    {
        return $this->db->table('client_funeral_interest_plans cfp')
            ->select('funeral_financing_plans.*')
            ->join('funeral_financing_plans', 'funeral_financing_plans.id = cfp.funeral_financing_plan_id')
            ->where('cfp.client_funeral_interest_id', $interestId)
            ->orderBy('funeral_financing_plans.sort_order', 'ASC')
            ->orderBy('funeral_financing_plans.months', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function syncConsideredPlans(int $interestId, array $planIds): void
    {
        $this->db->table('client_funeral_interest_plans')
            ->where('client_funeral_interest_id', $interestId)
            ->delete();

        $planIds = array_values(array_unique(array_filter(array_map('intval', $planIds))));
        $now     = date('Y-m-d H:i:s');

        foreach ($planIds as $planId) {
            $this->db->table('client_funeral_interest_plans')->insert([
                'client_funeral_interest_id' => $interestId,
                'funeral_financing_plan_id'  => $planId,
                'created_at'                 => $now,
            ]);
        }
    }

    public function resolveStatus(array $data, array $planIds, ?array $current = null): string
    {
        if ($current && in_array($current['status'] ?? '', ['Apartado', 'Vendido', 'Cancelado'], true)) {
            return $current['status'];
        }

        if (! empty($data['selected_financing_plan_id'])) {
            return 'Definido';
        }

        if (count($planIds) >= 1) {
            return 'En análisis';
        }

        return 'Interesado';
    }

    public function calculateApartadoAmount(int $interestId): float
    {
        $interest = $this->find($interestId);
        if (! $interest || empty($interest['selected_financing_plan_id'])) {
            return 0.0;
        }

        $plan = (new FuneralFinancingPlanModel())->find((int) $interest['selected_financing_plan_id']);

        return $plan ? round((float) $plan['enganche'], 2) : 0.0;
    }

    public function registerApartado(int $interestId, ?string $receipt = null): bool
    {
        $amount = $this->calculateApartadoAmount($interestId);
        if ($amount <= 0) {
            return false;
        }

        return $this->update($interestId, [
            'apartado_amount'  => $amount,
            'apartado_date'    => date('Y-m-d H:i:s'),
            'apartado_receipt' => $receipt,
            'status'           => 'Apartado',
        ]);
    }

    public function canRemove(int $interestId): bool
    {
        $interest = $this->find($interestId);
        if (! $interest) {
            return false;
        }

        if (in_array($interest['status'], ['Apartado', 'Vendido'], true)) {
            return false;
        }

        return empty($interest['apartado_amount']);
    }

    public function canEdit(int $interestId): bool
    {
        $interest = $this->find($interestId);

        return $interest && ! in_array($interest['status'], ['Apartado', 'Vendido', 'Cancelado'], true);
    }
}
