<?php
// filepath: c:\laragon\www\PH-JS-Kickstart\admin\configurar_2fa.php

require_once 'includes/session.php';
require_once 'includes/functions/2fa_functions.php';
require_once dirname(__DIR__) . '/config/db_conn.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin']))
{
    header('Location: login.php');
    exit();
}

$adminId = $_SESSION['admin'];

// Obtener información del usuario
$sql = "SELECT username, tfa_enabled, tfa_secret FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$adminId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$qrCodeUrl = '';
$newSecret = '';
$backupCodes = [];
$showBackupCodes = false;

// Si el usuario aún no tiene 2FA configurado o está configurando uno nuevo
if (isset($_GET['setup']) || empty($user['tfa_secret']))
{
    $newSecret = generateTOTPSecret();
    $totp = createTOTP($newSecret, $user['username'], 'Sistema Admin');    // Obtenemos la URI de aprovisionamiento y generamos una URL de código QR usando Google Chart API
    $provisioningUri = $totp->getProvisioningUri();
    $qrCodeUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' . urlencode($provisioningUri);
}

// Procesar la activación de 2FA
if (isset($_POST['activate_2fa']))
{
    $secret = $_POST['secret'];
    $otp = $_POST['otp'];

    // Verificar que el código OTP es correcto
    if (verifyOTP($secret, $otp))
    {
        // Generar códigos de respaldo
        $backupCodes = generateBackupCodes();

        // Guardar el secreto y activar 2FA
        $sql = "UPDATE admin SET tfa_secret = ?, tfa_enabled = 1, tfa_backup_codes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$secret, json_encode($backupCodes), $adminId]);

        $message = 'Autenticación de dos factores activada correctamente.';
        $showBackupCodes = true;
    }
    else
    {
        $message = 'El código ingresado es incorrecto. Inténtalo de nuevo.';
    }
}

// Desactivar 2FA
if (isset($_POST['deactivate_2fa']))
{
    $otp = $_POST['otp'];

    // Verificar que el código OTP es correcto
    if (verifyOTP($user['tfa_secret'], $otp))
    {
        // Desactivar 2FA
        $sql = "UPDATE admin SET tfa_enabled = 0 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$adminId]);

        $message = 'Autenticación de dos factores desactivada correctamente.';
        $user['tfa_enabled'] = 0;
    }
    else
    {
        $message = 'El código ingresado es incorrecto. Inténtalo de nuevo.';
    }
}

// Regenerar códigos de respaldo
if (isset($_POST['regenerate_backup']))
{
    $otp = $_POST['otp'];

    // Verificar que el código OTP es correcto
    if (verifyOTP($user['tfa_secret'], $otp))
    {
        // Generar nuevos códigos de respaldo
        $backupCodes = generateBackupCodes();

        // Guardar los nuevos códigos
        $sql = "UPDATE admin SET tfa_backup_codes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([json_encode($backupCodes), $adminId]);

        $message = 'Códigos de respaldo regenerados correctamente.';
        $showBackupCodes = true;
    }
    else
    {
        $message = 'El código ingresado es incorrecto. Inténtalo de nuevo.';
    }
}

// Si el usuario solicita ver sus códigos de respaldo
if (isset($_POST['show_backup']))
{
    $otp = $_POST['otp'];

    // Verificar que el código OTP es correcto
    if (verifyOTP($user['tfa_secret'], $otp))
    {
        // Obtener códigos de respaldo
        $sql = "SELECT tfa_backup_codes FROM admin WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$adminId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['tfa_backup_codes']))
        {
            $backupCodes = json_decode($row['tfa_backup_codes'], true);
            $showBackupCodes = true;
        }
        else
        {
            $message = 'No hay códigos de respaldo disponibles.';
        }
    }
    else
    {
        $message = 'El código ingresado es incorrecto. Inténtalo de nuevo.';
    }
}

// No incluir el template normal para usar un estilo similar a verificar_2fa.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configuración de Autenticación de Dos Factores</title>
    <link rel="icon" href="../images/favicon.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-thin.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-solid.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-regular.css">
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-light.css">
    <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="../plugins/sweetalert2/sweetalert2.css">
    <link rel="stylesheet" href="../plugins/toastr/toastr.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.css">
</head>

