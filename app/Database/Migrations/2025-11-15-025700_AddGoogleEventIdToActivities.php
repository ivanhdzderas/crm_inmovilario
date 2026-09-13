<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleEventIdToActivities extends Migration
{
    public function up()
    {
        $this->forge->addColumn('calendar_activities', [
            'google_event_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'ID del evento correspondiente en Google Calendar'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('calendar_activities', ['google_event_id']);
    }
}