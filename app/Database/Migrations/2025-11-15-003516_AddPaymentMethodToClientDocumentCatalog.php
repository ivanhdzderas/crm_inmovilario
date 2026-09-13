<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentMethodToClientDocumentCatalog extends Migration
{
    public function up()
    {
        // Agregar campo payment_method
        $this->forge->addColumn('client_document_catalog', [
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Todos',
                'comment'    => 'Método de pago al que aplica: Infonavit, Fovissste, Bancario, Contado, Todos',
                'after'      => 'process_stage'
            ]
        ]);

        // Actualizar datos existentes con documentos específicos por método de pago
        $this->updateExistingDocuments();
        
        // Agregar nuevos documentos específicos por método de pago
        $this->insertPaymentMethodDocuments();
    }

    public function down()
    {
        $this->forge->dropColumn('client_document_catalog', 'payment_method');
    }

    private function updateExistingDocuments()
    {
        $db = \Config\Database::connect();
        
        // Actualizar documentos existentes para ser "Todos" por defecto
        $db->table('client_document_catalog')
           ->update(['payment_method' => 'Todos']);
           
        // Actualizar documentos específicos de Infonavit
        $db->table('client_document_catalog')
           ->where('name', 'Carta de No Adeudo Infonavit')
           ->update(['payment_method' => 'Infonavit']);
    }

    private function insertPaymentMethodDocuments()
    {
        $db = \Config\Database::connect();
        
        $documents = [
            // DOCUMENTOS ESPECÍFICOS INFONAVIT
            [
                'name' => 'Precalificación Infonavit',
                'description' => 'Documento de precalificación vigente del Instituto del Fondo Nacional de la Vivienda',
                'category' => 'Crédito Infonavit',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Infonavit',
                'estimated_days' => 3,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 50,
            ],
            [
                'name' => 'Certificado de Trabajo',
                'description' => 'Constancia laboral vigente con antigüedad mínima de 6 meses',
                'category' => 'Laboral',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Infonavit',
                'estimated_days' => 2,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 51,
            ],
            [
                'name' => 'Buro de Crédito',
                'description' => 'Reporte de buro de crédito no mayor a 30 días',
                'category' => 'Crédito Infonavit',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Infonavit',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 50.00,
                'sort_order' => 52,
            ],

            // DOCUMENTOS ESPECÍFICOS FOVISSSTE
            [
                'name' => 'Solicitud de Crédito Fovissste',
                'description' => 'Formulario de solicitud de crédito Fovissste debidamente llenado',
                'category' => 'Crédito Fovissste',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Fovissste',
                'estimated_days' => 2,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 60,
            ],
            [
                'name' => 'Talón de Pago',
                'description' => 'Últimos 3 talones de pago del empleado',
                'category' => 'Laboral',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Fovissste',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 61,
            ],
            [
                'name' => 'Estado de Cuenta Fovissste',
                'description' => 'Estado de cuenta de puntos Fovissste actualizado',
                'category' => 'Crédito Fovissste',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Fovissste',
                'estimated_days' => 3,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 62,
            ],

            // DOCUMENTOS ESPECÍFICOS BANCARIO
            [
                'name' => 'Solicitud de Crédito Bancario',
                'description' => 'Formulario de solicitud de crédito hipotecario bancario',
                'category' => 'Crédito Bancario',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Bancario',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 70,
            ],
            [
                'name' => 'Referencias Comerciales',
                'description' => 'Mínimo 2 referencias comerciales con historial crediticio',
                'category' => 'Crédito Bancario',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Bancario',
                'estimated_days' => 5,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 71,
            ],
            [
                'name' => 'Comprobante de Ingresos Conyugales',
                'description' => 'Documentos que acrediten ingresos del cónyuge (si aplica)',
                'category' => 'Financieros',
                'document_type' => 'Condicional',
                'process_stage' => 'Apartado',
                'payment_method' => 'Bancario',
                'estimated_days' => 3,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 72,
            ],
            [
                'name' => 'Enganche Bancario',
                'description' => 'Comprobante de enganche mínimo del 20% del valor del inmueble',
                'category' => 'Financieros',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Bancario',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 73,
            ],

            // DOCUMENTOS ESPECÍFICOS CONTADO
            [
                'name' => 'Comprobante de Fondos',
                'description' => 'Documentación que acredite la disponibilidad total del recurso',
                'category' => 'Financieros',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Contado',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 80,
            ],
            [
                'name' => 'Carta de Procedencia de Recursos',
                'description' => 'Documento que acredita la procedencia lícita de los recursos',
                'category' => 'Legal',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'payment_method' => 'Contado',
                'estimated_days' => 5,
                'requires_notary' => true,
                'cost_estimate' => 500.00,
                'sort_order' => 81,
            ],
            [
                'name' => 'Pago Total Anticipado',
                'description' => 'Comprobante de pago total del inmueble',
                'category' => 'Financieros',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Escrituración',
                'payment_method' => 'Contado',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 82,
            ],
        ];

        foreach ($documents as $document) {
            $document['created_at'] = date('Y-m-d H:i:s');
            $document['updated_at'] = date('Y-m-d H:i:s');
            $document['is_active'] = true;
            
            $db->table('client_document_catalog')->insert($document);
        }
    }
}
