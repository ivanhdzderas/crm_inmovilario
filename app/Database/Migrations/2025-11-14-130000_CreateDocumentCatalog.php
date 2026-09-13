<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentCatalog extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Nombre del trámite/documento',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Descripción detallada del trámite',
            ],
            'category' => [
                'type' => 'ENUM',
                'constraint' => ['Avalúo', 'Legal', 'Administrativo', 'Notarial', 'Registro', 'Financiero', 'Otro'],
                'default' => 'Otro',
            ],
            'default_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
                'default' => 0.00,
                'comment' => 'Costo promedio del trámite',
            ],
            'is_required' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=Obligatorio, 0=Opcional',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=Activo, 0=Inactivo',
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
        $this->forge->addKey('category');
        $this->forge->createTable('document_catalog');

        // Insertar datos iniciales
        $data = [
            [
                'name' => 'Avalúo Comercial',
                'description' => 'Avalúo profesional del inmueble por perito certificado',
                'category' => 'Avalúo',
                'default_cost' => 3500.00,
                'is_required' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Certificado de Libertad de Gravamen',
                'description' => 'Documento que certifica que el inmueble está libre de cargas',
                'category' => 'Legal',
                'default_cost' => 250.00,
                'is_required' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Escrituración',
                'description' => 'Gastos notariales para escrituración',
                'category' => 'Notarial',
                'default_cost' => 12000.00,
                'is_required' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Registro Público',
                'description' => 'Inscripción en Registro Público de la Propiedad',
                'category' => 'Registro',
                'default_cost' => 1500.00,
                'is_required' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Gestión Administrativa',
                'description' => 'Gastos administrativos y de gestión',
                'category' => 'Administrativo',
                'default_cost' => 2000.00,
                'is_required' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('document_catalog')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('document_catalog');
    }
}