<?php

namespace App\Models;

use App\Helpers\CatalogoFunerarioHelper;
use CodeIgniter\Model;

class FuneralLocationModel extends Model
{
    protected $table            = 'funeral_locations';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'slug', 'address', 'zone_id', 'description', 'is_active'];
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

    public function getPublicos(): array
    {
        return $this->where('is_active', 1)->orderBy('name', 'ASC')->findAll() ?: [];
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', CatalogoFunerarioHelper::slug($slug))->where('is_active', 1)->first();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getHomesForLocation(int $locationId): array
    {
        return $this->db->table('funeral_location_homes flh')
            ->select('funeral_homes.*')
            ->join('funeral_homes', 'funeral_homes.id = flh.funeral_home_id')
            ->where('flh.funeral_location_id', $locationId)
            ->where('funeral_homes.is_active', 1)
            ->orderBy('funeral_homes.name', 'ASC')
            ->get()->getResultArray();
    }

    public function syncHomes(int $locationId, array $homeIds): void
    {
        $this->db->table('funeral_location_homes')->where('funeral_location_id', $locationId)->delete();
        $now = date('Y-m-d H:i:s');
        foreach (array_unique(array_map('intval', $homeIds)) as $homeId) {
            if ($homeId > 0) {
                $this->db->table('funeral_location_homes')->insert([
                    'funeral_location_id' => $locationId,
                    'funeral_home_id'     => $homeId,
                    'created_at'          => $now,
                ]);
            }
        }
    }
}
