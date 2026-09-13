<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorAdvisorToUser extends Migration
{
    public function up()
    {
        // 1. Eliminar la Foreign Key que clients tenía a advisors
        // (El nombre del índice en tu dump es clients_advisor_id_foreign)
        $this->forge->dropForeignKey('clients', 'clients_advisor_id_foreign');
        
        // 2. Eliminar la columna advisor_id de la tabla clients
        $this->forge->dropColumn('clients', 'advisor_id');
        
        // 3. Añadir la nueva columna user_id que apunta directamente a users
        $fields = [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id', // Colócala al principio
                'comment'    => 'Usuario (Asesor) responsable del prospecto.',
            ],
        ];
        $this->forge->addColumn('clients', $fields);

        // 4. Añadir la nueva Foreign Key
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('clients');

        // 5. Eliminar la tabla advisors
        $this->forge->dropTable('advisors');
    }

    public function down()
    {
        // Revertir pasos: Re-crear advisors y restaurar FKs (si fuera necesario)
        
        // Por la complejidad de re-crear advisors, solo definimos la reversión de clients
        $this->forge->dropForeignKey('clients', 'clients_user_id_foreign');
        $this->forge->dropColumn('clients', 'user_id');

        $fields = [
            'advisor_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('clients', $fields);
        // NOTA: Para un rollback completo, la tabla advisors debería re-crearse aquí.
    }
}