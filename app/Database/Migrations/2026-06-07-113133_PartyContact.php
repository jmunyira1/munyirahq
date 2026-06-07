<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PartyContact extends Migration
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
            'party_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true

            ],
            'contact_type' => [
                'type' => 'ENUM',
                'constraint' => [
                    'phone',
                    'email',
                ],
                'default' => 'phone'
            ],
            'contact_value' => [
                'type' => 'VARCHAR',
                'constraint' => '70'
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ],
            'updated_at' => [
                'type' => 'DATETIME'
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('party_id');
        $this->forge->addForeignKey('party_id', 'parties', 'id');
        $this->forge->createTable('partycontacts');
    }

    public function down()
    {
        $this->forge->dropTable('partycontacts');
    }
}
