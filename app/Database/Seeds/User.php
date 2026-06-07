<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class User extends Seeder
{
    public function run()
    {
        $user = [
            'username' => 'Munyira',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $credentials = [
            'user_id' => 1,
            'type' => 'email_password',
            'secret' => 'munyira@munyira.co.ke',
            'secret2' => '$2y$12$jtJbl2.gBz3OeJg7JahWQu2fCw1LNGon16IS3.TkDBbC189cM8s7G',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')];

        // Insert a single row using the Query Builder
        $this->db->table('users')->insert($user);
        $this->db->table('auth_identities')->insert($credentials);
    }
}
