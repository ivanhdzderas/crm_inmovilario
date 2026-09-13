<?php

namespace App\Models;

use App\Helpers\CatalogoFunerarioHelper;
use CodeIgniter\Model;

class FuneralHomeModel extends Model
{
    protected $table            = 'funeral_homes';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'slug', 'description', 'is_active'];
    protected $useTimestamps    = true;

    protected function setSlug(array $data): array
    {
        if (isset($data['data']['name']) && empty($data['data']['slug'])) {
            $data['data']['slug'] = CatalogoFunerarioHelper::slug($data['data']['name']);
        }

        return $data;
    }

    protected $beforeInsert = ['setSlug'];
    protected $beforeUpdate = ['setSlug'];

    public function getActivas(): array
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll() ?: [];
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', CatalogoFunerarioHelper::slug($slug))->where('is_active', 1)->first();
    }

    /**
     * Funerarias vinculadas a un lugar de descanso.
     *
     * @return list<array<string, mixed>>
     */
    public function getByLocation(int $locationId): array
    {
        return $this->db->table('funeral_homes fh')
            ->select('fh.*')
            ->join('funeral_location_homes flh', 'flh.funeral_home_id = fh.id')
            ->where('flh.funeral_location_id', $locationId)
            ->where('fh.is_active', 1)
            ->orderBy('fh.name', 'ASC')
            ->get()->getResultArray();
    }
}
