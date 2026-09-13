<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClientDocumentChecklist extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'client_account_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'comment'    => 'ID de la cuenta del cliente (apartado)',
            ],
            'client_document_catalog_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'comment'    => 'ID del documento del catálogo',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Pendiente', 'En Proceso', 'Completado', 'No Aplica', 'Vencido'],
                'default'    => 'Pendiente',
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
                'comment' => 'Fecha límite para obtener el documento',
            ],
            'completed_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Fecha cuando se completó el documento',
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => true,
                'comment' => 'Ruta del archivo subido',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Notas adicionales del documento',
            ],
            'actual_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'comment'    => 'Costo real del trámite',
            ],
            'assigned_to_user_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Usuario responsable del seguimiento',
            ],
            'created_by_user_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('client_document_catalog_id');
        $this->forge->addKey(['status', 'due_date']);
        
        // Foreign Keys
        $this->forge->addForeignKey('client_account_id', 'client_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('client_document_catalog_id', 'client_document_catalog', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_to_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        
        $this->forge->createTable('client_document_checklist');
    }

    public function down()
    {
        $this->forge->dropTable('client_document_checklist');
    }
}
