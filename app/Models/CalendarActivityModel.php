<?php

namespace App\Models;

use CodeIgniter\Model;

class CalendarActivityModel extends Model
{
    protected $table            = 'calendar_activities';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'user_id',      // Asesor responsable
        'client_id',    // Prospecto asociado
        'title',
        'description',
        'start_time',
        'end_time',
        'type',         // Reunión, Llamada, Cita Infonavit, etc.
        'status',       // Pendiente, Completada, Cancelada
        'google_event_id'  // ID del evento en Google Calendar
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene las actividades agendadas para un usuario en un rango de fechas (útil para el calendario).
     */
    public function getActivitiesByUserAndRange(int $userId, string $startDate, string $endDate)
    {
        return $this->where('user_id', $userId)
                    ->where('start_time >=', $startDate)
                    ->where('start_time <=', $endDate)
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene actividades con información del cliente
     */
    public function getActivitiesWithClient(int $userId, string $startDate, string $endDate)
    {
        return $this->select('
                calendar_activities.*,
                clients.full_name as client_name,
                clients.phone as client_phone,
                clients.email as client_email
            ')
            ->join('clients', 'clients.id = calendar_activities.client_id', 'left')
            ->where('calendar_activities.user_id', $userId)
            ->where('calendar_activities.start_time >=', $startDate)
            ->where('calendar_activities.start_time <=', $endDate)
            ->orderBy('calendar_activities.start_time', 'ASC')
            ->findAll();
    }

    /**
     * Obtiene estadísticas de actividades para un usuario en un mes
     */
    public function getMonthlyStats(int $userId, int $year, int $month)
    {
        $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

        return $this->select('status, COUNT(*) as count')
                    ->where('user_id', $userId)
                    ->where('start_time >=', $startDate . ' 00:00:00')
                    ->where('start_time <=', $endDate . ' 23:59:59')
                    ->groupBy('status')
                    ->findAll();
    }

    /**
     * Obtiene actividades próximas (siguientes 7 días)
     */
    public function getUpcomingActivities(int $userId, int $limit = 10)
    {
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime('+7 days'));

        return $this->select('
                calendar_activities.*,
                clients.full_name as client_name
            ')
            ->join('clients', 'clients.id = calendar_activities.client_id', 'left')
            ->where('calendar_activities.user_id', $userId)
            ->where('calendar_activities.start_time >=', $startDate)
            ->where('calendar_activities.start_time <=', $endDate)
            ->where('calendar_activities.status', 'Pendiente')
            ->orderBy('calendar_activities.start_time', 'ASC')
            ->limit($limit)
            ->findAll();
    }
}