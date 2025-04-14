<?php
require_once __DIR__ . '/../config/env_reader.php';
$mail_support = env('MAIL_SUPPORT');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Error 403 - Acceso prohibido</title>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
        crossorigin="anonymous" />
    <link rel="icon" href="/images/favicon.png">
</head>

<body class="bg-light">

    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-8 text-center">
                <p style="color: #2b3948;">
                    <img src="/images/logo.png" width="20%" />
                </p>
                <h2 class="mb-4" style="color: #2b3948;">
                    <strong>403</strong> - Acceso prohibido
                </h2>
                <p style="color: #2b3948;">
                    Lo sentimos, no tienes permiso para acceder a este recurso.
                    <br>
                    Por favor comunícate con nuestro equipo de soporte al correo <a style="color: #2b3948;" href="mailto:<?php echo $mail_support; ?>"><?php echo $mail_support; ?></a>.
                </p>
            </div>
        </div>
    </div>
    <!-- Bootstrap JavaScript Libraries -->
    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
</body>

</html>