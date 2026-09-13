<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyConfig extends Migration
{
    public function up()
    {
        $this->db->table('configurations')->insertBatch([
            [
                'config_key'   => 'company_name',
                'config_value' => 'Tu Inmobiliaria',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'config_key'   => 'company_website',
                'config_value' => 'www.tonyinmobiliaria.com',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function down()
    {
        $this->db->table('configurations')
             ->whereIn('config_key', ['company_name', 'company_website'])
             ->delete();
    }
}
