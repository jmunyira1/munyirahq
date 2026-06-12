<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProjectCost extends Migration
{
    public function up()
    {

        // ── project_costs ─────────────────────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'project_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'incurred_on' => ['type' => 'DATE'],
            'notes'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'default' => null],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('projectcosts');
    }

    public function down()
    {
        $this->forge->dropTable('projectcosts');

    }
}
