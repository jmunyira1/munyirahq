<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Category extends Migration
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

            'parent_category_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],

            'account_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'allocation_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'default'    => 0.0000,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('parent_category_id', 'categories', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('account_id', 'accounts', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('categories');


}

    public function down()
    {
        $this->forge->dropTable('categories');
    }
}
