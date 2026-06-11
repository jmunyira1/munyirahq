<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBudgetItems extends Migration
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
            // Must be a subcategory (has parent_category_id) — enforced in controller
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'item_type' => [
                'type'       => 'ENUM',
                'constraint' => ['one_off', 'recurring'],
                'default'    => 'one_off',
            ],
            // Only populated when item_type = recurring
            'recurrence' => [
                'type'       => 'ENUM',
                'constraint' => ['weekly', 'monthly', 'yearly'],
                'null'       => true,
                'default'    => null,
            ],
            // Populated when fulfilled — links back to the transaction that paid for it
            'transaction_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'fulfilled'],
                'default'    => 'pending',
            ],
            'due_date' => [
                'type'    => 'DATE',
                'null'    => true,
                'default' => null,
            ],
            'notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('category_id',    'categories',   'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addKey('status');
        $this->forge->createTable('budgetitems');
    }

    public function down()
    {
        $this->forge->dropTable('budgetitems');
    }
}