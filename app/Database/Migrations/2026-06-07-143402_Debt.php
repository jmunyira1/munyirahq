<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Debt extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'party_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'amount' => [
                'type' => 'DECIMAL', // Changed to DECIMAL for currency accuracy
                'constraint' => '10,2',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('party_id', 'parties', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('debts');
    }

    public function down()
    {
        $this->forge->dropTable('debts');

    }
}
