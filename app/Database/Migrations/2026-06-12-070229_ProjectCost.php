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
            'quantity'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 1],
            'unit_price'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'incurred_on' => ['type' => 'DATE'],
            'notes'       => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'default' => null],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('projectcosts');
        $this->db->query('ALTER TABLE projectcosts MODIFY amount DECIMAL(15,2) GENERATED ALWAYS AS (unit_price * quantity) STORED');


    }

    public function down()
    {
        $this->forge->dropTable('projectcosts');

    }
}
