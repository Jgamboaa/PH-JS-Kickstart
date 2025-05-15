<?php
require_once 'includes/session.php';

// Simplemente redirigir a home con un parámetro para abrir el modal de 2FA
header('Location: home.php?open_2fa_modal=1');
exit();
