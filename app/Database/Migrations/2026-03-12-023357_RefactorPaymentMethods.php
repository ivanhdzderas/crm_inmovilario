<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorPaymentMethods extends Migration
{
    public function up()
    {
        // 1. Intentar eliminar la llave foránea de la tabla 'clients'.
        // Se usa un bloque try-catch por si el nombre de la restricción varía o ya no existe.
        try {
            $this->forge->dropForeignKey('clients', 'clients_payment_method_id_foreign');
        } catch (\Exception $e) {
            // Ignorar el error si la llave no existe.
        }

        // 2. Modificar la columna 'payment_method_id' en 'clients'.
        // Se renombra a 'payment_method' y se cambia su tipo a VARCHAR para guardar el nombre del crédito.
        $this->forge->modifyColumn('clients', [
            'payment_method_id' => [
                'name' => 'payment_method',
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Tipo de crédito (Infonavit, Contado, etc.)',
            ],
        ]);

        // 3. Eliminar la tabla 'payment_methods' que ya no es necesaria.
        $this->forge->dropTable('payment_methods', true); // El 'true' añade 'IF EXISTS'
    }

    public function down()
    {
        // --- Este método revierte los cambios si es necesario ---

        // 1. Volver a crear la tabla 'payment_methods'
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('payment_methods');

        // 2. Revertir la columna 'payment_method' en 'clients' a su estado anterior
        $this->forge->modifyColumn('clients', [
            'payment_method' => [
                'name' => 'payment_method_id',
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
            ],
        ]);

        // 3. Volver a añadir la llave foránea
        $this->db->query('ALTER TABLE `clients` ADD CONSTRAINT `clients_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }
}
