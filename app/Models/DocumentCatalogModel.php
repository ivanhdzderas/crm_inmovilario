<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentCatalogModel extends Model
{
    protected $table            = 'document_catalog';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'description',
        'category', 
        'default_cost',
        'is_required',
        'is_active',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene documentos activos por categoría
     */
    public function getActiveByCategory(string $category = null)
    {
        $builder = $this->where('is_active', 1);
        
        if ($category) {
            $builder->where('category', $category);
        }
        
        return $builder->orderBy('category', 'ASC')
                      ->orderBy('name', 'ASC')
                      ->findAll();
    }

    /**
     * Obtiene documentos requeridos
     */
    public function getRequired()
    {
        return $this->where(['is_active' => 1, 'is_required' => 1])
                   ->orderBy('category', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Obtiene el costo total de trámites requeridos
     */
    public function getTotalRequiredCost(): float
    {
        $result = $this->selectSum('default_cost')
                      ->where(['is_active' => 1, 'is_required' => 1])
                      ->first();
        
        return (float) ($result['default_cost'] ?? 0);
    }

    /**
     * Obtiene categorías disponibles
     */
    public function getCategories(): array
    {
        return $this->select('category')
                   ->where('is_active', 1)
                   ->distinct()
                   ->orderBy('category', 'ASC')
                   ->findColumn('category');
    }
}