<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCancellationReasons extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['prospect', 'apartado', 'both'],
                'default'    => 'both',
                'comment'    => 'prospect=descarte prospecto, apartado=cancelación apartado/venta',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('cancellation_reasons');

        $now = date('Y-m-d H:i:s');
        $reasons = [
            ['Ya compró otra propiedad', 'prospect', 10],
            ['Mucho tiempo sin respuesta', 'prospect', 20],
            ['No califica para crédito', 'both', 30],
            ['Fuera de presupuesto', 'both', 40],
            ['Ya no está interesado', 'both', 50],
            ['Contacto perdido', 'prospect', 60],
            ['Cambió de opinión sobre el inmueble', 'apartado', 70],
            ['Problemas económicos', 'apartado', 80],
            ['Encontró otra propiedad', 'apartado', 90],
            ['Incumplimiento de pagos', 'apartado', 100],
            ['Crédito no autorizado', 'apartado', 110],
            ['Error o apartado duplicado', 'apartado', 120],
            ['Otro', 'both', 999],
        ];

        $builder = $this->db->table('cancellation_reasons');
        foreach ($reasons as [$name, $category, $sortOrder]) {
            $builder->insert([
                'name'        => $name,
                'category'    => $category,
                'is_active'   => 1,
                'sort_order'  => $sortOrder,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('cancellation_reasons');
    }
}
