<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClientDocumentCatalog extends Migration
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => 'General',
            ],
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Obligatorio', 'Opcional', 'Condicional'],
                'default'    => 'Obligatorio',
            ],
            'process_stage' => [
                'type'       => 'ENUM',
                'constraint' => ['Apartado', 'Escrituración', 'Entrega', 'Post-venta'],
                'default'    => 'Apartado',
            ],
            'estimated_days' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 7,
                'comment'    => 'Días estimados para obtener el documento',
            ],
            'requires_notary' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'comment'    => 'Indica si requiere notario',
            ],
            'cost_estimate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'comment'    => 'Costo estimado del trámite',
            ],
            'is_active' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 999,
                'comment'    => 'Orden de presentación en checklist',
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
        $this->forge->addKey(['category', 'process_stage']);
        $this->forge->addKey('sort_order');
        $this->forge->createTable('client_document_catalog');

        // Insertar datos por defecto
        $this->insertDefaultDocuments();
    }

    public function down()
    {
        $this->forge->dropTable('client_document_catalog');
    }

    private function insertDefaultDocuments()
    {
        $data = [
            // APARTADO
            [
                'name' => 'Identificación Oficial',
                'description' => 'INE, pasaporte o licencia de manejo vigente del comprador',
                'category' => 'Identificación',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'CURP',
                'description' => 'Clave Única de Registro de Población actualizada',
                'category' => 'Identificación',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 2,
            ],
            [
                'name' => 'RFC',
                'description' => 'Registro Federal de Contribuyentes',
                'category' => 'Identificación',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 3,
            ],
            [
                'name' => 'Comprobante de Ingresos',
                'description' => 'Últimos 3 recibos de nómina o estados de cuenta',
                'category' => 'Financieros',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'estimated_days' => 3,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 4,
            ],
            [
                'name' => 'Estado de Cuenta Bancario',
                'description' => 'Últimos 3 meses de movimientos bancarios',
                'category' => 'Financieros',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Apartado',
                'estimated_days' => 2,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 5,
            ],
            [
                'name' => 'Carta de No Adeudo Infonavit',
                'description' => 'Documento que certifica no tener crédito Infonavit vigente',
                'category' => 'Crédito',
                'document_type' => 'Condicional',
                'process_stage' => 'Apartado',
                'estimated_days' => 5,
                'requires_notary' => false,
                'cost_estimate' => 150.00,
                'sort_order' => 6,
            ],

            // ESCRITURACIÓN
            [
                'name' => 'Acta de Matrimonio',
                'description' => 'Acta de matrimonio certificada (si aplica)',
                'category' => 'Estado Civil',
                'document_type' => 'Condicional',
                'process_stage' => 'Escrituración',
                'estimated_days' => 7,
                'requires_notary' => true,
                'cost_estimate' => 200.00,
                'sort_order' => 7,
            ],
            [
                'name' => 'Certificado de Libertad de Gravamen',
                'description' => 'Documento que certifica que el inmueble está libre de gravámenes',
                'category' => 'Notaría',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Escrituración',
                'estimated_days' => 10,
                'requires_notary' => true,
                'cost_estimate' => 800.00,
                'sort_order' => 8,
            ],
            [
                'name' => 'Avalúo Bancario',
                'description' => 'Avalúo comercial realizado por institución bancaria',
                'category' => 'Financieros',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Escrituración',
                'estimated_days' => 15,
                'requires_notary' => false,
                'cost_estimate' => 3500.00,
                'sort_order' => 9,
            ],
            [
                'name' => 'Escritura Pública',
                'description' => 'Elaboración de escritura pública ante notario',
                'category' => 'Notaría',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Escrituración',
                'estimated_days' => 20,
                'requires_notary' => true,
                'cost_estimate' => 12000.00,
                'sort_order' => 10,
            ],

            // ENTREGA
            [
                'name' => 'Registro Público de la Propiedad',
                'description' => 'Inscripción en el Registro Público de la Propiedad',
                'category' => 'Registro',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Entrega',
                'estimated_days' => 30,
                'requires_notary' => false,
                'cost_estimate' => 2500.00,
                'sort_order' => 11,
            ],
            [
                'name' => 'Entrega de Llaves',
                'description' => 'Acta de entrega física del inmueble',
                'category' => 'Entrega',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Entrega',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 12,
            ],

            // POST-VENTA
            [
                'name' => 'Manual de Usuario',
                'description' => 'Manual de mantenimiento y garantías del inmueble',
                'category' => 'Post-venta',
                'document_type' => 'Obligatorio',
                'process_stage' => 'Post-venta',
                'estimated_days' => 1,
                'requires_notary' => false,
                'cost_estimate' => 0.00,
                'sort_order' => 13,
            ],
        ];

        $builder = \Config\Database::connect()->table('client_document_catalog');
        foreach ($data as $document) {
            $document['created_at'] = date('Y-m-d H:i:s');
            $document['updated_at'] = date('Y-m-d H:i:s');
            $builder->insert($document);
        }
    }
}
