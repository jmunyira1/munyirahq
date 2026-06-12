<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjects extends Migration
{
    public function up()
    {
        // ── projects ─────────────────────────────────────────────────────────
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'party_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'contracted_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['active', 'completed'], 'default' => 'active'],
            'due_date' => ['type' => 'DATE', 'null' => true, 'default' => null],
            // Populated once: the income transaction auto-created on completion
            'transaction_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'default' => null],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('party_id',       'parties',      'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addKey('status');
        $this->forge->createTable('projects');


    }

    public function down()
    {
        $this->forge->dropTable('projects');
    }
}