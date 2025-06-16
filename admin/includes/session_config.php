<?php
if (session_status() === PHP_SESSION_NONE)
{
    session_name('admin_session');

    // Configuración de la sesión
    ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);

    // Configuración de cookies de sesión
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isDev = (bool) preg_match('/(^localhost$|\.dev$|\.test$)/i', $host);

    $params = [
        'domain'   => '',
        'lifetime' => 30 * 24 * 60 * 60,
    ];

    if (! $isDev)
    {
        $params['secure']   = true;
        $params['httponly'] = true;
        $params['samesite'] = 'Lax';
    }

    session_start();
}
