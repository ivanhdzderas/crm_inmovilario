<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientFollowUpModel extends Model
{
    protected $table            = 'client_follow_ups';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'client_id',
        'user_id',
        'advisor_id',
        'contact_method',
        'discussion_detail',
        'contact_datetime',
        'personal_notes',
        'activity_id' // Clave para la integración con la agenda
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // No aplica soft deletes aquí

    /**
     * Obtiene el historial de seguimiento de un prospecto con información del asesor.
     */
    public function getFollowUpsByClient(int $clientId)
    {
        $followUps = $this->where('client_id', $clientId)
                         ->orderBy('created_at', 'DESC')
                         ->findAll();
        
        // Agregar nombre del asesor y datos de actividad vinculada
        $userModel = model('UserModel');
        $db = \Config\Database::connect();
        $hasActivities = $db->tableExists('calendar_activities');

        foreach ($followUps as &$followUp) {
            $user = $userModel->find($followUp['user_id']);
            $followUp['advisor_name'] = $user['full_name'] ?? $user['username'] ?? $user['email'] ?? 'Asesor Desconocido';

            if ($hasActivities && ! empty($followUp['activity_id'])) {
                $activity = $db->table('calendar_activities')->where('id', $followUp['activity_id'])->get()->getRowArray();
                $followUp['activity_title'] = $activity['title'] ?? null;
                $followUp['activity_start_time'] = $activity['start_time'] ?? null;
                $followUp['activity_type'] = $activity['type'] ?? null;
            }
        }
        
        return $followUps;
    }
}