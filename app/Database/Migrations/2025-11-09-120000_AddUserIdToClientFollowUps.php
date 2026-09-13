<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToClientFollowUps extends Migration
{
    public function up()
    {
        // 1) Añadir la columna user_id (nullable) para compatibilidad
        $fields = [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'advisor_id',
            ],
        ];

        $this->forge->addColumn('client_follow_ups', $fields);

        // 2) Copiar valores existentes desde advisor_id a user_id
        $db = \Config\Database::connect();
        // Use a safe update: sólo copia donde user_id es NULL
        $db->query('UPDATE `client_follow_ups` SET `user_id` = `advisor_id` WHERE `user_id` IS NULL');

        // 3) Añadir foreign key a users(id) si la tabla users existe
        // Si ya existe una fk, Forge manejará el error.
        try {
            $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        } catch (\Exception $e) {
            // No queremos romper la migración si la FK no puede crearse en este entorno.
            log_message('warning', '[migration] could not add FK user_id -> users.id: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Remover fk si existe y eliminar columna
        // Forge no tiene removeForeignKey por nombre fácilmente; simplemente eliminar columna
        $this->forge->dropColumn('client_follow_ups', 'user_id');
    }
}
