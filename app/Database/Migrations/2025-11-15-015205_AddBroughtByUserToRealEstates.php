<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBroughtByUserToRealEstates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('real_estates', [
            'brought_by_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Usuario/asesor que trajo/consiguió este inmueble',
                'after' => 'created_by_user_id'
            ]
        ]);
        
        // Agregar foreign key
        $this->forge->addForeignKey('brought_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL', 'real_estates');
    }

    public function down()
    {
        $this->forge->dropForeignKey('real_estates', 'real_estates_brought_by_user_id_foreign');
        $this->forge->dropColumn('real_estates', 'brought_by_user_id');
    }
}
