<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActivityFKToFollowUps extends Migration
{
    // Solo el método up() para el ejemplo. Recuerda añadir el método down()
    public function up()
    {
        $this->forge->addForeignKey('activity_id', 'calendar_activities', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('client_follow_ups');
    }

    public function down()
    {
        //
    }
}
