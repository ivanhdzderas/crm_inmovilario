<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id'    => 1,
                'name'  => 'Asesor',
            ],
            [
                'id'    => 2,
                'name'  => 'Admin',
            ],
        ];

        $this->db->table('roles')->insertBatch($data);
    }
}