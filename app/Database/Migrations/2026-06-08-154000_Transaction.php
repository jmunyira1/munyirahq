<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Transaction extends Migration
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
            // The account money moves FROM (or INTO for income)
            'account_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            // Populated for expense transactions only.
            // Implies account_id — server pulls it from category.account_id.
            'category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            // Populated for debt_payment transactions only.
            'debt_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            // Populated for transfer transactions only.
            'transfer_to_account_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'transaction_type' => [
                'type'       => 'ENUM',
                'constraint' => ['income', 'expense', 'transfer', 'debt_payment'],
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'transaction_date' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('account_id', 'accounts', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('category_id', 'categories', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('debt_id', 'debts', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('transfer_to_account_id', 'accounts', 'id', 'CASCADE', 'RESTRICT');

        // Indexes for common filter queries
        $this->forge->addKey('transaction_type');
        $this->forge->addKey('transaction_date');

        $this->forge->createTable('transactions');

    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}
