<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Account extends Migration
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
            'account_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'account_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Bank', 'Mobile Money', 'Cash'],
                'default'    => 'Cash',
            ],
            'color' =>[
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'   => '000000',
            ],
            'current_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('accounts');

    }

    public function down()
    {
        $this->forge->dropTable('accounts');
    }
}
