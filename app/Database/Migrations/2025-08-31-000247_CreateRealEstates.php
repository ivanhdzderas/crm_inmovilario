<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRealEstates extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            // ... (el resto de tus campos)
            'sold_to_client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'project_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_by_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'updated_by_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
       $this->forge->addKey('id', true);
        $this->forge->addForeignKey('property_type_id', 'property_types', 'id');
        $this->forge->addForeignKey('zone_id', 'zones', 'id');
        $this->forge->addForeignKey('sold_to_client_id', 'clients', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('project_id', 'real_estate_projects', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', '', 'SET NULL');
        $this->forge->addForeignKey('updated_by_user_id', 'users', 'id', '', 'SET NULL');
        $this->forge->createTable('real_estates');
    }

    public function down()
    {
        $this->forge->dropTable('real_estates');
    }
}