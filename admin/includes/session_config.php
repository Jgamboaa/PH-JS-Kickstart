<?php
if (session_status() === PHP_SESSION_NONE)
{
    session_name('admin_session');

    // Configuración de la sesión - Aumentada a 1 mes (30 días)
    $oneMonthInSeconds = 30 * 24 * 60 * 60;
    ini_set('session.gc_maxlifetime', $oneMonthInSeconds);
    ini_set('session.cookie_lifetime', $oneMonthInSeconds); // Añadido explícitamente
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);

    // Configuración de cookies de sesión
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isDev = (bool) preg_match('/(^localhost$|\.dev$|\.test$)/i', $host);

    $params = [
        'domain'   => '',
        'lifetime' => $oneMonthInSeconds,
    ];

    if (! $isDev)
    {
        $params['secure']   = true;
        $params['httponly'] = true;
        $params['samesite'] = 'Lax';
    }

    session_set_cookie_params($params);

    session_start();

    // Si la sesión está iniciando, establecer también una cookie persistente
    if (!isset($_COOKIE['persistent_session']) && isset($_SESSION['admin']))
    {
        setcookie(
            'persistent_session',
            $_SESSION['admin'],
            [
                'expires' => time() + $oneMonthInSeconds,
                'path' => '/',
                'domain' => '',
                'secure' => !$isDev,
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }
}
