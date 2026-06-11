<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Debt extends Migration
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
            'party_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'debt_type' => [
                'type'       => 'ENUM',
                'constraint' => ['owed_by_me', 'owed_to_me'],
                'default'    => 'owed_by_me',
            ],
            'total_principal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'current_balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('party_id', 'parties', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addKey('status'); // frequent filter: WHERE status = 0
        $this->forge->createTable('debts');}

    public function down()
    {
        $this->forge->dropTable('debts');

    }
}
