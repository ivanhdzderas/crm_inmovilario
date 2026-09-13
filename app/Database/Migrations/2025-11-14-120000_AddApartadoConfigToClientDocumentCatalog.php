<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApartadoConfigToClientDocumentCatalog extends Migration
{
    public function up()
    {
        // Agregar campos para configuración de apartado
        $this->forge->addColumn('client_document_catalog', [
            'apartado_type' => [
                'type' => 'ENUM',
                'constraint' => ['percentage', 'fixed_amount', 'not_applicable'],
                'default' => 'not_applicable',
                'null' => true,
                'comment' => 'Tipo de apartado: porcentaje, monto fijo o no aplica',
                'after' => 'process_stage'
            ],
            'apartado_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'comment' => 'Porcentaje del apartado (ej: 1.00 para 1%)',
                'after' => 'apartado_type'
            ],
            'apartado_fixed_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'comment' => 'Monto fijo del apartado en pesos',
                'after' => 'apartado_percentage'
            ],
            'apartado_description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Descripción personalizada del apartado',
                'after' => 'apartado_fixed_amount'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('client_document_catalog', [
            'apartado_type',
            'apartado_percentage', 
            'apartado_fixed_amount',
            'apartado_description'
        ]);
    }
}