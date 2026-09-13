<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveLatitudeLongitudeFromRealEstates extends Migration
{
    public function up()
    {
        // Eliminar campos de latitud y longitud que ya no se necesitan
        $this->forge->dropColumn('real_estates', ['latitude', 'longitude']);
    }

    public function down()
    {
        // Restaurar los campos en caso de rollback
        $this->forge->addColumn('real_estates', [
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,8',
                'null' => true,
                'comment' => 'Latitud de la ubicación del inmueble',
                'after' => 'address'
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '11,8',
                'null' => true,
                'comment' => 'Longitud de la ubicación del inmueble',
                'after' => 'latitude'
            ]
        ]);
    }
}
