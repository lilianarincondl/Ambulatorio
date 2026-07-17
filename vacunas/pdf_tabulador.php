<?php
// pdf_tabulador.php - Generador del Tabulador Mensual con dompdf

// Cargar el autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Conectar a la base de datos
$host = "localhost";
$dbname = "ambulatorio";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// ------------------------------------------------------------------
// 1. OBTENER Y PROCESAR DATOS DE LA BASE DE DATOS
// ------------------------------------------------------------------
// (AQUÍ DEBES HACER TU CONSULTA REAL. ESTO ES UN EJEMPLO DE CÓMO ESTRUCTURARLO)
// Supongamos que pasamos el mes y el año por la URL (ej: pdf_tabulador.php?mes=07&anio=2026)
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

$vacunas = [
    'BCG', 'HEPATITIS B', 'HEPATITIS B (Pediátrico)', 'ROTAVIRUS', 'PENTAVALENTE', 
    'POLIO INYECTABLE', 'POLIO ORAL', 'NEUMOCOCO CONJUGADA', 'INFLUENZA ESTACIONAL', 
    'FIEBRE AMARILLA', 'SAR/RUB/PAR', 'TOXOIDE TETÁNICO DIFTÉRICO', 
    'NEUMOCOCO POLISACÁRIDA', 'MENINGOCÓCICA B-C', 'ANTI-RÁBICA HUMANA'
];

// Inicializamos la matriz de datos en vacío
$tabulador = [];
foreach ($vacunas as $vacuna) {
    for ($dia = 1; $dia <= 31; $dia++) {
        $tabulador[$vacuna][$dia] = ''; // Puedes poner 0, pero vacío se ve mejor en el PDF
    }
}

// AQUÍ IRÍA TU LÓGICA PDO PARA LLENAR $tabulador
// Ejemplo: 
// $stmt = $pdo->prepare("SELECT DAY(fecha) as dia, nombre_vacuna, COUNT(id) as total FROM vacunas_aplicadas WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? GROUP BY dia, nombre_vacuna");
// $stmt->execute([$mes, $anio]);
// while($row = $stmt->fetch()) {
//     $tabulador[$row['nombre_vacuna']][$row['dia']] = $row['total'];
// }

// ------------------------------------------------------------------
// 2. CONSTRUIR EL HTML
// ------------------------------------------------------------------
ob_start(); // Empezamos a capturar la salida HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tabulador Diario de Vacunación</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; margin: 0; padding: 0; }
        .header { width: 100%; text-align: center; margin-bottom: 10px; display: table; }
        .header > div { display: table-cell; vertical-align: middle; }
        .logo-left, .logo-right { width: 20%; }
        .title { width: 60%; font-size: 10px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid black; padding: 2px 3px; }
        th { text-align: center; background-color: #f2f2f2; }
        .col-biologico { width: 16%; text-align: left; font-weight: bold; font-size: 7px; }
        .col-dia { width: 2.2%; text-align: center; }
        .col-total { width: 3%; text-align: center; font-weight: bold; background-color: #f9f9f9; }
        .instrucciones { font-size: 6px; text-align: justify; line-height: 1.1; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-left"><strong>[Logo MPPS]</strong></div>
        <div class="title">
            DIRECCIÓN GENERAL DE EPIDEMIOLOGÍA<br>
            DIRECCIÓN DE INMUNIZACIONES<br>
            SISTEMA DE INFORMACIÓN DEL PROGRAMA AMPLIADO DE INMUNIZACIONES<br>
            TABULADOR DIARIO DE VACUNACIÓN - MES: <?= str_pad($mes, 2, '0', STR_PAD_LEFT) ?> / <?= $anio ?>
        </div>
        <div class="logo-right"><strong>[Logo PAI]</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-biologico" style="text-align: left;">POBLACIÓN INDÍGENA</th>
                <th colspan="31">19. DIA DEL MES</th>
                <th class="col-total" rowspan="2">20.<br>TOTAL</th>
            </tr>
            <tr>
                <th class="col-biologico" style="text-align: center;">BIOLÓGICO</th>
                <?php for($i=1; $i<=31; $i++): ?>
                    <th class="col-dia"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            $gran_total_dosis = 0;
            foreach($vacunas as $nombre_vacuna): 
                $total_fila = 0;
            ?>
            <tr>
                <td class="col-biologico"><?= $nombre_vacuna ?></td>
                <?php for($dia=1; $dia<=31; $dia++): 
                    $cantidad = $tabulador[$nombre_vacuna][$dia];
                    if(is_numeric($cantidad)) { $total_fila += $cantidad; }
                ?>
                    <td style="text-align:center;"><?= $cantidad ?></td>
                <?php endfor; ?>
                <td class="col-total"><?= ($total_fila > 0) ? $total_fila : '' ?></td>
            </tr>
            <?php 
                $gran_total_dosis += $total_fila;
            endforeach; 
            ?>
            <tr>
                <td class="col-biologico" style="text-align: center;">21. TOTAL DOSIS ADMINISTRADAS</td>
                <td colspan="31"></td>
                <td class="col-total"><?= $gran_total_dosis ?></td>
            </tr>
        </tbody>
    </table>

    <div class="instrucciones">
        <strong>INSTRUCTIVO (6/6):</strong> 19. INDIQUE SEGÚN EL DÍA DEL MES EL NÚMERO DE DOSIS ADMINISTRADAS EN POBLACIÓN INDÍGENA...
    </div>

</body>
</html>
<?php
$html = ob_get_clean(); // Guardamos el HTML generado en la variable $html

// ------------------------------------------------------------------
// 3. CONFIGURAR Y RENDERIZAR DOMPDF
// ------------------------------------------------------------------
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Útil si las imágenes de los logos están en URLs externas

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// ¡MUY IMPORTANTE! El tabulador requiere que la hoja esté en horizontal (landscape)
$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

$nombre_archivo = 'Tabulador_Mes_' . $mes . '_' . $anio . '.pdf';
$download = isset($_GET['download']) && $_GET['download'] == 1;

$dompdf->stream($nombre_archivo, array(
    'Attachment' => $download,
    'compress' => true
));
?>