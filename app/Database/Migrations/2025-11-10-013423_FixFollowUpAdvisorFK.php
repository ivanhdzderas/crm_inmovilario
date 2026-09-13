<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixFollowUpAdvisorFK extends Migration
{
    public function up()
    {
        // Deshabilitar la verificación de FK temporalmente
        $this->db->disableForeignKeyChecks();

        // 1. Eliminar la clave foránea ROTA que apunta a la tabla inexistente 'advisors'
        $this->db->query("
            ALTER TABLE `client_follow_ups` 
            DROP FOREIGN KEY `client_follow_ups_advisor_id_foreign`
        ");

        // 2. Agregar la clave foránea CORRECTA que apunta a la tabla 'users'
        // NOTA: Asumo que la columna `advisor_id` ya es INT(11) UNSIGNED.
        $this->db->query("
            ALTER TABLE `client_follow_ups` 
            ADD CONSTRAINT `fk_client_follow_ups_user_advisor` 
            FOREIGN KEY (`advisor_id`) 
            REFERENCES `users` (`id`) 
            ON DELETE CASCADE ON UPDATE CASCADE
        ");

        // Habilitar la verificación de FK
        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        // En caso de querer revertir la migración, apuntamos a la tabla users y luego la eliminamos.
        $this->db->disableForeignKeyChecks();
        
        $this->db->query("
            ALTER TABLE `client_follow_ups` 
            DROP FOREIGN KEY `fk_client_follow_ups_user_advisor`
        ");

        // Opcional: Si quieres restaurar el FK roto (aunque no es útil), lo dejarías aquí.
        // Pero lo mejor es dejarlo roto para recordar que la estructura cambió.

        $this->db->enableForeignKeyChecks();
    }
}