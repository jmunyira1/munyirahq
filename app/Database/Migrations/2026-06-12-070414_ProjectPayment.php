<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProjectPayment extends Migration
{
    public function up()
    {

        // ── project_payments ──────────────────────────────────────────────────
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'project_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'payment_date' => ['type' => 'DATETIME'],
            'method'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'default' => null],
            'reference'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('projectpayments');
    }

    public function down()
    {
        $this->forge->dropTable('projectpayments');

    }
}
