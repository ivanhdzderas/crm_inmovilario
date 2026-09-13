<?php

namespace App\Models;

use CodeIgniter\Model;

class CancellationReasonModel extends Model
{
    protected $table            = 'cancellation_reasons';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * @return list<array<string, mixed>>
     */
    public function getActiveByCategory(?string $category = null): array
    {
        $builder = $this->where('is_active', 1)->orderBy('sort_order', 'ASC');

        if ($category === 'prospect') {
            $builder->whereIn('category', ['prospect', 'both']);
        } elseif ($category === 'apartado') {
            $builder->whereIn('category', ['apartado', 'both']);
        }

        return $builder->findAll() ?: [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAllGrouped(): array
    {
        return $this->orderBy('category', 'ASC')->orderBy('sort_order', 'ASC')->findAll() ?: [];
    }
}
