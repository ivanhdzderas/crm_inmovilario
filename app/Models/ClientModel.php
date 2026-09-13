<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    // --- Configuración Básica ---
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // CAMPOS PERMITIDOS (¡CRÍTICO!)
    protected $allowedFields = [
        'user_id',
        'full_name', // Campo original de tu tabla clients
        'email',     // Campo original de tu tabla clients
        'phone',     // Campo original de tu tabla clients
        // Campos nuevos de la migración de prospectos:
        'credit_amount',
        'client_type', // Nuevo campo para distinguir comprador/vendedor
        'is_funeral_prospect',
        'zone_id',
        'payment_method', // Actualizado de payment_method_id a payment_method
        'payment_method_id', // Compatibilidad con esquema anterior
        'status',
        'deactivation_reason_id',
        'deactivation_notes',
        'deactivated_at',
        'acquisition_source_id',
        'acquisition_detail',
    ];

    // --- Configuración de Tiempos ---
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    /**
     * Obtiene la lista de prospectos con los nombres de la Zona y el Asesor
     * (Necesario para la vista tipo DataTables)
     */
    public function getProspectsList($type = null, ?string $interest = null)
    {
        $builder = $this->db->table('clients c');

        $builder->select('
            c.id, 
            c.full_name, 
            c.phone, 
            c.client_type,
            c.credit_amount,
            c.status, 
            c.deactivation_reason_id,
            c.deactivation_notes,
            c.deactivated_at,
            cr.name as deactivation_reason_name,
            acs.name as acquisition_source_name,
            acs.icon_class as acquisition_source_icon,
            acs.channel_type as acquisition_channel_type,
            c.acquisition_detail,
            z.name as zone_name,
            u.full_name as user_name,
            c.email
        ', false);

        if ($this->db->fieldExists('is_funeral_prospect', 'clients')) {
            $builder->select('c.is_funeral_prospect', false);
        }
        
        // UNIÓN DIRECTA: clients (c) -> users (u)
        $builder->join('users u', 'u.id = c.user_id', 'left'); 
        
        // UNIÓN 2: Zona de Interés
        $builder->join('zones z', 'z.id = c.zone_id', 'left');

        if ($this->db->tableExists('cancellation_reasons')) {
            $builder->join('cancellation_reasons cr', 'cr.id = c.deactivation_reason_id', 'left');
        }

        if ($this->db->tableExists('acquisition_sources')) {
            $builder->join('acquisition_sources acs', 'acs.id = c.acquisition_source_id', 'left');
        }
        
        // Filtrar por tipo si se especifica
        if ($type) {
            $builder->where('c.client_type', $type);
        }

        if ($interest === 'funerario') {
            $conditions = [];

            if ($this->db->fieldExists('is_funeral_prospect', 'clients')) {
                $conditions[] = 'c.is_funeral_prospect = 1';
            }

            if ($this->db->tableExists('client_funeral_interests')) {
                $conditions[] = 'EXISTS (SELECT 1 FROM client_funeral_interests cfi WHERE cfi.client_id = c.id)';
            }
            if ($this->db->tableExists('client_accounts') && $this->db->fieldExists('account_type', 'client_accounts')) {
                $conditions[] = "EXISTS (SELECT 1 FROM client_accounts ca WHERE ca.client_id = c.id AND ca.account_type = 'funerario' AND ca.status = 'Activo')";
            }
            if ($conditions !== []) {
                $builder->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }

            if ($this->db->tableExists('client_funeral_interests') && $this->db->tableExists('funeral_products')) {
                $builder->select('(
                    SELECT GROUP_CONCAT(DISTINCT fp.name ORDER BY fp.name SEPARATOR \', \')
                    FROM client_funeral_interests cfi
                    INNER JOIN funeral_products fp ON fp.id = cfi.funeral_product_id
                    WHERE cfi.client_id = c.id
                ) AS funeral_packages', false);
            }
        }

        // **SOLUCIÓN:** Usar get() y getResultArray() para ejecutar la consulta personalizada.
        return $builder->orderBy('c.created_at', 'DESC')->get()->getResultArray();
    }
}