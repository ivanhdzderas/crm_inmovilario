<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CalendarActivityModel;
use App\Models\ClientModel;
use App\Models\ClientFollowUpModel;
use CodeIgniter\I18n\Time;

class Agenda extends BaseController
{
    protected $activityModel;
    protected $clientModel;
    protected $followUpModel;

    public function __construct()
    {
        $this->activityModel = new CalendarActivityModel();
        $this->clientModel = new ClientModel();
        $this->followUpModel = new ClientFollowUpModel();
    }

    /**
     * Pantalla principal de la agenda
     */
    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        // Obtener actividades del usuario para el mes actual
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-t 23:59:59');
        
        $activities = $this->getActivitiesWithClientInfo($userId, $startDate, $endDate, false);
        
        // Obtener minutas pendientes (sin fecha límite específica o próximas a vencer)
        $pendingMinutes = $this->getPendingMinutes($userId);
        
        // Obtener estadísticas de actividades
        $stats = $this->getActivityStats($userId);

        $data = [
            'title' => 'Mi Agenda',
            'activities' => $activities,
            'pendingMinutes' => $pendingMinutes,
            'stats' => $stats,
            'currentDate' => date('Y-m-d'),
            'currentMonth' => date('F Y'),
            'userId' => $userId
        ];

        return view('agenda/index', $data);
    }

    /**
     * API para obtener actividades (para FullCalendar)
     */
    public function getActivities()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userId = session()->get('user_id'); // 1. Get User ID

        // FullCalendar envía automáticamente los parámetros 'start' y 'end'
        // que corresponden al rango de fechas visible en el calendario.
        $start = $this->request->getGet('start'); // 2. Get 'start' from URL
        $end = $this->request->getGet('end');     // 3. Get 'end' from URL

        // --- LOGGING PARA DEPURACIÓN ---
        // Este log nos ayudará a ver si el calendario está llamando a esta función cuando cambias de mes.
        log_message('debug', '[getActivities] Request received. User: ' . ($userId ?? 'NULL') . ', Start: ' . ($start ?? 'NULL') . ', End: ' . ($end ?? 'NULL'));

        if (!$userId) {
            log_message('error', '[getActivities] No user ID in session for AJAX request.');
            return $this->response->setJSON([]); // Devuelve un array vacío si no hay sesión
        }

        // Es crucial usar estas fechas para filtrar en el servidor.
        // De lo contrario, el calendario podría no mostrar eventos fuera del rango inicial.
        // El 'true' final es para que el rango de fecha final sea exclusivo (start_time < end_date),
        // que es como FullCalendar espera los eventos.
        $activities = $this->getActivitiesWithClientInfo($userId, $start, $end, true);
        
        // Log del resultado de la consulta
        log_message('debug', '[getActivities] Found ' . count($activities) . ' activities for user ' . $userId . ' in the given range.');

        // Formatear para el estándar de FullCalendar
        $events = [];
        foreach ($activities as $activity) {
            $color = $this->getActivityColor($activity['status'], $activity['type']);
            
            // Prepara el título para evitar duplicar el nombre del cliente
            $title = $activity['title'];
            if (!empty($activity['client_name']) && stripos($title, $activity['client_name']) === false) {
                $title .= ' - ' . $activity['client_name'];
            }

            $events[] = [
                'id' => $activity['id'],
                'title' => $title,
                'start' => $activity['start_time'],
                'end' => $activity['end_time'],
                'backgroundColor' => $color['bg'],
                'borderColor' => $color['border'],
                'textColor' => $color['text'],
                'extendedProps' => [
                    'client_id' => $activity['client_id'],
                    'client_name' => $activity['client_name'],
                    'client_phone' => $activity['client_phone'] ?? null,
                    'client_email' => $activity['client_email'] ?? null,
                    'is_funeral_prospect' => ! empty($activity['client_is_funeral_prospect']),
                    'description' => $activity['description'],
                    'type' => $activity['type'],
                    'status' => $activity['status'],
                    'google_synced' => !empty($activity['google_event_id'])
                ]
            ];
        }

        return $this->response->setJSON($events);
    }

    /**
     * Conectar con Google Calendar
     */
    public function connectGoogle()
    {
        $googleCalendar = new \App\Libraries\GoogleCalendar(session()->get('user_id'));
        $authUrl = $googleCalendar->getAuthUrl();
        
        return redirect()->to($authUrl);
    }

    /**
     * Callback de Google Calendar OAuth
     */
    public function googleCallback()
    {
        $code = $this->request->getGet('code');
        
        if (!$code) {
            return redirect()->to('agenda')->with('error', 'Error en la autorización de Google Calendar');
        }

        $userId = session()->get('user_id');
        log_message('info', 'Google Calendar callback for user: ' . $userId);
        
        if (!$userId) {
            return redirect()->to('agenda')->with('error', 'Usuario no autenticado');
        }

        $googleCalendar = new \App\Libraries\GoogleCalendar($userId);
        
        if ($googleCalendar->handleCallback($code)) {
            return redirect()->to('agenda')->with('success', '¡Google Calendar conectado exitosamente!');
        } else {
            return redirect()->to('agenda')->with('error', 'Error al conectar con Google Calendar');
        }
    }

    /**
     * Desconectar Google Calendar
     */
    public function disconnectGoogle()
    {
        $googleCalendar = new \App\Libraries\GoogleCalendar(session()->get('user_id'));
        $googleCalendar->disconnect();
        
        return redirect()->to('agenda')->with('success', 'Google Calendar desconectado');
    }

    /**
     * Sincronizar eventos con Google Calendar
     */
    public function syncWithGoogle()
    {
        $userId = session()->get('user_id');
        $googleCalendar = new \App\Libraries\GoogleCalendar($userId);
        
        if (!$googleCalendar->isConnected()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Google Calendar no está conectado'
            ]);
        }

        // Importar eventos desde Google
        $importedEvents = $googleCalendar->importGoogleEvents();
        
        // Sincronizar eventos locales a Google
        $activities = $this->activityModel
            ->where('user_id', $userId)
            ->where('google_event_id IS NULL')
            ->where('created_at >=', date('Y-m-d', strtotime('-1 week')))
            ->findAll();

        $syncedCount = 0;
        foreach ($activities as $activity) {
            if ($googleCalendar->syncEventToGoogle($activity)) {
                $syncedCount++;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Sincronización completada. {$syncedCount} eventos enviados a Google, " . count($importedEvents) . " eventos importados.",
            'synced_to_google' => $syncedCount,
            'imported_from_google' => count($importedEvents)
        ]);
    }

    /**
     * Mover una actividad (cambiar fecha/hora)
     */
    public function moveActivity($activityId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $activity = $this->activityModel->find($activityId);

        if (!$activity || $activity['user_id'] != $userId) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['error' => 'Actividad no encontrada.']
            ]);
        }

        $rules = [
            'new_start' => 'required|valid_date[Y-m-d H:i:s]',
            'new_end' => 'permit_empty|valid_date[Y-m-d H:i:s]',
            'reason' => 'required|min_length[5]|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $oldStartTime = $activity['start_time'];
        $newStartTime = $this->request->getPost('new_start');
        $newEndTime = $this->request->getPost('new_end') ?: null;
        $reason = $this->request->getPost('reason');

        // Iniciar transacción
        $this->activityModel->db->transBegin();

        try {
            // Actualizar la actividad
            $updateData = [
                'start_time' => $newStartTime,
                'end_time' => $newEndTime
            ];

            if (!$this->activityModel->update($activityId, $updateData)) {
                throw new \Exception('Error al actualizar la actividad');
            }

            // Si hay cliente asociado, agregar minuta de reprogramación
            if (!empty($activity['client_id'])) {
                // Usar zona horaria de la aplicación para fechas
                date_default_timezone_set(config('App')->appTimezone);
                $currentDateTime = date('Y-m-d H:i:s');
                
                $followUpData = [
                    'client_id' => $activity['client_id'],
                    'user_id' => $userId,
                    'advisor_id' => $userId,
                    'contact_datetime' => $currentDateTime,
                    'contact_method' => 'Reprogramación',
                    'discussion_detail' => "📅 CITA REPROGRAMADA\n\n" .
                                         "Actividad: {$activity['title']}\n" .
                                         "Fecha anterior: " . date('d/m/Y H:i', strtotime($oldStartTime)) . "\n" .
                                         "Nueva fecha: " . date('d/m/Y H:i', strtotime($newStartTime)) . "\n\n" .
                                         "Motivo de reprogramación:\n{$reason}",
                    'activity_id' => $activityId
                ];

                if (!$this->followUpModel->insert($followUpData)) {
                    throw new \Exception('Error al registrar la minuta de reprogramación');
                }
            }

            // Sincronizar con Google Calendar si está conectado
            try {
                $googleCalendar = new \App\Libraries\GoogleCalendar($userId);
                if ($googleCalendar->isConnected()) {
                    $activityData = $this->activityModel->find($activityId);
                    
                    // Enriquecer datos para Google Calendar
                    $googleActivityData = $activityData;
                    if (!empty($googleActivityData['client_id'])) {
                        $client = $this->clientModel->find($googleActivityData['client_id']);
                        if ($client) {
                            // Enriquecer título si es necesario
                            if (stripos($googleActivityData['title'], $client['full_name']) === false) {
                                $googleActivityData['title'] = $googleActivityData['title'] . ' - ' . $client['full_name'];
                            }
                            
                            // Enriquecer descripción
                            $clientInfo = "📋 CLIENTE: " . $client['full_name'] . "\n";
                            $clientInfo .= "📞 Teléfono: " . $client['phone'] . "\n";
                            $clientInfo .= "📧 Email: " . $client['email'] . "\n";
                            $googleActivityData['description'] = $clientInfo . "\n📝 NOTAS:\n" . ($googleActivityData['description'] ?: 'Sin notas adicionales.');
                        }
                    }
                    
                    // Debug: Log para verificar google_event_id
                    log_message('debug', "Sincronizando actividad {$activityId} - google_event_id: " . ($activityData['google_event_id'] ?? 'NULL'));
                    
                    // Si ya tiene google_event_id, usar updateGoogleEvent directamente
                    if (!empty($activityData['google_event_id'])) {
                        $result = $googleCalendar->updateGoogleEvent($googleActivityData);
                        log_message('info', "Actividad {$activityId} actualizada en Google Calendar: " . ($result ? 'SUCCESS' : 'FAILED'));
                    } else {
                        // Si no tiene google_event_id, crear nuevo evento
                        $googleEventId = $googleCalendar->createGoogleEvent($googleActivityData);
                        if ($googleEventId) {
                            // Guardar el ID del evento de Google
                            $this->activityModel->update($activityId, ['google_event_id' => $googleEventId]);
                            log_message('info', "Nuevo evento creado en Google Calendar: {$googleEventId}");
                        }
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error sincronizando reprogramación con Google Calendar: ' . $e->getMessage());
            }

            $this->activityModel->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Actividad reprogramada correctamente.',
                'new_start' => $newStartTime,
                'new_end' => $newEndTime
            ]);

        } catch (\Exception $e) {
            $this->activityModel->db->transRollback();
            log_message('error', '[moveActivity] Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['error' => 'Error al reprogramar: ' . $e->getMessage()]
            ]);
        }
    }

    /**
     * Cancelar una actividad
     */
    public function cancelActivity($activityId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $activity = $this->activityModel->find($activityId);

        if (!$activity || $activity['user_id'] != $userId) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['error' => 'Actividad no encontrada.']
            ]);
        }

        $rules = [
            'reason' => 'required|min_length[5]|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $reason = $this->request->getPost('reason');

        // Iniciar transacción
        $this->activityModel->db->transBegin();

        try {
            // Cambiar estado a cancelada
            if (!$this->activityModel->update($activityId, ['status' => 'Cancelada'])) {
                throw new \Exception('Error al cancelar la actividad');
            }

            // Si hay cliente asociado, agregar minuta de cancelación
            if (!empty($activity['client_id'])) {
                // Usar zona horaria de la aplicación para fechas
                date_default_timezone_set(config('App')->appTimezone);
                $currentDateTime = date('Y-m-d H:i:s');
                
                $followUpData = [
                    'client_id' => $activity['client_id'],
                    'user_id' => $userId,
                    'advisor_id' => $userId,
                    'contact_datetime' => $currentDateTime,
                    'contact_method' => 'Cancelación',
                    'discussion_detail' => "❌ CITA CANCELADA\n\n" .
                                         "Actividad: {$activity['title']}\n" .
                                         "Fecha programada: " . date('d/m/Y H:i', strtotime($activity['start_time'])) . "\n\n" .
                                         "Motivo de cancelación:\n{$reason}",
                    'activity_id' => $activityId
                ];

                if (!$this->followUpModel->insert($followUpData)) {
                    throw new \Exception('Error al registrar la minuta de cancelación');
                }
            }

            $this->activityModel->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Actividad cancelada correctamente.'
            ]);

        } catch (\Exception $e) {
            $this->activityModel->db->transRollback();
            log_message('error', '[cancelActivity] Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['error' => 'Error al cancelar: ' . $e->getMessage()]
            ]);
        }
    }

    /**
     * Completar una actividad
     */
    public function completeActivity($activityId)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $activity = $this->activityModel->find($activityId);

        if (!$activity || $activity['user_id'] != $userId) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['error' => 'Actividad no encontrada.']
            ]);
        }

        if ($this->activityModel->update($activityId, ['status' => 'Completada'])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Actividad marcada como completada.'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'errors' => ['error' => 'Error al actualizar el estado.']
        ]);
    }

    /**
     * Crear nueva actividad
     */
    public function createActivity()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        
        $rules = [
            'title' => 'required|max_length[255]',
            'start_time' => 'required|valid_date[Y-m-d H:i]',
            'end_time' => 'permit_empty|valid_date[Y-m-d H:i]',
            'type' => 'required|in_list[Llamada,Reunión,Cita Infonavit,Envío Documentos,Otro]',
            'client_id' => 'permit_empty|integer',
            'description' => 'permit_empty|max_length[500]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $clientId = $this->request->getPost('client_id') ?: null;
            $originalTitle = $this->request->getPost('title');
            $originalDescription = $this->request->getPost('description');
            
            // Enriquecer título y descripción con información del cliente
            $enrichedTitle = $originalTitle;
            $enrichedDescription = $originalDescription;
            
            if ($clientId) {
                $client = $this->clientModel->find($clientId);
                if ($client) {
                    // Si el título no incluye el nombre del cliente, agregarlo
                    if (stripos($originalTitle, $client['full_name']) === false) {
                        $enrichedTitle = $originalTitle . ' - ' . $client['full_name'];
                    }
                    
                    // Enriquecer descripción con datos del cliente
                    $clientInfo = "📋 CLIENTE: " . $client['full_name'] . "\n";
                    $clientInfo .= "📞 Teléfono: " . $client['phone'] . "\n";
                    $clientInfo .= "📧 Email: " . $client['email'] . "\n";
                    
                    if (!empty($client['credit_amount'])) {
                        $clientInfo .= "💰 Crédito: $" . number_format($client['credit_amount'], 2) . "\n";
                    }
                    
                    // Obtener zona del cliente
                    $zoneModel = new \App\Models\ZoneModel();
                    $zone = $zoneModel->find($client['zone_id']);
                    if ($zone) {
                        $clientInfo .= "🗺️ Zona: " . $zone['name'] . "\n";
                    }
                    
                    $clientInfo .= "\n📝 NOTAS:\n" . ($originalDescription ?: 'Sin notas adicionales.');
                    $enrichedDescription = $clientInfo;
                }
            } else if (empty($enrichedDescription)) {
                $enrichedDescription = "Reunión sin cliente específico asignado.";
            }

            $activityData = [
                'user_id' => $userId,
                'client_id' => $clientId,
                'title' => $originalTitle, // Guardamos el título original en la BD
                'description' => $originalDescription, // Guardamos la descripción original en la BD
                'start_time' => $this->request->getPost('start_time') . ':00',
                'end_time' => $this->request->getPost('end_time') ? $this->request->getPost('end_time') . ':00' : null,
                'type' => $this->request->getPost('type'),
                'status' => 'Pendiente'
            ];

            $activityId = $this->activityModel->insert($activityData);
            if ($activityId) {
                // Intentar sincronizar con Google Calendar si está conectado
                try {
                    $googleCalendar = new \App\Libraries\GoogleCalendar($userId);
                    if ($googleCalendar->isConnected()) {
                        // Preparar datos enriquecidos para Google Calendar
                        $googleActivityData = $activityData;
                        $googleActivityData['id'] = $activityId;
                        $googleActivityData['title'] = $enrichedTitle; // Título enriquecido para Google
                        $googleActivityData['description'] = $enrichedDescription; // Descripción enriquecida para Google
                        
                        $googleEventId = $googleCalendar->syncEventToGoogle($googleActivityData);
                        if ($googleEventId) {
                            log_message('info', "Actividad {$activityId} sincronizada con Google Calendar: {$googleEventId}");
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', 'Error sincronizando con Google Calendar: ' . $e->getMessage());
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Actividad creada correctamente.',
                    'activity_id' => $activityId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => ['error' => 'Error al guardar la actividad.']
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', '[createActivity] Error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'errors' => ['error' => 'Error interno del servidor.']
            ]);
        }
    }

    /**
     * Obtener actividades con información del cliente
     */
    private function getActivitiesWithClientInfo($userId, $startDate = null, $endDate = null, $exclusiveEnd = false)
    {
        $clientSelect = '
                clients.full_name as client_name,
                clients.phone as client_phone,
                clients.email as client_email';
        if ($this->activityModel->db->fieldExists('is_funeral_prospect', 'clients')) {
            $clientSelect .= ',
                clients.is_funeral_prospect as client_is_funeral_prospect';
        }

        $builder = $this->activityModel->select('
                calendar_activities.*,' . $clientSelect . '
            ')
            ->join('clients', 'clients.id = calendar_activities.client_id', 'left')
            ->where('calendar_activities.user_id', $userId);

        if ($startDate && $endDate) {
            $endOperator = $exclusiveEnd ? '<' : '<=';
            $builder->where('calendar_activities.start_time >=', $startDate)
                    ->where('calendar_activities.start_time ' . $endOperator, $endDate);
        }

        return $builder->orderBy('calendar_activities.start_time', 'ASC')
                       ->findAll();
    }

    /**
     * Obtener colores para el calendario según estado y tipo
     */
    private function getActivityColor($status, $type)
    {
        $colors = [
            'Pendiente' => [
                'bg' => '#0F2D46', // Azul Pacífico
                'border' => '#0A1F33',
                'text' => '#fff'
            ],
            'Completada' => [
                'bg' => '#CFA75B', // Dorado
                'border' => '#A67C2E',
                'text' => '#fff'
            ],
            'Cancelada' => [
                'bg' => '#6B7280', // Gris Muted
                'border' => '#4B5563',
                'text' => '#fff'
            ]
        ];

        return $colors[$status] ?? $colors['Pendiente'];
    }
    
    /**
     * Obtener minutas pendientes que requieren seguimiento
     */
    private function getPendingMinutes($userId)
    {
        $clientModel = new ClientModel();
        
        // Obtener minutas recientes que podrían necesitar seguimiento
        $recentMinutes = $this->followUpModel
            ->select('client_follow_ups.*, clients.full_name as client_name, clients.phone as client_phone, clients.email as client_email')
            ->join('clients', 'clients.id = client_follow_ups.client_id')
            ->where('client_follow_ups.user_id', $userId)
            ->where('client_follow_ups.created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->orderBy('client_follow_ups.created_at', 'DESC')
            ->limit(10)
            ->findAll();
            
        return $recentMinutes;
    }
    
    /**
     * Obtener estadísticas de actividades del mes
     */
    private function getActivityStats($userId)
    {
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-t 23:59:59');
        
        $total = $this->activityModel
            ->where('user_id', $userId)
            ->where('start_time >=', $startDate)
            ->where('start_time <=', $endDate)
            ->countAllResults();
            
        $completed = $this->activityModel
            ->where('user_id', $userId)
            ->where('status', 'Completada')
            ->where('start_time >=', $startDate)
            ->where('start_time <=', $endDate)
            ->countAllResults();
            
        $pending = $this->activityModel
            ->where('user_id', $userId)
            ->where('status', 'Pendiente')
            ->where('start_time >=', $startDate)
            ->where('start_time <=', $endDate)
            ->countAllResults();
            
        $cancelled = $this->activityModel
            ->where('user_id', $userId)
            ->where('status', 'Cancelada')
            ->where('start_time >=', $startDate)
            ->where('start_time <=', $endDate)
            ->countAllResults();
            
        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'cancelled' => $cancelled
        ];
    }
}