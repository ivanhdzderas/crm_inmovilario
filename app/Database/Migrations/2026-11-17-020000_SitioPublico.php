<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sitio público: foto de los paquetes funerarios y datos configurables del sitio
 * (nombre, contacto opcional y asesor que recibe los prospectos que llegan por la web).
 */
class SitioPublico extends Migration
{
    public function up()
    {
        $this->forge->addColumn('funeral_products', [
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'description'],
        ]);

        $ahora = date('Y-m-d H:i:s');
        $valores = [
            'site_name'          => 'Pacific Inmobiliaria',
            'site_tagline'       => 'Tepic · Bahía · Pacífico',
            'site_whatsapp'      => '',
            'site_email'         => '',
            'site_address'       => '',
            'site_leads_user_id' => '',
        ];
        foreach ($valores as $clave => $valor) {
            $existe = $this->db->table('configurations')->where('config_key', $clave)->countAllResults();
            if (! $existe) {
                $this->db->table('configurations')->insert([
                    'config_key' => $clave, 'config_value' => $valor, 'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropColumn('funeral_products', 'image_path');
        $this->db->table('configurations')->whereIn('config_key', [
            'site_name', 'site_tagline', 'site_whatsapp', 'site_email', 'site_address', 'site_leads_user_id',
        ])->delete();
    }
}
