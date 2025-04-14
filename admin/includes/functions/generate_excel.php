<?php
require_once __DIR__ . '/../../../composer/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Genera un archivo Excel a partir de un query SQL
 * 
 * @param mysqli $conn Conexión a la base de datos
 * @param string $query Consulta SQL a ejecutar
 * @param string $filename Nombre del archivo Excel (sin extensión)
 * @return void
 */
function generateExcelFromQuery($conn, $query, $filename)
{
    // Ejecutar la consulta
    $result = $conn->query($query);

    if (!$result)
    {
        die("Error en la consulta: " . $conn->error);
    }

    // Crear nuevo objeto Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Verificar si hay datos
    if ($result->num_rows == 0)
    {
        // No hay datos, establecer mensaje
        $sheet->setCellValue('A1', 'No hay datos disponibles para la consulta.');
    }
    else
    {
        // Obtener la información sobre las columnas
        $row = $result->fetch_assoc();
        $headers = array_keys($row);

        // Volver a posicionar el puntero al principio
        $result->data_seek(0);

        // Escribir encabezados
        foreach ($headers as $idx => $header)
        {
            $col = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue($col . '1', $header);
        }

        // Dar formato a los encabezados
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);

        // Llenar datos
        $rowNumber = 2;
        while ($row = $result->fetch_assoc())
        {
            foreach ($headers as $idx => $header)
            {
                $col = Coordinate::stringFromColumnIndex($idx + 1);
                $sheet->setCellValue($col . $rowNumber, $row[$header]);
            }
            $rowNumber++;
        }

        // Auto-size columnas
        for ($i = 1; $i <= count($headers); $i++)
        {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    // Crear el archivo Excel
    $writer = new Xlsx($spreadsheet);

    // Headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}
