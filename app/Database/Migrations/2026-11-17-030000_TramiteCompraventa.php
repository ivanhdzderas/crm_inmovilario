<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Etapa del trámite de compraventa por interés apartado, con historial de cambios. */
class TramiteCompraventa extends Migration
{
    public function up()
    {
        $this->forge->addColumn('client_real_estate_interests', [
            'tramite_etapa'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'tramite_actualizado' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'interest_id' => ['type' => 'INT', 'unsigned' => true],
            'etapa'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'nota'        => ['type' => 'TEXT', 'null' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('interest_id');
        $this->forge->createTable('tramite_historial', true);

        // Los apartados existentes arrancan en la etapa "Apartado"
        $this->db->query("UPDATE client_real_estate_interests SET tramite_etapa = 'apartado', tramite_actualizado = COALESCE(apartado_date, updated_at) WHERE status = 'Apartado' AND tramite_etapa IS NULL");
        $this->db->query("UPDATE client_real_estate_interests SET tramite_etapa = 'entregado', tramite_actualizado = updated_at WHERE status = 'Vendido' AND tramite_etapa IS NULL");
    }

    public function down()
    {
        $this->forge->dropTable('tramite_historial', true);
        $this->forge->dropColumn('client_real_estate_interests', ['tramite_etapa', 'tramite_actualizado']);
    }
}
