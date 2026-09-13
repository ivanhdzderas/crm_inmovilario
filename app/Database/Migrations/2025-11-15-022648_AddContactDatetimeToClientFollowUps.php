<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContactDatetimeToClientFollowUps extends Migration
{
    public function up()
    {
        $this->forge->addColumn('client_follow_ups', [
            'contact_datetime' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Fecha y hora real del contacto con el cliente'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('client_follow_ups', 'contact_datetime');
    }
}
