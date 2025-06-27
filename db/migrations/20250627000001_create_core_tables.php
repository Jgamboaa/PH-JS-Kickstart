<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCoreTables extends AbstractMigration
{
    public function change(): void
    {
        // Tabla admin
        $this->table('admin', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('username', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('user_firstname', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('user_lastname', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('photo', 'string', ['limit' => 200, 'null' => false])
            ->addColumn('created_on', 'date', ['null' => false])
            ->addColumn('color_mode', 'string', ['limit' => 15, 'null' => false, 'default' => 'light'])
            ->addColumn('last_login', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('roles_ids', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('admin_gender', 'string', ['limit' => 30, 'null' => true])
            ->addColumn('admin_estado', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('tfa_secret', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('tfa_enabled', 'boolean', ['null' => false, 'default' => 0])
            ->addColumn('tfa_backup_codes', 'text', ['null' => true])
            ->addColumn('tfa_required', 'integer', ['null' => false, 'default' => 1])
            ->create();

        // Tabla company_data
        $this->table('company_data', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('company_name', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('company_name_short', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('app_name', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('app_version', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('developer_name', 'string', ['limit' => 100, 'null' => false])
            ->create();

        // Datos iniciales para company_data
        $this->table('company_data')
            ->insert([
                [
                    'company_name' => 'Core',
                    'company_name_short' => 'Core',
                    'app_name' => 'Core',
                    'app_version' => '1.0',
                    'developer_name' => 'Isaí Gamboa',
                ]
            ])
            ->saveData();

        // Tabla login_attempts
        $this->table('login_attempts', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('username', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('login_attempts', 'integer', ['default' => 0, 'null' => true])
            ->addColumn('last_attempt', 'datetime', ['null' => true])
            ->addIndex(['username'], ['unique' => true])
            ->create();

        // Tabla login_logs
        $this->table('login_logs', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('username', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('status', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Tabla security_logs
        $this->table('security_logs', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('event_type', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('username', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => false])
            ->addColumn('details', 'text', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->create();

        // Tabla active_sessions
        $this->table('active_sessions', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('device_token', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => false])
            ->addColumn('user_agent', 'text', ['null' => false])
            ->addColumn('last_activity', 'datetime', ['null' => false])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex(['user_id', 'device_token'], ['unique' => true])
            ->create();

        // Tabla session_logs
        $this->table('session_logs', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('log_type', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('old_value', 'text', ['null' => true])
            ->addColumn('new_value', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->create();

        // Tabla roles
        $this->table('roles', ['id' => true, 'primary_key' => 'id', 'collation' => 'utf8mb4_spanish_ci'])
            ->addColumn('nombre', 'string', ['limit' => 50, 'null' => false])
            ->create();

        // Datos iniciales para roles
        $this->table('roles')
            ->insert([
                ['id' => 1, 'nombre' => 'Administrador'],
                ['id' => 2, 'nombre' => 'Usuario']
            ])
            ->saveData();
    }
}
