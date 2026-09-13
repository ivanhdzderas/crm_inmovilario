<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsFuneralProspectToClients extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('is_funeral_prospect', 'clients')) {
            return;
        }

        $this->forge->addColumn('clients', [
            'is_funeral_prospect' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'client_type',
                'comment'    => 'Prospecto/comprador del flujo funerario',
            ],
        ]);

        if ($this->db->tableExists('client_funeral_interests')) {
            $this->db->query('
                UPDATE clients c
                SET c.is_funeral_prospect = 1
                WHERE EXISTS (
                    SELECT 1 FROM client_funeral_interests cfi WHERE cfi.client_id = c.id
                )
            ');
        }

        if ($this->db->tableExists('client_accounts') && $this->db->fieldExists('account_type', 'client_accounts')) {
            $this->db->query("
                UPDATE clients c
                SET c.is_funeral_prospect = 1
                WHERE EXISTS (
                    SELECT 1 FROM client_accounts ca
                    WHERE ca.client_id = c.id AND ca.account_type = 'funerario'
                )
            ");
        }

        if ($this->db->tableExists('client_real_estate_interests')) {
            $this->db->query("
                UPDATE clients c
                SET c.is_funeral_prospect = 1
                WHERE c.is_funeral_prospect = 0
                  AND c.status IN ('Nuevo', 'Seguimiento')
                  AND (c.credit_amount IS NULL OR c.credit_amount = 0)
                  AND c.zone_id IS NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM client_real_estate_interests cri WHERE cri.client_id = c.id
                  )
            ");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_funeral_prospect', 'clients')) {
            $this->forge->dropColumn('clients', 'is_funeral_prospect');
        }
    }
}
