<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommissionFieldsToRealEstates extends Migration
{
    public function up()
    {
        $this->forge->addColumn('real_estates', [
            // Comisión para quien vende
            'sales_commission_type' => [
                'type' => 'ENUM',
                'constraint' => ['percentage', 'fixed_amount'],
                'default' => 'percentage',
                'comment' => 'Tipo de comisión para quien vende (porcentaje o monto fijo)',
                'after' => 'brought_by_user_id'
            ],
            'sales_commission_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'comment' => 'Porcentaje de comisión para quien vende',
                'after' => 'sales_commission_type'
            ],
            'sales_commission_fixed_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'comment' => 'Monto fijo de comisión para quien vende',
                'after' => 'sales_commission_percentage'
            ],
            
            // Comisión para quien trajo el inmueble
            'brought_commission_type' => [
                'type' => 'ENUM',
                'constraint' => ['percentage', 'fixed_amount'],
                'default' => 'percentage',
                'comment' => 'Tipo de comisión para quien trajo el inmueble',
                'after' => 'sales_commission_fixed_amount'
            ],
            'brought_commission_percentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'comment' => 'Porcentaje de comisión para quien trajo el inmueble',
                'after' => 'brought_commission_type'
            ],
            'brought_commission_fixed_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'null' => true,
                'comment' => 'Monto fijo de comisión para quien trajo el inmueble',
                'after' => 'brought_commission_percentage'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('real_estates', [
            'sales_commission_type',
            'sales_commission_percentage', 
            'sales_commission_fixed_amount',
            'brought_commission_type',
            'brought_commission_percentage',
            'brought_commission_fixed_amount'
        ]);
    }
}
