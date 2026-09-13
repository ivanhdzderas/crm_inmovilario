<?php

namespace App\Models;

use App\Helpers\CatalogoHelper;
use CodeIgniter\Model;

class ProyectosInmobiliariosModel extends Model
{
    protected $table      = 'real_estate_projects'; // <--- CAMBIO AQUÍ
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // Aquí definimos los campos que se pueden manipular
    protected $allowedFields = ['name', 'description', 'location'];

    // Timestamps
    protected $useTimestamps = false; 

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getPublicos(): array
    {
        return $this->orderBy('name', 'ASC')->findAll() ?: [];
    }

    public function getBySlug(string $slug): ?array
    {
        $slug = CatalogoHelper::normalizeSegment($slug);

        foreach ($this->getPublicos() as $proyecto) {
            if (CatalogoHelper::slug($proyecto['name']) === $slug) {
                return $proyecto;
            }
        }

        if (str_starts_with($slug, 'desarrollo-')) {
            $alterno = substr($slug, strlen('desarrollo-'));
            foreach ($this->getPublicos() as $proyecto) {
                if (CatalogoHelper::slug($proyecto['name']) === $alterno) {
                    return $proyecto;
                }
            }
        }

        foreach ($this->getPublicos() as $proyecto) {
            if ('desarrollo-' . CatalogoHelper::slug($proyecto['name']) === $slug) {
                return $proyecto;
            }
        }

        return null;
    }
}