<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectIdToRealEstates extends Migration
{
    public function up()
    {
        $fields = [
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'description_general',
            ],
        ];

        $this->forge->addColumn('real_estates', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('real_estates', 'project_id');
    }
}