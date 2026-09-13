<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRealEstateAmenities extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'real_estate_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'amenity_type'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'value'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('real_estate_id', 'real_estates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('real_estate_amenities');
    }

    public function down()
    {
        $this->forge->dropTable('real_estate_amenities');
    }
}
