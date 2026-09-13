<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleCalendarToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'google_calendar_token' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Token de acceso para Google Calendar API'
            ],
            'google_calendar_connected' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'comment' => 'Indica si el usuario tiene Google Calendar conectado'
            ],
            'google_calendar_last_sync' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Última sincronización con Google Calendar'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['google_calendar_token', 'google_calendar_connected', 'google_calendar_last_sync']);
    }
}