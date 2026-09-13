<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCancellationFieldsToClientsAndInterests extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('deactivation_reason_id', 'clients')) {
            $this->forge->addColumn('clients', [
                'deactivation_reason_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'deactivation_notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'deactivated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }

        if (! $this->db->fieldExists('cancellation_reason_id', 'client_real_estate_interests')) {
            $this->forge->addColumn('client_real_estate_interests', [
                'cancellation_reason_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'cancellation_notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'cancelled_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'cancelled_by_user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]);
        }

        $clientFk = $this->db->query("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'clients'
              AND COLUMN_NAME = 'deactivation_reason_id'
              AND REFERENCED_TABLE_NAME = 'cancellation_reasons'
            LIMIT 1
        ")->getRowArray();

        if (empty($clientFk)) {
            $this->db->query('ALTER TABLE clients ADD CONSTRAINT fk_clients_deactivation_reason FOREIGN KEY (deactivation_reason_id) REFERENCES cancellation_reasons(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }

        $interestFk = $this->db->query("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'client_real_estate_interests'
              AND COLUMN_NAME = 'cancellation_reason_id'
              AND REFERENCED_TABLE_NAME = 'cancellation_reasons'
            LIMIT 1
        ")->getRowArray();

        if (empty($interestFk)) {
            $this->db->query('ALTER TABLE client_real_estate_interests ADD CONSTRAINT fk_interests_cancellation_reason FOREIGN KEY (cancellation_reason_id) REFERENCES cancellation_reasons(id) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }

    public function down()
    {
        $this->db->query('ALTER TABLE clients DROP FOREIGN KEY fk_clients_deactivation_reason');
        $this->db->query('ALTER TABLE client_real_estate_interests DROP FOREIGN KEY fk_interests_cancellation_reason');

        if ($this->db->fieldExists('deactivation_reason_id', 'clients')) {
            $this->forge->dropColumn('clients', ['deactivation_reason_id', 'deactivation_notes', 'deactivated_at']);
        }

        if ($this->db->fieldExists('cancellation_reason_id', 'client_real_estate_interests')) {
            $this->forge->dropColumn('client_real_estate_interests', [
                'cancellation_reason_id',
                'cancellation_notes',
                'cancelled_at',
                'cancelled_by_user_id',
            ]);
        }
    }
}
