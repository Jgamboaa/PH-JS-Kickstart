<?php
// filepath: c:\laragon\www\PH-JS-Kickstart\admin\verificar_2fa.php

require_once 'includes/session_config.php';
require_once dirname(__DIR__) . '/config/db_conn.php';
require_once 'includes/functions/2fa_functions.php';

// Verificar que existe un pendiente de 2FA
if (!isset($_SESSION['2fa_pending']) || !isset($_SESSION['2fa_user_id']))
{
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['2fa_user_id'];
$error = '';
$useBackupCode = isset($_GET['backup']);

// Obtener información del usuario
$sql = "SELECT username FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user)
{
    // Si no se encuentra el usuario, redirigir
    unset($_SESSION['2fa_pending']);
    unset($_SESSION['2fa_user_id']);
    header('Location: index.php');
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['verify_code']))
    {
        $code = trim($_POST['code']);

        if (empty($code))
        {
            $error = 'Por favor ingresa un código.';
        }
        else
        {
            if ($useBackupCode)
            {
                // Verificar código de respaldo
                if (verifyBackupCode($userId, $code))
                {
                    // Código válido, completar inicio de sesión
                    completeLogin($userId);
                }
                else
                {
                    $error = 'Código de respaldo inválido.';
                }
            }
            else
            {
                // Verificar código TOTP
                $sql = "SELECT tfa_secret FROM admin WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$userId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row && verifyOTP($row['tfa_secret'], $code))
                {
                    // Código válido, completar inicio de sesión
                    completeLogin($userId);
                }
                else
                {
                    $error = 'Código inválido.';
                }
            }
        }
    }
    else if (isset($_POST['cancel']))
    {
        // Cancelar inicio de sesión
        unset($_SESSION['2fa_pending']);
        unset($_SESSION['2fa_user_id']);
        header('Location: index.php');
        exit();
    }
}

/**
 * Completa el proceso de inicio de sesión
 */
function completeLogin($userId)
{
    global $conn;

    // Obtener información del usuario
    $sql = "SELECT * FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Actualizar last_login
    $sql = "UPDATE admin SET last_login = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);

    // Eliminar estado pendiente 2FA
    unset($_SESSION['2fa_pending']);
    unset($_SESSION['2fa_user_id']);

    // Establecer sesión normal
    $_SESSION['admin'] = $userId;
    $_SESSION['last_activity'] = time();

    // Redireccionar a home
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de dos factores</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="../plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="../plugins/toastr/toastr.min.css">
    <link rel="stylesheet" href="../plugins/bootstrap/js/bootstrap.bundle.min.js">
    <!-- Theme style -->
    <link rel="stylesheet" href="../plugins/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="/" class="h2">Verificación 2FA</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">
                    <?php if ($useBackupCode): ?>
                        Ingresa un código de respaldo para continuar
                    <?php else: ?>
                        Ingresa el código de verificación de tu aplicación de autenticación
                    <?php endif; ?>
                </p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="code" placeholder="<?php echo $useBackupCode ? 'Código de respaldo' : 'Código de 6 dígitos'; ?>" autocomplete="off" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-shield-alt"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="verify_code" class="btn btn-primary btn-block">Verificar</button>
                        </div>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <?php if ($useBackupCode): ?>
                        <p><a href="verificar_2fa.php">Usar código de aplicación en su lugar</a></p>
                    <?php else: ?>
                        <p><a href="verificar_2fa.php?backup=1">Usar código de respaldo</a></p>
                    <?php endif; ?>

                    <form method="post" class="mt-3">
                        <button type="submit" name="cancel" class="btn btn-default btn-sm">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../plugins/dist/js/adminlte.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
</body>

</html>