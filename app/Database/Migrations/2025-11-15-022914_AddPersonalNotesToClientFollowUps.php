<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPersonalNotesToClientFollowUps extends Migration
{
    public function up()
    {
        $this->forge->addColumn('client_follow_ups', [
            'personal_notes' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Observaciones personales/especiales del cliente (vacaciones, eventos, etc.)'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('client_follow_ups', 'personal_notes');
    }
}
