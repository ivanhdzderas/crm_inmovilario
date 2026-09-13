<?php

namespace App\Models;

use App\Helpers\CatalogoFunerarioHelper;
use CodeIgniter\Model;

class FuneralProductModel extends Model
{
    protected $table         = 'funeral_products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'clave', 'name', 'slug', 'funeral_location_id', 'funeral_home_id',
        'product_type_id', 'base_price', 'description', 'status',
    ];
    protected $useTimestamps = true;

    protected function setSlug(array $data): array
    {
        if (isset($data['data']['name']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = CatalogoFunerarioHelper::slug($data['data']['name']);
        }

        return $data;
    }

    protected $beforeInsert = ['setSlug'];
    protected $beforeUpdate = ['setSlug'];

    public function getAdminList(): array
    {
        return $this->select('
                funeral_products.*,
                funeral_locations.name as location_name,
                funeral_homes.name as home_name,
                funeral_product_types.name as type_name
            ')
            ->join('funeral_locations', 'funeral_locations.id = funeral_products.funeral_location_id')
            ->join('funeral_homes', 'funeral_homes.id = funeral_products.funeral_home_id')
            ->join('funeral_product_types', 'funeral_product_types.id = funeral_products.product_type_id', 'left')
            ->orderBy('funeral_products.name', 'ASC')
            ->findAll() ?: [];
    }

    public function getPublicCatalog(array $filters = []): array
    {
        $builder = $this->select('
                funeral_products.*,
                funeral_locations.name as location_name,
                funeral_locations.slug as location_slug,
                funeral_locations.address as location_address,
                funeral_homes.name as home_name,
                funeral_homes.slug as home_slug,
                funeral_product_types.name as type_name,
                zones.name as zone_name
            ')
            ->join('funeral_locations', 'funeral_locations.id = funeral_products.funeral_location_id')
            ->join('funeral_homes', 'funeral_homes.id = funeral_products.funeral_home_id')
            ->join('funeral_product_types', 'funeral_product_types.id = funeral_products.product_type_id', 'left')
            ->join('zones', 'zones.id = funeral_locations.zone_id', 'left')
            ->where('funeral_products.status', 'Disponible')
            ->where('funeral_locations.is_active', 1)
            ->where('funeral_homes.is_active', 1);

        if (! empty($filters['location_id'])) {
            $builder->where('funeral_products.funeral_location_id', $filters['location_id']);
        }
        if (! empty($filters['home_id'])) {
            $builder->where('funeral_products.funeral_home_id', $filters['home_id']);
        }
        if (! empty($filters['product_type_id'])) {
            $builder->where('funeral_products.product_type_id', $filters['product_type_id']);
        }

        return $builder->orderBy('funeral_locations.name', 'ASC')
            ->orderBy('funeral_homes.name', 'ASC')
            ->orderBy('funeral_products.name', 'ASC')
            ->findAll() ?: [];
    }

    public function findPublicBySlugs(string $locationSlug, string $homeSlug, string $productSlug): ?array
    {
        $locationSlug = CatalogoFunerarioHelper::slug($locationSlug);
        $homeSlug     = CatalogoFunerarioHelper::slug($homeSlug);
        $productSlug  = CatalogoFunerarioHelper::slug($productSlug);

        return $this->select('
                funeral_products.*,
                funeral_locations.name as location_name,
                funeral_locations.slug as location_slug,
                funeral_locations.address as location_address,
                funeral_locations.description as location_description,
                funeral_homes.name as home_name,
                funeral_homes.slug as home_slug,
                funeral_homes.description as home_description,
                funeral_product_types.name as type_name,
                zones.name as zone_name
            ')
            ->join('funeral_locations', 'funeral_locations.id = funeral_products.funeral_location_id')
            ->join('funeral_homes', 'funeral_homes.id = funeral_products.funeral_home_id')
            ->join('funeral_product_types', 'funeral_product_types.id = funeral_products.product_type_id', 'left')
            ->join('zones', 'zones.id = funeral_locations.zone_id', 'left')
            ->where('funeral_locations.slug', $locationSlug)
            ->where('funeral_homes.slug', $homeSlug)
            ->where('funeral_products.slug', $productSlug)
            ->where('funeral_products.status', 'Disponible')
            ->first();
    }
}
