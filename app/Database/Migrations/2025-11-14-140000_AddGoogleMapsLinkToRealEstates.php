<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleMapsLinkToRealEstates extends Migration
{
    public function up()
    {
        // Agregar campo para la liga de Google Maps
        $this->forge->addColumn('real_estates', [
            'google_maps_link' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Liga de Google Maps de la ubicación del inmueble',
                'after' => 'longitude'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('real_estates', 'google_maps_link');
    }
}