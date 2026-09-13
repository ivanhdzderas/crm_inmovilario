<?php

namespace App\Models;

use CodeIgniter\Model;

class FuneralProductTypeModel extends Model
{
    protected $table         = 'funeral_product_types';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'sort_order'];
    protected $useTimestamps = true;

    public function getAllOrdered(): array
    {
        return $this->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll() ?: [];
    }
}
