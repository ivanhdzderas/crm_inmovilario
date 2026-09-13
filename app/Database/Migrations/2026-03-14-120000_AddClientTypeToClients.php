<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClientTypeToClients extends Migration
{
    public function up()
    {
        $this->forge->addColumn('clients', [
            'client_type' => [
                'type'       => 'ENUM',
                'constraint' => ['comprador', 'vendedor'],
                'default'    => 'comprador',
                'after'      => 'full_name',
                'comment'    => 'Tipo de cliente: comprador (busca casa) o vendedor (propietario)'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('clients', 'client_type');
    }
}