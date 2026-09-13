<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientAccounts extends Migration
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
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'real_estate_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'real_estate_interest_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Referencia al interés que originó esta cuenta',
            ],
            'apartado_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0.00,
                'comment' => 'Monto del apartado (ingreso inicial)',
            ],
            'total_expenses' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0.00,
                'comment' => 'Suma total de gastos',
            ],
            'current_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0.00,
                'comment' => 'Balance actual (apartado - gastos)',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Activa', 'Cerrada', 'Suspendida'],
                'default' => 'Activa',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Notas adicionales sobre la cuenta',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('client_id');
        $this->forge->addKey('real_estate_id');
        
        // Índice único para evitar cuentas duplicadas por cliente-inmueble
        $this->forge->addKey(['client_id', 'real_estate_id'], false, true);
        
        // Foreign keys
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('real_estate_id', 'real_estates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('real_estate_interest_id', 'client_real_estate_interests', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('client_accounts');
    }

    public function down()
    {
        $this->forge->dropTable('client_accounts');
    }
}