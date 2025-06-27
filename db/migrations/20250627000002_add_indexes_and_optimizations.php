<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIndexesAndOptimizations extends AbstractMigration
{
    public function change(): void
    {
        // Índices para la tabla admin
        $this->table('admin')
            ->addIndex(['username'], ['name' => 'idx_username', 'unique' => true])
            ->save();

        // Índices para la tabla login_logs
        $this->table('login_logs')
            ->addIndex(['username'], ['name' => 'idx_login_logs_username'])
            ->addIndex(['created_at'], ['name' => 'idx_login_logs_created_at'])
            ->save();

        // Índices para la tabla security_logs
        $this->table('security_logs')
            ->addIndex(['username'], ['name' => 'idx_security_logs_username'])
            ->addIndex(['event_type'], ['name' => 'idx_security_logs_event_type'])
            ->addIndex(['created_at'], ['name' => 'idx_security_logs_created_at'])
            ->save();

        // Índices para la tabla active_sessions
        $this->table('active_sessions')
            ->addIndex(['user_id'], ['name' => 'idx_active_sessions_user_id'])
            ->addIndex(['last_activity'], ['name' => 'idx_active_sessions_last_activity'])
            ->save();

        // Índices para la tabla session_logs
        $this->table('session_logs')
            ->addIndex(['user_id'], ['name' => 'idx_session_logs_user_id'])
            ->addIndex(['log_type'], ['name' => 'idx_session_logs_log_type'])
            ->addIndex(['created_at'], ['name' => 'idx_session_logs_created_at'])
            ->save();
    }
}
