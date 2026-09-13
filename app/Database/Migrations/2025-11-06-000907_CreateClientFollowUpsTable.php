<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientFollowUpsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'client_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'advisor_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'contact_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Llamada, Reunion, Correo, WhatsApp, etc.',
            ],
            'discussion_detail' => [
                'type' => 'TEXT',
                'comment' => 'El contenido de la minuta/post-it: qué se habló, qué se solicitó.',
            ],
            // Se dejará como INT para luego crear la FK, ya que calendar_activities no existe aún
            'activity_id' => [ 
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID de la actividad agendada si la minuta generó un pendiente.',
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
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('advisor_id', 'advisors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('client_follow_ups');
    }

    public function down()
    {
        $this->forge->dropTable('client_follow_ups');
    }
}