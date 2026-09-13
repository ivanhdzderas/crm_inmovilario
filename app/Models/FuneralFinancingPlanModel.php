<?php

namespace App\Models;

use App\Helpers\CatalogoFunerarioHelper;
use CodeIgniter\Model;

class FuneralFinancingPlanModel extends Model
{
    protected $table         = 'funeral_financing_plans';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['funeral_product_id', 'name', 'enganche', 'mensualidad', 'months', 'sort_order', 'is_active'];
    protected $useTimestamps = true;

    public function getByProduct(int $productId, bool $onlyActive = false): array
    {
        $builder = $this->where('funeral_product_id', $productId)->orderBy('sort_order', 'ASC')->orderBy('months', 'ASC');
        if ($onlyActive) {
            $builder->where('is_active', 1);
        }

        return $builder->findAll() ?: [];
    }

    public function replaceForProduct(int $productId, array $plans): void
    {
        $this->where('funeral_product_id', $productId)->delete();

        foreach ($plans as $i => $plan) {
            if (empty($plan['name'])) {
                continue;
            }
            $this->insert([
                'funeral_product_id' => $productId,
                'name'               => $plan['name'],
                'enganche'           => (float) ($plan['enganche'] ?? 0),
                'mensualidad'        => (float) ($plan['mensualidad'] ?? 0),
                'months'             => max(1, (int) ($plan['months'] ?? 12)),
                'sort_order'         => (int) ($plan['sort_order'] ?? ($i + 1) * 10),
                'is_active'          => isset($plan['is_active']) ? (int) $plan['is_active'] : 1,
            ]);
        }
    }
}
