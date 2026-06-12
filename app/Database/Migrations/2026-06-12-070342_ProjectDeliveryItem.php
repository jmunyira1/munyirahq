<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProjectDeliveryItem extends Migration
{
    public function up()
    {

        // ── project_delivery_items ────────────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'project_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'quantity'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 1],
            'unit_price'  => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            // total_price is a generated column — computed by the DB, never written by app
            'total_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                // CI4 forge doesn't support GENERATED natively — we use a raw query after createTable
            ],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('projectdeliveryitems');

        // Replace total_price with a generated column
        $this->db->query('ALTER TABLE project_delivery_items 
            MODIFY total_price DECIMAL(15,2) GENERATED ALWAYS AS (unit_price * quantity) STORED');

    }

    public function down()
    {
        $this->forge->dropTable('projectdeliveryitems');
    }
}
