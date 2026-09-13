<?php

namespace App\Models;

use CodeIgniter\Model;

class RealEstateAmenitiesModel extends Model
{
    // Nombre de la tabla en la base de datos
    protected $table = 'real_estate_amenities';
    // La clave primaria de la tabla
    protected $primaryKey = 'id';
    
    // Configuración por defecto
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    // Campos que se pueden insertar y actualizar
    protected $allowedFields = ['real_estate_id', 'amenity_type', 'value', 'created_at', 'updated_at'];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = '';
}