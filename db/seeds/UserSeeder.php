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
        // Buscar datos del administrador desde el archivo temporal
        $adminData = $this->getAdminData();

        if ($adminData)
        {
            // Usar los datos del instalador
            $this->table('admin')
                ->insert([
                    'username' => $adminData['email'],
                    'password' => $adminData['password'],
                    'user_firstname' => $adminData['firstname'],
                    'user_lastname' => $adminData['lastname'],
                    'photo' => '',
                    'created_on' => $adminData['created_on'],
                    'roles_ids' => '1',
                    'admin_gender' => $adminData['gender'],
                    'admin_estado' => 0,
                    'tfa_enabled' => 0,
                    'tfa_required' => 0
                ])
                ->saveData();

            echo "Se ha creado el usuario administrador con el correo: " . $adminData['email'] . "\n";
        }
        else
        {
            // Usar los datos predeterminados si no hay datos del instalador
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
                    'admin_estado' => 0,
                    'tfa_enabled' => 0,
                    'tfa_required' => 0
                ])
                ->saveData();

            echo "Se ha creado el usuario administrador predeterminado (admin@admin.com)\n";
        }
    }

    /**
     * Obtiene los datos del administrador desde el archivo temporal
     */
    private function getAdminData(): ?array
    {
        $adminConfigFile = dirname(dirname(__DIR__)) . '/tmp/admin_config.json';

        if (file_exists($adminConfigFile))
        {
            $adminData = json_decode(file_get_contents($adminConfigFile), true);
            return $adminData;
        }

        return null;
    }
}
