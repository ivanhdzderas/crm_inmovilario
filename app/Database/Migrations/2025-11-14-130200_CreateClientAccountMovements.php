<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientAccountMovements extends Migration
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
            'client_account_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'document_catalog_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Referencia al catálogo de documentos (null para ingresos)',
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['Ingreso', 'Egreso'],
                'null' => false,
                'comment' => 'Tipo de movimiento',
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'comment' => 'Monto del movimiento',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'comment' => 'Descripción del movimiento',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Pendiente', 'En Proceso', 'Completado', 'Cancelado'],
                'default' => 'Pendiente',
            ],
            'receipt_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Número de factura o recibo',
            ],
            'provider' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'comment' => 'Proveedor del servicio',
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha límite para el trámite',
            ],
            'completed_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Fecha de completado',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Notas adicionales del movimiento',
            ],
            'created_by_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario que registró el movimiento',
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
        $this->forge->addKey('client_account_id');
        $this->forge->addKey('type');
        $this->forge->addKey('status');
        
        // Foreign keys
        $this->forge->addForeignKey('client_account_id', 'client_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('document_catalog_id', 'document_catalog', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('client_account_movements');
    }

    public function down()
    {
        $this->forge->dropTable('client_account_movements');
    }
}