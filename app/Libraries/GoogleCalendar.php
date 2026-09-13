<?php

namespace App\Libraries;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendar
{
    private $client;
    private $service;
    private $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
        $this->initializeClient();
    }

    /**
     * Inicializar cliente de Google
     */
    private function initializeClient()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Tony CRM - Calendario Integrado');
        $this->client->setScopes([Calendar::CALENDAR]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        // Configurar credenciales OAuth2
        $this->client->setAuthConfig([
            'web' => [
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                'redirect_uris' => [base_url('agenda/google/callback')]
            ]
        ]);

        // Si hay usuario, cargar sus tokens
        if ($this->userId) {
            $this->loadUserTokens();
        }

        $this->service = new Calendar($this->client);
    }

    /**
     * Cargar tokens del usuario desde la base de datos
     */
    private function loadUserTokens()
    {
        $userModel = model('UserModel');
        $user = $userModel->find($this->userId);

        if ($user && !empty($user['google_calendar_token'])) {
            $token = json_decode($user['google_calendar_token'], true);
            $this->client->setAccessToken($token);

            // Refresh token si es necesario
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->saveUserTokens($this->client->getAccessToken());
                }
            }
        }
    }

    /**
     * Guardar tokens del usuario en la base de datos
     */
    public function saveUserTokens($token)
    {
        try {
            $userModel = model('UserModel');
            
            // Debug: Log antes de guardar
            log_message('debug', 'Saving Google Calendar tokens for user: ' . $this->userId);
            
            $updateData = [
                'google_calendar_token' => json_encode($token),
                'google_calendar_connected' => 1, // Explícitamente 1 en lugar de true
                'google_calendar_last_sync' => date('Y-m-d H:i:s')
            ];
            
            log_message('debug', 'Update data: ' . json_encode($updateData));
            
            $result = $userModel->update($this->userId, $updateData);
            
            log_message('debug', 'Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            
            if (!$result) {
                // Obtener errores del modelo si los hay
                $errors = $userModel->errors();
                log_message('error', 'Failed to save Google Calendar tokens for user: ' . $this->userId . '. Errors: ' . json_encode($errors));
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error saving Google Calendar tokens: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener URL de autorización de Google
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Procesar código de autorización de Google
     */
    public function handleCallback($code)
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            
            if (isset($token['error'])) {
                log_message('error', 'Google OAuth error: ' . $token['error']);
                return false;
            }
            
            $this->client->setAccessToken($token);
            $result = $this->saveUserTokens($token);
            
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error in Google Calendar callback: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si el usuario está conectado a Google Calendar
     */
    public function isConnected()
    {
        return $this->client->getAccessToken() && !$this->client->isAccessTokenExpired();
    }

    /**
     * Sincronizar evento del CRM a Google Calendar
     */
    public function syncEventToGoogle($activity)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Verificar si el evento ya existe en Google Calendar
            if (!empty($activity['google_event_id'])) {
                // Evento ya existe, actualizarlo
                return $this->updateGoogleEvent($activity);
            } else {
                // Evento nuevo, crearlo
                return $this->createGoogleEvent($activity);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error sincronizando evento a Google: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevo evento en Google Calendar
     */
    public function createGoogleEvent($activity)
    {
        $event = new Event([
            'summary' => $activity['title'],
            'description' => $activity['description'] . "\n\n[Creado desde Tony CRM]",
            'start' => [
                'dateTime' => date('c', strtotime($activity['start_time'])),
                'timeZone' => 'America/Mexico_City'
            ],
            'end' => [
                'dateTime' => $activity['end_time'] 
                    ? date('c', strtotime($activity['end_time'])) 
                    : date('c', strtotime($activity['start_time'] . ' +1 hour')),
                'timeZone' => 'America/Mexico_City'
            ],
            'colorId' => $this->getColorByStatus($activity['status']),
            'extendedProperties' => [
                'private' => [
                    'tony_crm_id' => $activity['id'],
                    'tony_crm_type' => $activity['type'],
                    'tony_crm_client_id' => $activity['client_id']
                ]
            ]
        ]);

        $calendarId = 'primary';
        $createdEvent = $this->service->events->insert($calendarId, $event);
        
        // Guardar ID del evento de Google en la base de datos
        $activityModel = model('CalendarActivityModel');
        $activityModel->update($activity['id'], [
            'google_event_id' => $createdEvent->getId()
        ]);

        return $createdEvent->getId();
    }

    /**
     * Actualizar evento en Google Calendar
     */
    public function updateGoogleEvent($activity)
    {
        if (!$this->isConnected() || empty($activity['google_event_id'])) {
            return false;
        }

        try {
            $event = $this->service->events->get('primary', $activity['google_event_id']);
            
            $event->setSummary($activity['title']);
            $event->setDescription($activity['description'] . "\n\n[Actualizado desde Tony CRM]");
            
            $start = new EventDateTime();
            $start->setDateTime(date('c', strtotime($activity['start_time'])));
            $start->setTimeZone('America/Mexico_City');
            $event->setStart($start);

            $end = new EventDateTime();
            $end->setDateTime($activity['end_time'] 
                ? date('c', strtotime($activity['end_time'])) 
                : date('c', strtotime($activity['start_time'] . ' +1 hour')));
            $end->setTimeZone('America/Mexico_City');
            $event->setEnd($end);

            $event->setColorId($this->getColorByStatus($activity['status']));

            $this->service->events->update('primary', $activity['google_event_id'], $event);
            return true;
        } catch (Exception $e) {
            log_message('error', 'Error actualizando evento en Google: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar evento de Google Calendar
     */
    public function deleteGoogleEvent($googleEventId)
    {
        if (!$this->isConnected() || empty($googleEventId)) {
            return false;
        }

        try {
            $this->service->events->delete('primary', $googleEventId);
            return true;
        } catch (Exception $e) {
            log_message('error', 'Error eliminando evento de Google: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Importar eventos de Google Calendar al CRM
     */
    public function importGoogleEvents($startDate = null, $endDate = null)
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $optParams = [
                'maxResults' => 100,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => $startDate ? date('c', strtotime($startDate)) : date('c'),
                'timeMax' => $endDate ? date('c', strtotime($endDate)) : date('c', strtotime('+1 month'))
            ];

            $results = $this->service->events->listEvents('primary', $optParams);
            $events = $results->getItems();

            $importedEvents = [];
            $activityModel = model('CalendarActivityModel');

            foreach ($events as $event) {
                // Skip si ya existe en el CRM
                $extendedProps = $event->getExtendedProperties();
                if ($extendedProps && isset($extendedProps['private']['tony_crm_id'])) {
                    continue;
                }

                // Importar evento
                $startTime = $event->getStart()->getDateTime() ?: $event->getStart()->getDate();
                $endTime = $event->getEnd()->getDateTime() ?: $event->getEnd()->getDate();

                $activityData = [
                    'user_id' => $this->userId,
                    'title' => $event->getSummary() ?: 'Evento de Google Calendar',
                    'description' => $event->getDescription() . "\n\n[Importado de Google Calendar]",
                    'start_time' => date('Y-m-d H:i:s', strtotime($startTime)),
                    'end_time' => $endTime ? date('Y-m-d H:i:s', strtotime($endTime)) : null,
                    'type' => 'Importado',
                    'status' => 'Pendiente',
                    'google_event_id' => $event->getId()
                ];

                $activityId = $activityModel->insert($activityData);
                if ($activityId) {
                    $importedEvents[] = $activityData;
                }
            }

            return $importedEvents;
        } catch (Exception $e) {
            log_message('error', 'Error importando eventos de Google: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpiar eventos duplicados en Google Calendar
     */
    public function cleanDuplicateEvents($activity)
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            // Buscar eventos con el ID del CRM en las propiedades extendidas
            $events = $this->service->events->listEvents('primary', [
                'timeMin' => date('c', strtotime($activity['start_time'] . ' -1 day')),
                'timeMax' => date('c', strtotime($activity['start_time'] . ' +1 day')),
                'singleEvents' => true,
                'orderBy' => 'startTime'
            ]);

            $duplicateEvents = [];
            foreach ($events->getItems() as $event) {
                $extendedProperties = $event->getExtendedProperties();
                if ($extendedProperties && $extendedProperties->getPrivate()) {
                    $privateProps = $extendedProperties->getPrivate();
                    if (isset($privateProps['tony_crm_id']) && $privateProps['tony_crm_id'] == $activity['id']) {
                        $duplicateEvents[] = $event;
                    }
                }
            }

            // Si hay más de un evento, eliminar los duplicados (excepto el más reciente)
            if (count($duplicateEvents) > 1) {
                // Ordenar por fecha de creación y mantener solo el más reciente
                usort($duplicateEvents, function($a, $b) {
                    return strcmp($b->getCreated(), $a->getCreated());
                });

                // Eliminar todos excepto el primero (más reciente)
                for ($i = 1; $i < count($duplicateEvents); $i++) {
                    $this->service->events->delete('primary', $duplicateEvents[$i]->getId());
                    log_message('info', "Evento duplicado eliminado: " . $duplicateEvents[$i]->getId());
                }

                // Actualizar el google_event_id en la base de datos con el evento que se mantiene
                $activityModel = model('CalendarActivityModel');
                $activityModel->update($activity['id'], [
                    'google_event_id' => $duplicateEvents[0]->getId()
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            log_message('error', 'Error limpiando eventos duplicados: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Desconectar Google Calendar
     */
    public function disconnect()
    {
        $userModel = model('UserModel');
        $userModel->update($this->userId, [
            'google_calendar_token' => null,
            'google_calendar_connected' => false
        ]);

        // Optionally revoke token
        if ($this->client->getAccessToken()) {
            $this->client->revokeToken();
        }
    }

    /**
     * Obtener color basado en el estado
     */
    private function getColorByStatus($status)
    {
        $colors = [
            'Pendiente' => '1',    // Azul
            'Completada' => '10',  // Verde
            'Cancelada' => '8',    // Gris
        ];

        return $colors[$status] ?? '1';
    }
}