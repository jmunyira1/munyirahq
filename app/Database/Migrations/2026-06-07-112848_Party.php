<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Party extends Migration
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
            'is_person' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'default' => null,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'gender' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
                'default' => null,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('name');
        $this->forge->addKey('is_person');
        $this->forge->createTable('parties');
    }

    public function down()
    {
        $this->forge->dropTable('parties');
    }
}
