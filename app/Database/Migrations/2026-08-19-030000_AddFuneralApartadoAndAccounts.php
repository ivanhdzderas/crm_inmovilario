<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFuneralApartadoAndAccounts extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('client_funeral_interests')) {
            $fields = [
                'apartado_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'null'       => true,
                    'after'      => 'status',
                ],
                'apartado_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'apartado_amount',
                ],
                'apartado_receipt' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'apartado_date',
                ],
                'cancellation_reason_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'apartado_receipt',
                ],
                'cancellation_notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'cancellation_reason_id',
                ],
                'cancelled_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'cancellation_notes',
                ],
                'cancelled_by_user_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'cancelled_at',
                ],
            ];
            $this->forge->addColumn('client_funeral_interests', $fields);

            if ($this->db->tableExists('cancellation_reasons')) {
                $this->db->query('ALTER TABLE client_funeral_interests ADD CONSTRAINT fk_cfi_cancel_reason FOREIGN KEY (cancellation_reason_id) REFERENCES cancellation_reasons(id) ON DELETE SET NULL ON UPDATE CASCADE');
            }

            $this->db->query("ALTER TABLE client_funeral_interests MODIFY status ENUM('Interesado','En análisis','Definido','Apartado','Vendido','Cancelado') NOT NULL DEFAULT 'Interesado'");
        }

        if ($this->db->tableExists('client_accounts')) {
            $this->forge->addColumn('client_accounts', [
                'account_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['inmueble', 'funerario'],
                    'default'    => 'inmueble',
                    'after'      => 'client_id',
                ],
                'funeral_product_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'real_estate_interest_id',
                ],
                'funeral_interest_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'funeral_product_id',
                ],
            ]);

            $this->db->query('UPDATE client_accounts SET account_type = "inmueble" WHERE account_type IS NULL OR account_type = ""');

            // Permitir cuentas funerarias sin inmueble
            $this->db->query('ALTER TABLE client_accounts DROP FOREIGN KEY client_accounts_real_estate_id_foreign');
            $this->db->query('ALTER TABLE client_accounts DROP FOREIGN KEY client_accounts_real_estate_interest_id_foreign');
            $this->db->query('ALTER TABLE client_accounts MODIFY real_estate_id INT UNSIGNED NULL');
            $this->db->query('ALTER TABLE client_accounts MODIFY real_estate_interest_id INT UNSIGNED NULL');
            $this->db->query('ALTER TABLE client_accounts ADD CONSTRAINT client_accounts_real_estate_id_foreign FOREIGN KEY (real_estate_id) REFERENCES real_estates(id) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE client_accounts ADD CONSTRAINT client_accounts_real_estate_interest_id_foreign FOREIGN KEY (real_estate_interest_id) REFERENCES client_real_estate_interests(id) ON DELETE CASCADE ON UPDATE CASCADE');

            $this->db->query('ALTER TABLE client_accounts DROP INDEX client_id_real_estate_id');
            $this->db->query('ALTER TABLE client_accounts ADD UNIQUE KEY uq_client_real_estate (client_id, real_estate_id)');
            $this->db->query('ALTER TABLE client_accounts ADD UNIQUE KEY uq_client_funeral_product (client_id, funeral_product_id)');

            if ($this->db->tableExists('funeral_products')) {
                $this->db->query('ALTER TABLE client_accounts ADD CONSTRAINT client_accounts_funeral_product_id_foreign FOREIGN KEY (funeral_product_id) REFERENCES funeral_products(id) ON DELETE CASCADE ON UPDATE CASCADE');
            }
            if ($this->db->tableExists('client_funeral_interests')) {
                $this->db->query('ALTER TABLE client_accounts ADD CONSTRAINT client_accounts_funeral_interest_id_foreign FOREIGN KEY (funeral_interest_id) REFERENCES client_funeral_interests(id) ON DELETE CASCADE ON UPDATE CASCADE');
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('client_accounts') && $this->db->fieldExists('account_type', 'client_accounts')) {
            $this->db->query('DELETE FROM client_accounts WHERE account_type = "funerario"');

            $this->db->query('ALTER TABLE client_accounts DROP FOREIGN KEY client_accounts_funeral_interest_id_foreign');
            $this->db->query('ALTER TABLE client_accounts DROP FOREIGN KEY client_accounts_funeral_product_id_foreign');
            $this->db->query('ALTER TABLE client_accounts DROP INDEX uq_client_funeral_product');
            $this->db->query('ALTER TABLE client_accounts DROP INDEX uq_client_real_estate');

            $this->forge->dropColumn('client_accounts', ['account_type', 'funeral_product_id', 'funeral_interest_id']);

            $this->db->query('ALTER TABLE client_accounts DROP FOREIGN KEY client_accounts_real_estate_id_foreign');
            $this->db->query('ALTER TABLE client_accounts DROP FOREIGN KEY client_accounts_real_estate_interest_id_foreign');
            $this->db->query('ALTER TABLE client_accounts MODIFY real_estate_id INT UNSIGNED NOT NULL');
            $this->db->query('ALTER TABLE client_accounts MODIFY real_estate_interest_id INT UNSIGNED NOT NULL');
            $this->db->query('ALTER TABLE client_accounts ADD CONSTRAINT client_accounts_real_estate_id_foreign FOREIGN KEY (real_estate_id) REFERENCES real_estates(id) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE client_accounts ADD CONSTRAINT client_accounts_real_estate_interest_id_foreign FOREIGN KEY (real_estate_interest_id) REFERENCES client_real_estate_interests(id) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE client_accounts ADD UNIQUE KEY client_id_real_estate_id (client_id, real_estate_id)');
        }

        if ($this->db->tableExists('client_funeral_interests')) {
            if ($this->db->fieldExists('cancellation_reason_id', 'client_funeral_interests')) {
                $this->db->query('ALTER TABLE client_funeral_interests DROP FOREIGN KEY fk_cfi_cancel_reason');
            }
            $this->forge->dropColumn('client_funeral_interests', [
                'apartado_amount', 'apartado_date', 'apartado_receipt',
                'cancellation_reason_id', 'cancellation_notes', 'cancelled_at', 'cancelled_by_user_id',
            ]);
            $this->db->query("ALTER TABLE client_funeral_interests MODIFY status ENUM('Interesado','En análisis','Definido','Cancelado') NOT NULL DEFAULT 'Interesado'");
        }
    }
}
