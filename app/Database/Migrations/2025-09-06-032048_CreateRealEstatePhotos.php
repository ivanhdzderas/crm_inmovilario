<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRealEstatePhotos extends Migration
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
            'real_estate_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('real_estate_id', 'real_estates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('real_estate_photos');
    }

    public function down()
    {
        $this->forge->dropTable('real_estate_photos');
    }
}
