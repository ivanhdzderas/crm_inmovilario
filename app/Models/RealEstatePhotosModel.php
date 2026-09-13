<?php

namespace App\Models;

use CodeIgniter\Model;

class RealEstatePhotosModel extends Model
{
    // Nombre de la tabla de la base de datos
    protected $table = 'real_estate_photos'; 
    // La clave primaria de la tabla
    protected $primaryKey = 'id';
    
    // El tipo de retorno de los resultados del modelo
    protected $returnType = 'array';
    
    // Campos que se pueden llenar en la base de datos
    protected $allowedFields = ['real_estate_id', 'file_path'];
    
    // Activar el uso de timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = ''; // No se usa borrado suave, por lo que se deja vacío
    protected $useSoftDeletes = false; // Desactivar borrado suave
}