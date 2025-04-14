<?php

// Cargar Dompdf
require_once("../../../vendor/autoload.php");

use Dompdf\Dompdf;

/**
 * Genera un archivo PDF con los parámetros proporcionados.
 * 
 * @param string $title Título del documento PDF
 * @param string $body Contenido HTML del cuerpo del documento
 * @param string $paperSize Tamaño del papel (letter, legal, A4, etc.)
 * @param string $orientation Orientación del papel (portrait, landscape)
 * @param string $filename Nombre del archivo PDF generado
 * @param bool $download Si es true, descargará el PDF; si es false, lo mostrará en el navegador
 * @param string $logoPath Ruta al logo, si es null usará la ruta por defecto
 * @return void
 */
function generatePDFenvio($title, $body, $paperSize = 'letter', $orientation = 'portrait', $filename = 'documento.pdf', $download = false, $logoPath = null)
{
    // Ruta predeterminada del logo
    $defaultLogoPath = "../../../images/logo.png";
    $logoPath = $logoPath ?? $defaultLogoPath;

    // Convertir el logo a base64
    $logoBase64 = imageToBase64($logoPath);

    // Reemplazar marcador de posición del logo si existe en el cuerpo
    if (strpos($body, '{{LOGO_BASE64}}') !== false)
    {
        $body = str_replace('{{LOGO_BASE64}}', $logoBase64, $body);
    }

    // Iniciar buffer de salida
    ob_start();

    // Crear estructura HTML
?>
    <!doctype html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            /* agrega tus estilos personalizados */
        </style>
    </head>

    <body>
        <?php echo $body; ?>
    </body>

    </html>
<?php

    // Capturar el HTML generado
    $HTML = ob_get_clean();

    $dompdf = new Dompdf();
    $opciones = $dompdf->getOptions();
    $opciones->set(array(
        "isRemoteEnabled" => true,
        "isPhpEnabled" => true,
        "defaultFont" => "DejaVu Sans"
    ));
    $dompdf->setOptions($opciones);
    $dompdf->loadHtml($HTML, 'UTF-8');
    $dompdf->setPaper($paperSize, $orientation);
    $dompdf->render();
    $dompdf->stream($filename, array("Attachment" => $download));
}

/**
 * Función para convertir una imagen a formato base64
 * 
 * @param string $imagePath Ruta de la imagen
 * @return string La imagen en formato base64
 */
function imageToBase64($imagePath)
{
    if (file_exists($imagePath))
    {
        return "data:image/png;base64," . base64_encode(file_get_contents($imagePath));
    }
    return '';
}
?>