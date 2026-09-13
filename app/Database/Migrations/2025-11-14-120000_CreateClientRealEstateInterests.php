<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientRealEstateInterests extends Migration
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
            'interest_level' => [
                'type' => 'ENUM',
                'constraint' => ['Alto', 'Medio', 'Bajo'],
                'default' => 'Medio',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Notas sobre el interés del cliente en esta propiedad',
            ],
            // Campos para control de apartado
            'apartado_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Monto del apartado pagado (1% del precio)',
            ],
            'apartado_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Fecha cuando se pagó el apartado',
            ],
            'apartado_receipt' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Número de recibo o referencia del apartado',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Interesado', 'Apartado', 'Vendido', 'Cancelado'],
                'default' => 'Interesado',
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
        
        // Índice único para evitar duplicados cliente-inmueble
        $this->forge->addKey(['client_id', 'real_estate_id'], false, true);
        
        // Foreign keys
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('real_estate_id', 'real_estates', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('client_real_estate_interests');
    }

    public function down()
    {
        $this->forge->dropTable('client_real_estate_interests');
    }
}