<body class="hold-transition login-page">
    <div class="login-box" style="width: 720px; max-width: 100%;">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="home.php" class="h2">Configuración 2FA</a>
            </div>
            <div class="card-body">
                <!-- Estado y mensajes -->
                <div class="text-center mb-4">
                    <h4>
                        Estado actual:
                        <?php if ($user['tfa_enabled'] == 1): ?>
                            <span class="badge bg-success">Activado</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Desactivado</span>
                        <?php endif; ?>
                    </h4>
                    <p><a href="home.php" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i> Volver al inicio</a></p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo strpos($message, 'correctamente') !== false ? 'alert-success' : 'alert-danger'; ?> alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?> <?php if ($showBackupCodes): ?>
                    <div class="alert alert-warning">
                        <h5 class="text-center"><i class="icon fas fa-exclamation-triangle"></i> Códigos de respaldo</h5>
                        <p>Estos códigos te permitirán acceder a tu cuenta si pierdes acceso a tu dispositivo de autenticación. Guárdalos en un lugar seguro.</p>
                        <div class="row justify-content-center">
                            <?php foreach ($backupCodes as $code): ?>
                                <div class="col-md-3 mb-2 text-center">
                                    <code class="p-2 d-block"><?php echo $code; ?></code>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?> <?php if ($user['tfa_enabled'] == 0): ?>
                    <!-- Configuración inicial de 2FA -->
                    <div class="text-center mb-4">
                        <h5>Configurar autenticación de dos factores</h5>
                        <p>Sigue estos pasos para activar la autenticación de dos factores:</p>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <ol>
                                <li>Descarga una aplicación de autenticación como Google Authenticator, Microsoft Authenticator o Authy en tu dispositivo móvil.</li>
                                <li>Escanea el código QR con la aplicación o ingresa la clave secreta manualmente.</li>
                                <li>Ingresa el código de verificación generado por la aplicación para completar la configuración.</li>
                            </ol>

                            <div class="bg-light p-2 mb-3 text-center">
                                <p class="mb-1">Clave manual:</p>
                                <code class="d-block p-2"><?php echo $newSecret; ?></code>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="text-center">
                                <div class="mb-3">
                                    <img src="<?php echo $qrCodeUrl; ?>" alt="Código QR" class="img-fluid">
                                </div>

                                <form method="post">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" name="otp" class="form-control text-center" required autocomplete="off" placeholder="Ingresa el código de 6 dígitos" maxlength="6">
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-shield-alt"></span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="secret" value="<?php echo $newSecret; ?>">
                                        </div>
                                    </div>
                                    <button type="submit" name="activate_2fa" class="btn btn-primary btn-block">Activar 2FA</button>
                                </form>
                            </div>
                        </div>
                    </div> <?php else: ?>
                    <!-- Gestión de 2FA ya activado -->
                    <div class="text-center mb-4">
                        <h5>Gestión de autenticación de dos factores</h5>
                        <p>Tu autenticación de dos factores está <span class="badge bg-success">Activada</span></p>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-5">
                            <div class="card mb-4">
                                <div class="card-header text-center bg-danger">
                                    <h5 class="m-0">Desactivar 2FA</h5>
                                </div>
                                <div class="card-body">
                                    <p>Para desactivar la autenticación de dos factores, ingresa un código de verificación:</p>
                                    <form method="post">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" name="otp" class="form-control text-center" required autocomplete="off" placeholder="Código de 6 dígitos" maxlength="6">
                                                <div class="input-group-append">
                                                    <div class="input-group-text">
                                                        <span class="fas fa-shield-alt"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" name="deactivate_2fa" class="btn btn-danger btn-block">Desactivar 2FA</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header text-center bg-info">
                                    <h5 class="m-0">Códigos de respaldo</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-center">Los códigos de respaldo te permiten acceder a tu cuenta si pierdes acceso a tu dispositivo.</p>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <form method="post" class="mb-3">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" name="otp" class="form-control text-center" required autocomplete="off" placeholder="Código de 6 dígitos" maxlength="6">
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                <span class="fas fa-key"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" name="show_backup" class="btn btn-info btn-block">Ver códigos</button>
                                            </form>
                                        </div>

                                        <div class="col-md-6">
                                            <form method="post">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" name="otp" class="form-control text-center" required autocomplete="off" placeholder="Código de 6 dígitos" maxlength="6">
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                <span class="fas fa-sync-alt"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" name="regenerate_backup" class="btn btn-warning btn-block">Regenerar códigos</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.js"></script>
    <!-- SweetAlert2 -->
    <script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
</body>

</html>