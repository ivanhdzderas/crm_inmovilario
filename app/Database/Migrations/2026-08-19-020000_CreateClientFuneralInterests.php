<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientFuneralInterests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'                   => ['type' => 'INT', 'unsigned' => true],
            'funeral_product_id'          => ['type' => 'INT', 'unsigned' => true],
            'interest_level'              => ['type' => 'ENUM', 'constraint' => ['Alto', 'Medio', 'Bajo'], 'default' => 'Medio'],
            'payment_frequency'           => [
                'type'       => 'ENUM',
                'constraint' => ['Semanal', 'Quincenal', 'Mensual', 'Fin de mes'],
                'null'       => true,
                'comment'    => 'Preferencia de cobro del cliente',
            ],
            'selected_financing_plan_id'  => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Plan elegido cuando deja de analizar opciones',
            ],
            'notes'                       => ['type' => 'TEXT', 'null' => true],
            'status'                      => [
                'type'       => 'ENUM',
                'constraint' => ['Interesado', 'En análisis', 'Definido', 'Cancelado'],
                'default'    => 'Interesado',
            ],
            'created_at'                  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'                  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['client_id', 'funeral_product_id']);
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('funeral_product_id', 'funeral_products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('selected_financing_plan_id', 'funeral_financing_plans', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('client_funeral_interests');

        $this->forge->addField([
            'id'                          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_funeral_interest_id'  => ['type' => 'INT', 'unsigned' => true],
            'funeral_financing_plan_id'   => ['type' => 'INT', 'unsigned' => true],
            'created_at'                  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['client_funeral_interest_id', 'funeral_financing_plan_id']);
        $this->forge->addForeignKey('client_funeral_interest_id', 'client_funeral_interests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('funeral_financing_plan_id', 'funeral_financing_plans', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('client_funeral_interest_plans');
    }

    public function down()
    {
        $this->forge->dropTable('client_funeral_interest_plans', true);
        $this->forge->dropTable('client_funeral_interests', true);
    }
}
