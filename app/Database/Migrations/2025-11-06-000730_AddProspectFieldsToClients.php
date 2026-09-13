<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProspectFieldsToClients extends Migration
{
    public function up()
    {
        // 1. Añadir los campos necesarios a la tabla 'clients'
        $fields = [
            'credit_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'phone',
                'comment'    => 'Monto de crédito o presupuesto disponible del prospecto.',
            ],
            // 2. Añadir la FK a Zonas (catálogo que ya existe)
            'zone_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'credit_amount',
                'comment'    => 'Zona de Tepic o lugar que le interesa al prospecto.',
            ],
            // 3. Añadir la FK a Método de Compra/Pago (asumiendo que crearás una tabla payment_methods)
            'payment_method_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'zone_id',
                'comment'    => 'Tipo de crédito o método de compra (Infonavit, Contado, etc.).',
            ],
            // 4. Añadir un campo para el estatus de seguimiento
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Nuevo',
                'after'      => 'payment_method_id',
                'comment'    => 'Estado del seguimiento (Nuevo, Interesado, Apartado, Cerrado, etc.).',
            ],
        ];

        $this->forge->addColumn('clients', $fields);

        // 5. Añadir las Foreign Keys (solo a 'zones' por ahora, ya que 'payment_methods' no está en el dump)
        $this->forge->addForeignKey('zone_id', 'zones', 'id', 'SET NULL', 'CASCADE');

        $this->forge->processIndexes('clients');
    }

    public function down()
    {
        // Esto es para revertir la migración si fuera necesario
        $this->forge->dropForeignKey('clients', 'clients_zone_id_foreign');
        $this->forge->dropColumn('clients', ['credit_amount', 'zone_id', 'payment_method_id', 'status']);
    }
}