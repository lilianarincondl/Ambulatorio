<?php
// pdf_jornada.php - Generador de PDF con dompdf (SISPAI-02 Dinámico)

require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Conexión a la base de datos
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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de jornada no válido");
}
$id_jornada = $_GET['id'];

// Obtener datos generales de la jornada
$stmt = $pdo->prepare("SELECT * FROM jornadas WHERE id = ?");
$stmt->execute([$id_jornada]);
$jornada = $stmt->fetch();

if (!$jornada) {
    die("Jornada no encontrada");
}

// Obtener los pacientes
$stmtPacientes = $pdo->prepare("SELECT * FROM jornada_pacientes WHERE jornada_id = ?");
$stmtPacientes->execute([$id_jornada]);
$pacientes = $stmtPacientes->fetchAll();

// Separar los pacientes en bloques de 15 (Paginación)
$bloques_pacientes = array_chunk($pacientes, 15);
if (empty($bloques_pacientes)) {
    $bloques_pacientes = [[]]; // Si la jornada está vacía, muestra el formato en blanco
}

// Función auxiliar: si la celda de vacuna está vacía, imprime "-"
function v($p, $key) {
    $val = trim($p[$key] ?? '');
    return ($val !== '') ? htmlspecialchars($val) : '-';
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Diario de Vacunación</title>
    <style>
        @page { size: A4 landscape; margin: 5mm 8mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 7px; color: #000; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        .page-break { page-break-after: always; }
        
        .top-header { width: 100%; display: table; margin-bottom: 5px; }
        .top-left, .top-center, .top-right { display: table-cell; vertical-align: middle; }
        .top-left { width: 20%; text-align: left; }
        .top-center { width: 60%; text-align: center; font-weight: bold; font-size: 8px; line-height: 1.2; }
        .top-right { width: 20%; text-align: right; line-height: 1.1; }
        
        .form-header { width: 100%; font-size: 7.5px; margin-bottom: 3px; line-height: 1.6;}
        .underline { border-bottom: 1px solid #000; display: inline-block; padding-left: 5px; padding-right: 5px; text-transform: uppercase;}
        
        .nominal-table td { border: 1px solid #000; text-align: center; padding: 3px 2px; font-size: 6.5px; text-transform: uppercase;}
        .nominal-table th, .lotes-table th { background-color: #e6e6e6; border: 1px solid #000; text-align: center; padding: 2px; font-size: 6px; font-weight: bold; vertical-align: middle;}
        /* Fijamos la altura de las filas para que no se deformen al ser poquitas */
        .nominal-table td, .lotes-table td { border: 1px solid #000; text-align: center; padding: 2px; font-size: 6.5px; height: 14px; text-transform: uppercase;}
        .lotes-table td { text-align: left; padding-left: 4px; }
        .lotes-table td.center { text-align: center; padding-left: 0; }
        
        .leyenda-top { font-size: 6px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .codigos-box { border: 1px solid #000; margin-bottom: 3px; padding: 3px; font-size: 5.5px; line-height: 1.1;}
        .instructivo { font-size: 5.5px; text-align: justify; line-height: 1.1; text-transform: uppercase;}
        /* Ajustamos la tabla para que no tenga altura fija */
        .nominal-table td { height: auto; padding: 4px 2px; }
        
        /* Paginación en la esquina inferior derecha */
        .pagenum:before { content: counter(page); }
        .pagecount:before { content: counter(pages); }
        
        .footer-pagination {
            position: fixed;
            bottom: 0;
            right: 0;
            font-size: 7px;
            color: #6c757d;
        }

    </style>
</head>
<body>

    <?php foreach ($bloques_pacientes as $index => $grupo): ?>
        
        <!-- PÁGINA ANVERSO -->
        <div class="anverso">
            <div class="top-header">
                <div class="top-left">
                    <strong>Gobierno Bolivariano de Venezuela</strong><br>
                    <span style="font-size: 5px;">Ministerio del Poder Popular para la Salud</span>
                </div>
                <div class="top-center">
                    DIRECCIÓN GENERAL DE EPIDEMIOLOGÍA<br>
                    DIRECCIÓN DE INMUNIZACIONES<br>
                    SISTEMA DE INFORMACIÓN DEL PROGRAMA AMPLIADO DE INMUNIZACIONES<br>
                    REGISTRO DIARIO DE VACUNACIÓN
                </div>
                <div class="top-right">
                    <strong>Dirección de INMUNIZACIÓN</strong><br>
                    <span style="font-size: 5px;">Dirección General de Epidemiología<br>SISPAI-02 VERSIÓN 2017 (ANVERSO)</span>
                </div>
            </div>

            <div class="form-header">
                1. FECHA: <span class="underline" style="width: 70px; text-align: center;"><?= date('d / m / Y', strtotime($jornada['fecha'])) ?></span> &nbsp;&nbsp;
                2. ASIC: <span class="underline" style="width: 90px; text-align: center;"><?= htmlspecialchars($jornada['asig'] ?? '') ?></span> &nbsp;&nbsp;
                3. CÓDIGO: <span class="underline" style="width: 70px; text-align: center;"><?= htmlspecialchars($jornada['codigo'] ?? '') ?></span> &nbsp;&nbsp;
                4. ESTABLECIMIENTO: <span class="underline" style="width: 200px; text-align: center;"><?= htmlspecialchars($jornada['establecimiento'] ?? '') ?></span><br>
                
                5. RESPONSABLES: <span class="underline" style="width: 250px; text-align: center;"><?= htmlspecialchars($jornada['responsables'] ?? '') ?></span> &nbsp;&nbsp;
                6. HORA INICIO: <span class="underline" style="width: 40px; text-align: center;"><?= htmlspecialchars($jornada['hora_inicio'] ?? '') ?></span> &nbsp;&nbsp;
                7. HORA FIN: <span class="underline" style="width: 40px; text-align: center;"><?= htmlspecialchars($jornada['hora_fin'] ?? '') ?></span> &nbsp;&nbsp;
                
                8. ESTRATEGIA: <span class="underline" style="width: 90px; text-align: center;"><?= htmlspecialchars($jornada['estrategia'] ?? '') ?></span> &nbsp;&nbsp;
                DÓNDE: <span class="underline" style="width: 220px; text-align: center;"><?= htmlspecialchars($jornada['estrategia_donde'] ?? '') ?></span>
            </div>

            <div class="leyenda-top">
                DU = DOSIS ÚNICA; DA = DOSIS ADICIONAL; 1D = PRIMERA DOSIS; 2D = SEGUNDA DOSIS; 3D = TERCERA DOSIS. N/A = NO APLICA (NO HAY RESTRICCIONES DE GRUPO ESPECIAL O EDAD).
            </div>

            <table class="nominal-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 15px;">Nº</th>
                        <th rowspan="2">9. NOMBRE</th>
                        <th rowspan="2">10. APELLIDO</th>
                        <th rowspan="2" style="width: 45px;">11. FECHA<br>NACIMIENTO</th>
                        <th rowspan="2" style="width: 45px;">12. NACIONALIDAD</th>
                        <th rowspan="2" style="width: 55px;">13. NÚMERO DE<br>CÉDULA</th>
                        <th rowspan="2" style="width: 25px;">14.<br>ORDEN<br>HIJO</th>
                        <th rowspan="2" style="width: 130px;">15. DIRECCIÓN<br>(COMUNIDAD, LOCALIDAD, CALLE, CASA)</th>
                        <th rowspan="2" style="width: 40px;">16. ETNIA</th>
                        <th rowspan="2" style="width: 25px;">17.<br>EDAD</th>
                        <th rowspan="2" style="width: 25px;">18.<br>SEXO</th>
                        <th rowspan="2" style="width: 45px;">19. GRUPOS<br>ESPECIALES</th>
                        <th colspan="3">20. BIOLÓGICOS</th>
                    </tr>
                    <tr>
                        <th style="width: 35px;">BCG</th>
                        <th style="width: 45px;">HEPATITIS B</th>
                        <th style="width: 45px;">ROTAVIRUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $numero_fila = ($index * 15) + 1;
                    // El bucle ahora solo dibuja las filas de los pacientes que SÍ existen
                    foreach ($grupo as $p): 
                    ?>
                    <tr>
                        <td><?= $numero_fila++ ?></td>
                        <td style="text-align: left; padding-left: 2px;"><?= htmlspecialchars($p['nombre'] ?? '') ?></td>
                        <td style="text-align: left; padding-left: 2px;"><?= htmlspecialchars($p['apellido'] ?? '') ?></td>
                        <td><?= (!empty($p['fecha_nacimiento'])) ? date('d/m/Y', strtotime($p['fecha_nacimiento'])) : '' ?></td>
                        <td><?= htmlspecialchars($p['nacionalidad'] ?? '') ?></td>
                        <td><?= htmlspecialchars($p['documento'] ?? '') ?></td>
                        <td><?= htmlspecialchars($p['orden_hijo'] ?? '') ?></td>
                        <td style="text-align: left; padding-left: 2px; font-size: 5px;"><?= htmlspecialchars($p['direccion'] ?? '') ?></td>
                        <td><?= htmlspecialchars($p['etnia'] ?? '') ?></td>
                        <td><?= htmlspecialchars($p['edad'] ?? '') ?></td>
                        <td><?= htmlspecialchars($p['sexo'] ?? '') ?></td>
                        <td><?= v($p, 'grupo_especial') ?></td>
                        <td><?= v($p, 'dosis_bcg') ?></td>
                        <td><?= v($p, 'dosis_hepb') ?></td>
                        <td><?= v($p, 'dosis_rotavirus') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="codigos-box">
                <strong>PUEBLOS INDÍGENAS / ETNIAS (CÓDIGOS PRINCIPALES):</strong> 01 CUMANAGOTO | 02 EÑEPA | 03 GAYÓN | 08 JIWI | 25 URUAK | 26 WARAO | 28 WAYUU | 30 YANOMAMI | 31 YEKUANA | 33 YUKPA | 34 BLANCO/CRIOLLO | 35 AFRODESCENDIENTE | 36 MESTIZO | 48 OTRO.<br>
                <strong>GRUPOS ESPECIALES (CÓD):</strong> 01 MILITARES | 02 EMBARAZADAS | 03 CRÓNICOS | 04 SALUD | 05 DIÁLISIS | 06 PRIVADOS DE LIBERTAD | 08 TRABAJADORES SEXUALES | 09 VIAJEROS | 10 OTRO.
            </div>
            
            <div class="instructivo">
                INSTRUCTIVO (1/2): 1. INDIQUE LA FECHA EN LA QUE ESTÁN ADMINISTRANDO LOS BIOLÓGICOS; 2. ESCRIBA EL NOMBRE DEL ASIC; 3. CÓDIGO DEL ESTABLECIMIENTO; 4. NOMBRE DEL ESTABLECIMIENTO; 5. RESPONSABLES; 6. HORA INICIO; 7. HORA FIN; 8. ESTRATEGIA Y DÓNDE SE REALIZÓ; 9 A 19. COMPLETE LOS DATOS PERSONALES DEL USUARIO SEGÚN CORRESPONDA.
            </div>
        </div>

        <div class="page-break"></div>

        <!-- PÁGINA REVERSO -->
        <div class="reverso">
            <div class="top-header">
                <div class="top-left">
                    <strong>Gobierno Bolivariano de Venezuela</strong><br>
                    <span style="font-size: 5px;">Ministerio del Poder Popular para la Salud</span>
                </div>
                <div class="top-center">
                    DIRECCIÓN GENERAL DE EPIDEMIOLOGÍA<br>
                    DIRECCIÓN DE INMUNIZACIONES<br>
                    SISTEMA DE INFORMACIÓN DEL PROGRAMA AMPLIADO DE INMUNIZACIONES<br>
                    REGISTRO DIARIO DE VACUNACIÓN
                </div>
                <div class="top-right">
                    <strong>Dirección de INMUNIZACIÓN</strong><br>
                    <span style="font-size: 5px;">Dirección General de Epidemiología<br>SISPAI-02 VERSIÓN 2017 (REVERSO)</span>
                </div>
            </div>
            
            <div class="leyenda-top">
                DU=DOSIS ÚNICA; DA=DOSIS ADICIONAL; DE=DOSIS ESTACIONAL; 1D=PRIMERA; 2D=SEGUNDA; 3D=TERCERA; 4D=CUARTA; 5D=QUINTA; 6D=SEXTA; 7D=SÉPTIMA; 1REF=PRIMER REFUERZO; 2REF=SEGUNDO REFUERZO.
            </div>

            <table class="nominal-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 15px;">Nº</th>
                        <th colspan="11">20. BIOLÓGICOS</th>
                        <th rowspan="2" style="width: 45px;">21. OTROS<br>BIOLÓGICOS</th>
                    </tr>
                    <tr>
                        <th style="width: 45px;">PENTAVALENTE</th>
                        <th style="width: 45px;">POLIO<br>INYECTABLE</th>
                        <th style="width: 45px;">POLIO<br>ORAL</th>
                        <th style="width: 45px;">NEUMOCOCO<br>CONJUGADA</th>
                        <th style="width: 45px;">INFLUENZA<br>ESTACIONAL</th>
                        <th style="width: 35px;">FIEBRE<br>AMARILLA</th>
                        <th style="width: 45px;">SRP</th>
                        <th style="width: 45px;">TOXOIDE<br>TETÁNICO</th>
                        <th style="width: 45px;">NEUMOCOCO<br>POLISACÁRIDA</th>
                        <th style="width: 45px;">MENINGOCÓCICA<br>B-C</th>
                        <th style="width: 60px;">ANTI-RÁBICA (PRE / POST)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $numero_fila = ($index * 15) + 1;
                    foreach ($grupo as $p): 
                    ?>
                    <tr>
                        <td><?= $numero_fila++ ?></td>
                        <td><?= v($p, 'dosis_pentavalente') ?></td>
                        <td><?= v($p, 'dosis_polio_iny') ?></td>
                        <td><?= v($p, 'dosis_polio_oral') ?></td>
                        <td><?= v($p, 'dosis_neumo_conj') ?></td>
                        <td><?= v($p, 'dosis_influenza') ?></td>
                        <td><?= v($p, 'dosis_fiebre_ama') ?></td>
                        <td><?= v($p, 'dosis_srp') ?></td>
                        <td><?= v($p, 'dosis_toxoide') ?></td>
                        <td><?= v($p, 'dosis_neumo_poli') ?></td>
                        <td><?= v($p, 'dosis_meningo') ?></td>
                        
                        <!-- LÓGICA INTELIGENTE PARA ANTI-RÁBICA -->
                        <td>
                            <?php 
                            $pre = trim($p['dosis_rabia_pre'] ?? '');
                            $post = trim($p['dosis_rabia_post'] ?? '');
                            
                            if ($pre !== '' && $post !== '') {
                                echo "PRE: " . htmlspecialchars($pre) . " / POST: " . htmlspecialchars($post);
                            } elseif ($pre !== '') {
                                echo "PRE: " . htmlspecialchars($pre);
                            } elseif ($post !== '') {
                                echo "POST: " . htmlspecialchars($post);
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                        <td><?= v($p, 'observaciones') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table class="lotes-table">
                <thead>
                    <tr>
                        <th colspan="12">22. NÚMEROS DE LOTE Y FECHAS DE VENCIMIENTO</th>
                    </tr>
                    <tr>
                        <th style="width: 12%;">BIOLÓGICO</th>
                        <th style="width: 7%;">Nº DE LOTE</th>
                        <th style="width: 8%;">FECHA VENC.</th>
                        <th style="width: 6%;">DOSIS P.</th>
                        
                        <th style="width: 12%;">BIOLÓGICO</th>
                        <th style="width: 7%;">Nº DE LOTE</th>
                        <th style="width: 8%;">FECHA VENC.</th>
                        <th style="width: 6%;">DOSIS P.</th>
                        
                        <th style="width: 13%;">BIOLÓGICO</th>
                        <th style="width: 7%;">Nº DE LOTE</th>
                        <th style="width: 8%;">FECHA VENC.</th>
                        <th style="width: 6%;">DOSIS P.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>BCG</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>POLIO INYECTABLE</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>SARAMPIÓN/RUBÉOLA/PAROTIDITIS</td><td class="center"></td><td class="center"></td><td class="center"></td>
                    </tr>
                    <tr>
                        <td>HEPATITIS B</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>POLIO ORAL</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>TOXOIDE TETÁNICO DIFTÉRICO</td><td class="center"></td><td class="center"></td><td class="center"></td>
                    </tr>
                    <tr>
                        <td>HEPATITIS B (Pediátrico)</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>NEUMOCOCO CONJUGADA</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>NEUMOCOCO POLISACÁRIDA</td><td class="center"></td><td class="center"></td><td class="center"></td>
                    </tr>
                    <tr>
                        <td>ROTAVIRUS</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>INFLUENZA ESTACIONAL</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>MENINGOCÓCICA B-C</td><td class="center"></td><td class="center"></td><td class="center"></td>
                    </tr>
                    <tr>
                        <td>PENTAVALENTE</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>FIEBRE AMARILLA</td><td class="center"></td><td class="center"></td><td class="center"></td>
                        <td>RABIA HUMANA</td><td class="center"></td><td class="center"></td><td class="center"></td>
                    </tr>
                </tbody>
            </table>

            <div class="instructivo">
                INSTRUCTIVO (2/2): 20. INDIQUE EL BIOLÓGICO Y LA DOSIS QUE SE ESTÁ ADMINISTRANDO AL USUARIO, MARCANDO O RELLENANDO EL RECUADRO CORRESPONDIENTE. 21. ESCRIBA EL NOMBRE DEL BIOLÓGICO Y LA DOSIS QUE SE ESTÁ ADMINISTRANDO AL USUARIO. 22. ESCRIBA LOS NÚMEROS DE LOTE Y FECHAS DE VENCIMIENTO DE LOS DIFERENTES BIOLÓGICOS REGISTRADOS EN EL FORMATO.
            </div>
        </div>
        
        <?php if ($index < count($bloques_pacientes) - 1): ?>
            <div class="page-break"></div>
        <?php endif; ?>
        
    <?php endforeach; ?>

    <div class="footer-pagination">
        Página <span class="pagenum"></span> de <span class="pagecount"></span>
    </div>

</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('defaultFont', 'Helvetica');
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$nombre_archivo = 'Registro_Diario_' . date('Ymd_His') . '_JOR' . $jornada['id'] . '.pdf';
$download = isset($_GET['download']) && $_GET['download'] == 1;

$dompdf->stream($nombre_archivo, array(
    'Attachment' => $download,
    'compress' => true
));
?>