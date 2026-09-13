<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeAdvisorIdNullableInFollowUps extends Migration
{
    public function up()
    {
        // Modify column advisor_id to be nullable to avoid insertion failures when no advisor exists
        $fields = [
            'advisor_id' => [
                'name'       => 'advisor_id',
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];

        $this->forge->modifyColumn('client_follow_ups', $fields);
    }

    public function down()
    {
        // Revert advisor_id to not null (default). WARNING: this may fail if null values exist.
        $fields = [
            'advisor_id' => [
                'name'       => 'advisor_id',
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ];

        $this->forge->modifyColumn('client_follow_ups', $fields);
    }
}
