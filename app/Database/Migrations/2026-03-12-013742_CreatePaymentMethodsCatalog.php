<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentMethodsCatalog extends Migration
{
    public function up()
    {
        // 1. Crear la tabla para el catálogo de métodos de pago
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'unique'     => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->createTable('payment_methods');

        // Insertar métodos de pago base para que el catálogo no esté vacío
        $this->db->table('payment_methods')->insertBatch([
            ['name' => 'Infonavit', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Fovissste', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Contado', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Crédito Bancario', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            ['name' => 'Otro', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
        ]);

        // 2. Modificar la tabla 'real_estates' para que 'payment_methods' guarde un JSON de IDs
        $this->forge->modifyColumn('real_estates', [
            'payment_methods' => [
                'name'    => 'payment_methods',
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'JSON array of accepted payment method IDs',
            ],
        ]);

        // Limpiar datos huérfanos en la tabla 'clients' antes de crear la FK.
        // Los valores no numéricos (como "Infonavit") se guardaron como 0 en la columna INT, que no es un ID válido.
        // Los ponemos a NULL para que la FK se pueda crear sin errores.
        $this->db->query('UPDATE `clients` SET `payment_method_id` = NULL WHERE `payment_method_id` IS NOT NULL AND `payment_method_id` NOT IN (SELECT `id` FROM `payment_methods`)');

        // 3. Añadir la Foreign Key a la tabla 'clients' que ya tiene la columna 'payment_method_id'
        // Esto conecta el prospecto con el nuevo catálogo.
        $this->db->query('ALTER TABLE `clients` ADD CONSTRAINT `clients_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // 1. Eliminar la Foreign Key de la tabla 'clients'
        $this->forge->dropForeignKey('clients', 'clients_payment_method_id_foreign');

        // 2. Revertir la modificación en la tabla 'real_estates'
        $this->forge->modifyColumn('real_estates', [
            'payment_methods' => [
                'name' => 'payment_methods',
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '', // Limpiar comentario
            ],
        ]);

        // 3. Eliminar la tabla del catálogo
        $this->forge->dropTable('payment_methods');
    }
}
