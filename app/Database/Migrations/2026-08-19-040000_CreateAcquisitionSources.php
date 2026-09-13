<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcquisitionSources extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 120],
            'channel_type' => [
                'type'       => 'ENUM',
                'constraint' => ['social', 'web', 'referral', 'offline', 'other'],
                'default'    => 'other',
                'comment'    => 'social=redes, web=internet, referral=referido, offline=presencial',
            ],
            'icon_class'   => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'is_active'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order'   => ['type' => 'INT', 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('acquisition_sources');

        if (! $this->db->fieldExists('acquisition_source_id', 'clients')) {
            $this->forge->addColumn('clients', [
                'acquisition_source_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'status',
                ],
                'acquisition_detail' => [
                    'type'    => 'VARCHAR',
                    'constraint' => 255,
                    'null'    => true,
                    'after'   => 'acquisition_source_id',
                    'comment' => 'Detalle: campaña, referido por, publicación, etc.',
                ],
            ]);
            $this->db->query('ALTER TABLE clients ADD CONSTRAINT fk_clients_acquisition_source FOREIGN KEY (acquisition_source_id) REFERENCES acquisition_sources(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }

        $now = date('Y-m-d H:i:s');
        $sources = [
            ['Facebook', 'social', 'fab fa-facebook', 10],
            ['Instagram', 'social', 'fab fa-instagram', 20],
            ['TikTok', 'social', 'fab fa-tiktok', 30],
            ['YouTube', 'social', 'fab fa-youtube', 40],
            ['WhatsApp', 'social', 'fab fa-whatsapp', 50],
            ['Google / Búsqueda', 'web', 'fab fa-google', 60],
            ['Página web / Catálogo', 'web', 'fas fa-globe', 70],
            ['Referido (cliente o conocido)', 'referral', 'fas fa-user-friends', 80],
            ['Feria o evento', 'offline', 'fas fa-store', 90],
            ['Llamada directa', 'offline', 'fas fa-phone', 100],
            ['Otro', 'other', 'fas fa-question-circle', 999],
        ];

        foreach ($sources as [$name, $channel, $icon, $sort]) {
            $this->db->table('acquisition_sources')->insert([
                'name'         => $name,
                'channel_type' => $channel,
                'icon_class'   => $icon,
                'is_active'    => 1,
                'sort_order'   => $sort,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('acquisition_source_id', 'clients')) {
            $this->db->query('ALTER TABLE clients DROP FOREIGN KEY fk_clients_acquisition_source');
            $this->forge->dropColumn('clients', ['acquisition_source_id', 'acquisition_detail']);
        }
        $this->forge->dropTable('acquisition_sources', true);
    }
}
