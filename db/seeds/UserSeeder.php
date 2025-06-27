<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Insertar el usuario administrador
        $this->table('admin')
            ->insert([
                'username' => 'admin@admin.com',
                'password' => '$2y$10$RT8BkA4YVF3e1PKyY4ZBlOk1B7wHD8gBiQAleFSPEjTEE98yJiXzm', // Contraseña: admin
                'user_firstname' => 'Usuario',
                'user_lastname' => 'Administrador',
                'photo' => '',
                'created_on' => date('Y-m-d'),
                'roles_ids' => '1',
                'admin_gender' => '0',
                'admin_estado' => 1,
                'tfa_enabled' => 0,
                'tfa_required' => 0
            ])
            ->saveData();
    }
}
