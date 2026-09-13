<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFuneralModule extends Migration
{
    public function up()
    {
        // Lugar físico de último descanso (un cementerio / parque puede tener varias funerarias)
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'address'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'zone_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('zone_id', 'zones', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('funeral_locations');

        // Marca / funeraria (Crespo, Fernández, Manzano…)
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('funeral_homes');

        // Varias funerarias operan en un mismo lugar
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'funeral_location_id' => ['type' => 'INT', 'unsigned' => true],
            'funeral_home_id'     => ['type' => 'INT', 'unsigned' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['funeral_location_id', 'funeral_home_id']);
        $this->forge->addForeignKey('funeral_location_id', 'funeral_locations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('funeral_home_id', 'funeral_homes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('funeral_location_homes');

        // Tipos: cripta, nicho, plan funerario…
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('funeral_product_types');

        // Paquete / producto funerario
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'clave'               => ['type' => 'VARCHAR', 'constraint' => 50],
            'name'                => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'                => ['type' => 'VARCHAR', 'constraint' => 255],
            'funeral_location_id' => ['type' => 'INT', 'unsigned' => true],
            'funeral_home_id'     => ['type' => 'INT', 'unsigned' => true],
            'product_type_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'base_price'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'description'         => ['type' => 'TEXT', 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['Disponible', 'No disponible'], 'default' => 'Disponible'],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('clave');
        $this->forge->addKey('slug');
        $this->forge->addForeignKey('funeral_location_id', 'funeral_locations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('funeral_home_id', 'funeral_homes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_type_id', 'funeral_product_types', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('funeral_products');

        // Planes de financiamiento por producto (enganche + mensualidad × meses)
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'funeral_product_id' => ['type' => 'INT', 'unsigned' => true],
            'name'               => ['type' => 'VARCHAR', 'constraint' => 120],
            'enganche'           => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'mensualidad'        => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'months'             => ['type' => 'INT', 'unsigned' => true, 'default' => 12],
            'sort_order'         => ['type' => 'INT', 'default' => 0],
            'is_active'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('funeral_product_id', 'funeral_products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('funeral_financing_plans');

        $now = date('Y-m-d H:i:s');
        $types = ['Cripta', 'Nicho', 'Plan funerario completo', 'Lote', 'Mausoleo', 'Servicio de cremación'];
        foreach ($types as $i => $typeName) {
            $this->db->table('funeral_product_types')->insert([
                'name'       => $typeName,
                'sort_order' => ($i + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('funeral_financing_plans', true);
        $this->forge->dropTable('funeral_products', true);
        $this->forge->dropTable('funeral_product_types', true);
        $this->forge->dropTable('funeral_location_homes', true);
        $this->forge->dropTable('funeral_homes', true);
        $this->forge->dropTable('funeral_locations', true);
    }
}
