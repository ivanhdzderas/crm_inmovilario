<?php

namespace App\Models;

use CodeIgniter\Model;

class RealEstateModel extends Model
{
    protected $table            = 'real_estates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'clave',
        'name',
        'description_general',
        'is_new',
        'price',
        'apartado_type',
        'apartado_percentage',
        'apartado_fixed_amount',
        'apartado_description',
        'property_type_id',
        'zone_id',
        'payment_methods',
        'address',
        'google_maps_link',
        'status',
        'sold_to_client_id',
        'project_id',
        'created_by_user_id',
        'updated_by_user_id',
        'brought_by_user_id',
        'sales_commission_type',
        'sales_commission_percentage',
        'sales_commission_fixed_amount',
        'brought_commission_type',
        'brought_commission_percentage',
        'brought_commission_fixed_amount',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // En tu RealEstateModel.php
    public function getAllWithProjects()
    {
        return $this->select('real_estates.*, real_estate_projects.name as project_name, 
                             created_users.full_name as created_by_name,
                             brought_users.full_name as brought_by_name')
                    ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
                    ->join('users as created_users', 'real_estates.created_by_user_id = created_users.id', 'left')
                    ->join('users as brought_users', 'real_estates.brought_by_user_id = brought_users.id', 'left')
                    ->orderBy('real_estates.id', 'DESC')
                    ->findAll();
    } 
    
    public function getAllWithProjectsAndMainPhoto()
    {
         return $this->select('real_estates.*, real_estate_projects.name as project_name, 
                              MIN(real_estate_photos.file_path) as main_photo_path,
                              created_users.full_name as created_by_name,
                              brought_users.full_name as brought_by_name')
                    ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
                    ->join('real_estate_photos', 'real_estate_photos.real_estate_id = real_estates.id', 'left')
                    ->join('users as created_users', 'real_estates.created_by_user_id = created_users.id', 'left')
                    ->join('users as brought_users', 'real_estates.brought_by_user_id = brought_users.id', 'left')
                    ->groupBy('real_estates.id')
                    ->orderBy('real_estates.id', 'ASC')
                    ->findAll();
    }

    public function getRealEstateWithDetails($id)
    {
        return $this->select('real_estates.*, real_estate_projects.name as project_name,
                             real_estate_projects.description as project_description,
                             real_estate_projects.location as project_location,
                             zones.name as zone_name,
                             created_users.full_name as created_by_name,
                             brought_users.full_name as brought_by_name')
                    ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
                    ->join('zones', 'zones.id = real_estates.zone_id', 'left')
                    ->join('users as created_users', 'real_estates.created_by_user_id = created_users.id', 'left')
                    ->join('users as brought_users', 'real_estates.brought_by_user_id = brought_users.id', 'left')
                    ->where('real_estates.id', $id)
                    ->first();
    }

    /**
     * Consulta base del catálogo público.
     */
    public function getPublicCatalogQuery()
    {
        return $this->asArray()
            ->select('real_estates.*, real_estate_projects.name as project_name, real_estate_projects.id as project_id, zones.name as zone_name')
            ->join('real_estate_projects', 'real_estate_projects.id = real_estates.project_id', 'left')
            ->join('zones', 'zones.id = real_estates.zone_id', 'left')
            ->whereIn('real_estates.status', ['Disponible', 'Nuevo', 'Apartado']);
    }

    public function getPublicCatalog(array $filters = []): array
    {
        $builder = $this->getPublicCatalogQuery();

        if (! empty($filters['zone_id'])) {
            $builder->where('real_estates.zone_id', $filters['zone_id']);
        }
        if (! empty($filters['min_price'])) {
            $builder->where('real_estates.price >=', $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $builder->where('real_estates.price <=', $filters['max_price']);
        }
        if (! empty($filters['payment_method'])) {
            $builder->like('real_estates.payment_methods', '"' . $filters['payment_method'] . '"');
        }
        if (! empty($filters['project_id'])) {
            $builder->where('real_estates.project_id', $filters['project_id']);
        }

        return $builder->orderBy('real_estate_projects.name', 'ASC')
            ->orderBy('real_estates.name', 'ASC')
            ->findAll() ?: [];
    }

    /**
     * Obtiene estadísticas mensuales para el dashboard
     */
    public function getMonthlyStats($year = null, $month = null, $userId = null)
    {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        
        $stats = [
            'casas_traidas' => 0,
            'inmuebles_vendidos' => 0,
            'comisiones_venta' => 0,
            'comisiones_captacion' => 0
        ];
        
        // Casas captadas por el usuario
        if ($userId) {
            $model1 = new RealEstateModel();
            $stats['casas_traidas'] = $model1->where('brought_by_user_id', $userId)
                ->where('YEAR(created_at)', $year)
                ->where('MONTH(created_at)', $month)
                ->countAllResults();
        } else {
            // Todas las casas captadas del mes
            $model1 = new RealEstateModel();
            $stats['casas_traidas'] = $model1->where('brought_by_user_id IS NOT NULL')
                ->where('YEAR(created_at)', $year)
                ->where('MONTH(created_at)', $month)
                ->countAllResults();
        }
        
        // Inmuebles vendidos del mes
        $model2 = new RealEstateModel();
        $stats['inmuebles_vendidos'] = $model2->where('status', 'Vendido')
            ->where('YEAR(updated_at)', $year)
            ->where('MONTH(updated_at)', $month)
            ->countAllResults();
        
        // Comisiones de venta (solo para el usuario específico si se proporciona)
        $model3 = new RealEstateModel();
        $salesCommissionQuery = $model3->where('status', 'Vendido')
            ->where('YEAR(updated_at)', $year)
            ->where('MONTH(updated_at)', $month);
            
        if ($userId) {
            $salesCommissionQuery->where('created_by_user_id', $userId);
        }
        
        $soldProperties = $salesCommissionQuery->findAll();
        
        foreach ($soldProperties as $property) {
            if ($property['sales_commission_type'] == 'percentage' && $property['sales_commission_percentage']) {
                $stats['comisiones_venta'] += $property['price'] * ($property['sales_commission_percentage'] / 100);
            } elseif ($property['sales_commission_type'] == 'fixed_amount' && $property['sales_commission_fixed_amount']) {
                $stats['comisiones_venta'] += $property['sales_commission_fixed_amount'];
            }
            
            // Comisiones de captación (solo si el usuario trajo la casa)
            if (($userId && $property['brought_by_user_id'] == $userId) || !$userId) {
                if ($property['brought_commission_type'] == 'percentage' && $property['brought_commission_percentage']) {
                    $stats['comisiones_captacion'] += $property['price'] * ($property['brought_commission_percentage'] / 100);
                } elseif ($property['brought_commission_type'] == 'fixed_amount' && $property['brought_commission_fixed_amount']) {
                    $stats['comisiones_captacion'] += $property['brought_commission_fixed_amount'];
                }
            }
        }
        
        return $stats;
    }

    /**
     * Obtiene el ranking de asesores por casas captadas
     */
    public function getTopAgentsThisMonth()
    {
        return $this->select('users.full_name, COUNT(*) as casas_traidas')
                    ->join('users', 'real_estates.brought_by_user_id = users.id')
                    ->where('YEAR(real_estates.created_at)', date('Y'))
                    ->where('MONTH(real_estates.created_at)', date('m'))
                    ->groupBy('real_estates.brought_by_user_id')
                    ->orderBy('casas_traidas', 'DESC')
                    ->limit(5)
                    ->findAll();
    }

    /**
     * Obtiene estadísticas de ventas por mes
     */
    public function getSalesStatsByMonth($year = null)
    {
        if (!$year) $year = date('Y');
        
        return $this->select('
            MONTH(updated_at) as mes,
            MONTHNAME(updated_at) as nombre_mes,
            COUNT(*) as ventas,
            SUM(price) as ingresos_totales
        ')
        ->where('status', 'Vendido')
        ->where('YEAR(updated_at)', $year)
        ->groupBy('MONTH(updated_at), MONTHNAME(updated_at)')
        ->orderBy('mes', 'ASC')
        ->findAll();
    }

}