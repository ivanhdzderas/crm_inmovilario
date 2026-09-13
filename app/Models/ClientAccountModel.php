<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientAccountModel extends Model
{
    protected $table            = 'client_accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'client_id',
        'account_type',
        'real_estate_id',
        'real_estate_interest_id',
        'funeral_product_id',
        'funeral_interest_id',
        'apartado_amount',
        'total_expenses',
        'current_balance',
        'status',
        'notes',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene la cuenta activa de un cliente
     */
    public function getClientActiveAccount(int $clientId)
    {
        $builder = $this->select('
                client_accounts.*,
                clients.full_name as client_name,
                real_estates.name as property_name,
                real_estates.clave as property_clave,
                real_estates.price as property_price
            ')
            ->join('clients', 'clients.id = client_accounts.client_id')
            ->join('real_estates', 'real_estates.id = client_accounts.real_estate_id', 'left')
            ->where('client_accounts.client_id', $clientId)
            ->where('client_accounts.status', 'Activa');

        if ($this->db->fieldExists('account_type', 'client_accounts')) {
            $builder->where('client_accounts.account_type', 'inmueble');
        } else {
            $builder->where('client_accounts.real_estate_id IS NOT NULL');
        }

        return $builder->first();
    }

    public function getClientFuneralActiveAccount(int $clientId): ?array
    {
        if (! $this->db->fieldExists('account_type', 'client_accounts')) {
            return null;
        }

        return $this->select('
                client_accounts.*,
                clients.full_name as client_name,
                funeral_products.name as product_name,
                funeral_products.clave as product_clave,
                funeral_products.base_price as product_price,
                funeral_locations.name as location_name,
                funeral_homes.name as home_name,
                client_funeral_interests.payment_frequency,
                client_funeral_interests.selected_financing_plan_id,
                selected_plan.name as plan_name,
                selected_plan.enganche as plan_enganche,
                selected_plan.mensualidad as plan_mensualidad,
                selected_plan.months as plan_months
            ')
            ->join('clients', 'clients.id = client_accounts.client_id')
            ->join('funeral_products', 'funeral_products.id = client_accounts.funeral_product_id')
            ->join('funeral_locations', 'funeral_locations.id = funeral_products.funeral_location_id')
            ->join('funeral_homes', 'funeral_homes.id = funeral_products.funeral_home_id')
            ->join('client_funeral_interests', 'client_funeral_interests.id = client_accounts.funeral_interest_id', 'left')
            ->join('funeral_financing_plans as selected_plan', 'selected_plan.id = client_funeral_interests.selected_financing_plan_id', 'left')
            ->where('client_accounts.client_id', $clientId)
            ->where('client_accounts.account_type', 'funerario')
            ->where('client_accounts.status', 'Activa')
            ->first();
    }

    /**
     * Crea una nueva cuenta para un cliente apartado
     */
    public function createAccountFromApartado(int $clientId, int $realEstateId, int $interestId, float $apartadoAmount): bool
    {
        $data = [
            'client_id'               => $clientId,
            'real_estate_id'          => $realEstateId,
            'real_estate_interest_id' => $interestId,
            'apartado_amount'         => $apartadoAmount,
            'total_expenses'          => 0.00,
            'current_balance'         => $apartadoAmount,
            'status'                  => 'Activa',
            'notes'                   => 'Cuenta creada automáticamente al apartar inmueble',
        ];
        if ($this->db->fieldExists('account_type', 'client_accounts')) {
            $data['account_type'] = 'inmueble';
        }

        return $this->insert($data) !== false;
    }

    public function createFuneralAccountFromApartado(int $clientId, int $productId, int $interestId, float $apartadoAmount): bool
    {
        $data = [
            'client_id'           => $clientId,
            'funeral_product_id'  => $productId,
            'funeral_interest_id' => $interestId,
            'apartado_amount'     => $apartadoAmount,
            'total_expenses'      => 0.00,
            'current_balance'     => $apartadoAmount,
            'status'              => 'Activa',
            'notes'               => 'Cuenta funeraria creada al registrar enganche/apartado',
        ];
        if ($this->db->fieldExists('account_type', 'client_accounts')) {
            $data['account_type'] = 'funerario';
        }

        return $this->insert($data) !== false;
    }

    /**
     * Actualiza el balance después de un movimiento
     */
    public function updateBalance(int $accountId): bool
    {
        $db = \Config\Database::connect();
        
        // Calcular totales desde los movimientos
        $query = $db->table('client_account_movements')
                   ->select('
                       SUM(CASE WHEN type = "Ingreso" AND status = "Completado" THEN amount ELSE 0 END) as total_ingresos,
                       SUM(CASE WHEN type = "Egreso" AND status = "Completado" THEN amount ELSE 0 END) as total_egresos
                   ')
                   ->where('client_account_id', $accountId)
                   ->get();

        $totals = $query->getRowArray();
        
        $totalIngresos = (float) ($totals['total_ingresos'] ?? 0);
        $totalEgresos = (float) ($totals['total_egresos'] ?? 0);
        $newBalance = $totalIngresos - $totalEgresos;

        return $this->update($accountId, [
            'total_expenses' => $totalEgresos,
            'current_balance' => $newBalance
        ]);
    }

    /**
     * Obtiene resumen financiero de todas las cuentas
     */
    public function getFinancialSummary()
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('client_accounts')
                   ->select('
                       COUNT(*) as total_accounts,
                       SUM(apartado_amount) as total_apartados,
                       SUM(total_expenses) as total_gastos,
                       SUM(current_balance) as balance_total,
                       SUM(CASE WHEN current_balance > 0 THEN current_balance ELSE 0 END) as balance_positivo,
                       SUM(CASE WHEN current_balance < 0 THEN ABS(current_balance) ELSE 0 END) as balance_negativo
                   ')
                   ->where('status', 'Activa')
                   ->get();

        return $query->getRowArray();
    }

    /**
     * Obtiene cuentas con balance negativo (números rojos)
     */
    public function getAccountsInRed()
    {
        return $this->select('
                client_accounts.*,
                clients.full_name as client_name,
                real_estates.name as property_name,
                real_estates.clave as property_clave
            ')
            ->join('clients', 'clients.id = client_accounts.client_id')
            ->join('real_estates', 'real_estates.id = client_accounts.real_estate_id')
            ->where('client_accounts.current_balance <', 0)
            ->where('client_accounts.status', 'Activa')
            ->orderBy('client_accounts.current_balance', 'ASC')
            ->findAll();
    }
}