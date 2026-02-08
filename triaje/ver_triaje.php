<?php
// ver_triaje.php - Visualización detallada de un triaje
session_start();

if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ambulatorio");
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Consulta detallada uniendo paciente y médico
$sql = "SELECT t.*, 
        p.cedula, p.nombres, p.apellidos,
        um.nombre as medico_nombre
        FROM triaje t
        INNER JOIN pacientes p ON t.paciente_id = p.id
        INNER JOIN usuario_medico um ON t.usuario_id = um.id
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$triaje = $stmt->get_result()->fetch_assoc();

if (!$triaje) {
    die("Registro de triaje no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Triaje - #<?= $id ?></title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <style>
        body { background: #f8fafc; padding-top: 80px; }
        .navbar { background-color: #aa0b0b; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .info-label { font-weight: bold; color: #475569; text-transform: uppercase; font-size: 0.8rem; }
        .info-value { font-size: 1.1rem; color: #1e293b; margin-bottom: 15px; }
        .prioridad-box { padding: 20px; border-radius: 10px; color: white; text-align: center; font-weight: bold; font-size: 1.5rem; }
        .bg-rojo { background-color: #dc3545; }
        .bg-naranja { background-color: #fd7e14; }
        .bg-verde { background-color: #198754; }
        
        .prioridad-box {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: white !important;
        }

        @media print {
            body { padding-top: 0; background: white; }
            .no-print, .navbar, .btn { display: none !important; }
            .container { width: 100%; max-width: 100%; margin: 0; }
            .card { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow no-print">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center text-white" href="#">
            <img src="../icons/logo.png" alt="Logo" style="height: 40px; margin-right: 10px;" />
            Detalle de Triaje Médico
        </a>
        <div class="ms-auto">
            <button onclick="window.print()" class="btn btn-light me-2">Imprimir</button>
            <a href="triaje.php" class="btn btn-outline-light">Cerrar</a>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">Información del Paciente</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="info-label">Nombre Completo</div>
                            <div class="info-value"><?= htmlspecialchars($triaje['nombres'] . ' ' . $triaje['apellidos']) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Cédula</div>
                            <div class="info-value"><?= htmlspecialchars($triaje['cedula']) ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Edad</div>
                            <div class="info-value"><?= $triaje['edad'] ?: 'N/A' ?> años</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">Evaluación de Signos Vitales</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 col-md-3 border-end mb-3">
                            <div class="info-label">Tensión Art.</div>
                            <div class="info-value"><?= $triaje['ta_sist'] ?>/<?= $triaje['ta_diast'] ?> <small>mmHg</small></div>
                        </div>
                        <div class="col-6 col-md-3 border-end mb-3">
                            <div class="info-label">Frec. Cardíaca</div>
                            <div class="info-value"><?= $triaje['fc'] ?> <small>lpm</small></div>
                        </div>
                        <div class="col-6 col-md-3 border-end mb-3">
                            <div class="info-label">Temperatura</div>
                            <div class="info-value"><?= $triaje['temperatura'] ?> <small>°C</small></div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="info-label">Saturación O₂</div>
                            <div class="info-value"><?= $triaje['saturacion'] ?> <small>%</small></div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="info-label">Conciencia</div>
                            <div class="info-value"><?= $triaje['conciencia'] ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Escala Dolor (EVA)</div>
                            <div class="info-value"><?= $triaje['dolor'] ?> / 10</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-bold">Reporte Clínico</div>
                <div class="card-body">
                    <div class="info-label">Motivo de Consulta</div>
                    <p class="info-value text-muted"><?= nl2br(htmlspecialchars($triaje['motivo_consulta'] ?: 'Ninguna registrada')) ?></p>
                    
                    <div class="info-label">Observaciones / Alarma</div>
                    <p class="info-value text-muted"><?= nl2br(htmlspecialchars($triaje['observaciones'] ?: 'Ninguna registrada')) ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-white fw-bold">Clasificación de Triaje</div>
                <div class="card-body">
                    <?php 
                        $prioridad = $triaje['prioridad_final'] ?: $triaje['prioridad_calculada'];
                        $color_class = ($prioridad == 'ROJO') ? 'bg-rojo' : (($prioridad == 'NARANJA') ? 'bg-naranja' : 'bg-verde');
                    ?>
                    <div class="prioridad-box <?= $color_class ?> mb-3">
                        <?= $prioridad ?>
                    </div>
                    
                    <?php if($triaje['prioridad_final'] && $triaje['prioridad_final'] != $triaje['prioridad_calculada']): ?>
                        <div class="alert alert-warning py-2 small">
                            <strong>Cambio Manual:</strong> El sistema sugirió <?= $triaje['prioridad_calculada'] ?>.
                            <br>Razon: <?= htmlspecialchars($triaje['razon_sobrescrito'] ?: 'No especificada') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-bold">Registro</div>
                <div class="card-body">
                    <div class="info-label">Atendido por</div>
                    <div class="info-value"><?= htmlspecialchars($triaje['medico_nombre'] ?: 'Desconocido') ?></div>
                    
                    <div class="info-label">Fecha y Hora</div>
                    <div class="info-value"><?= date('d/m/Y h:i A', strtotime($triaje['fecha_triaje'])) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>