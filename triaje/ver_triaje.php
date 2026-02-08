<?php
// ver_triaje.php - Informe Detallado de Triaje (Versión Imprimible a Color)
session_start();

if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ambulatorio");
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT t.*, 
        p.cedula, p.nombres, p.apellidos, p.fecha_nacimiento,
        um.nombre as medico_nombre
        FROM triaje t
        INNER JOIN pacientes p ON t.paciente_id = p.id
        INNER JOIN usuario_medico um ON t.usuario_id = um.id
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$triaje = $stmt->get_result()->fetch_assoc();

if (!$triaje) { die("Registro no encontrado."); }

// Calcular edad
if(empty($triaje['edad']) && !empty($triaje['fecha_nacimiento'])) {
    $dob = new DateTime($triaje['fecha_nacimiento']);
    $now = new DateTime();
    $triaje['edad'] = $now->diff($dob)->y;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Triaje #<?= $id ?></title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    
    <style>
        /* --- ESTILOS GENERALES --- */
        :root {
            --primary-red: #aa0b0b;
            --primary-blue: #003366;
            --bg-light: #eef2f6;
            --text-dark: #333;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text-dark);
            padding-bottom: 40px;
        }

        /* --- NAVBAR --- */
        .navbar {
            background: linear-gradient(90deg, var(--primary-red) 0%, var(--primary-blue) 100%);
            padding: 10px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            color: white !important;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-volver {
            color: white;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 6px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-volver:hover { background: rgba(255,255,255,0.2); color: white; }

        .btn-print-nav {
            background: rgba(255,255,255,0.9);
            color: var(--primary-blue);
            border: none;
            padding: 6px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.3s;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn-print-nav:hover { background: white; color: var(--primary-red); }

        /* --- HOJA DE PAPEL --- */
        .paper-sheet {
            background: white;
            max-width: 900px;
            margin: 30px auto;
            padding: 50px;
            border-radius: 5px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            position: relative;
        }

        /* --- ENCABEZADO REPORTE --- */
        .report-header {
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inst-name { font-size: 1.2rem; font-weight: 800; color: var(--primary-blue); margin: 0; line-height: 1.2; }
        .inst-sub { font-size: 0.85rem; color: #666; margin: 0; }
        .report-title { font-size: 1.5rem; font-weight: 800; color: var(--primary-red); margin: 0; text-transform: uppercase; }
        .report-id { font-size: 0.9rem; color: #888; font-family: monospace; text-align: right; }

        /* --- SECCIONES --- */
        .section-title {
            background-color: #f8f9fa;
            padding: 8px 15px;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            font-size: 0.85rem;
            border-left: 4px solid var(--primary-blue);
            margin-bottom: 15px;
            margin-top: 25px;
        }

        .data-label { font-size: 0.75rem; color: #777; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .data-value { font-size: 1.05rem; font-weight: 500; color: #000; padding-bottom: 5px; border-bottom: 1px dotted #ccc; }

        /* --- GRID SIGNOS --- */
        .vitals-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #eee;
        }
        .vital-item { text-align: center; }
        .vital-val { font-size: 1.1rem; font-weight: 700; color: var(--primary-blue); }
        .vital-unit { font-size: 0.7rem; color: #666; }

        /* --- CAJA DE PRIORIDAD (COLORES) --- */
        .priority-box {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        .priority-val { font-size: 2.2rem; font-weight: 900; line-height: 1; letter-spacing: 1px; }
        .priority-lbl { font-size: 0.8rem; text-transform: uppercase; opacity: 0.9; margin-bottom: 5px; }

        .bg-rojo { background-color: #d32f2f !important; border: 1px solid #b71c1c; }
        .bg-naranja { background-color: #f57c00 !important; border: 1px solid #e65100; }
        .bg-verde { background-color: #2e7d32 !important; border: 1px solid #1b5e20; }

        /* --- REGLAS DE IMPRESIÓN --- */
        @media print {
            body { background-color: white; padding: 0; margin: 0; }
            .d-print-none { display: none !important; }
            .paper-sheet { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; border: none; }
            
            /* Limpieza visual para papel */
            .vitals-grid { border: 1px solid #ccc; background: none; }
            .section-title { background: none; border-bottom: 1px solid #000; border-left: none; color: black; padding-left: 0; }
            
            /* --- FUERZA EL COLOR DE FONDO AL IMPRIMIR --- */
            .priority-box, .bg-rojo, .bg-naranja, .bg-verde {
                -webkit-print-color-adjust: exact !important;   /* Chrome/Safari */
                print-color-adjust: exact !important;           /* Firefox */
                color: white !important;                        /* Texto blanco */
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top d-print-none">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">
                <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" />
                <span>Detalle de Ficha</span>
            </a>
            
            <div class="ms-auto d-flex align-items-center">
                <button onclick="window.print()" class="btn-print-nav">🖨 Imprimir</button>
                <a href="triaje.php" class="btn-volver">✕ Cerrar</a>
            </div>
        </div>
    </nav>

    <div style="height: 80px;" class="d-print-none"></div>

    <div class="paper-sheet">
        
        <header class="report-header">
            <div style="display: flex; align-items: center; gap: 15px;">
                <img src="../icons/logo.png" alt="Logo" style="height: 60px;">
                <div>
                    <h1 class="inst-name">Ambulatorio Urbano I<br>Libertador</h1>
                    <p class="inst-sub">Sistema Integral de Gestión Médica</p>
                </div>
            </div>
            <div class="report-meta">
                <h2 class="report-title">Ficha de Triaje</h2>
                <div class="report-id">Ref: #<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></div>
                <div class="report-id"><?= date('d/m/Y h:i A', strtotime($triaje['fecha_triaje'])) ?></div>
            </div>
        </header>

        <div class="row">
            <div class="col-md-8">
                
                <div class="section-title">1. Identificación del Paciente</div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="data-label">Cédula</div>
                        <div class="data-value"><?= htmlspecialchars($triaje['cedula']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="data-label">Nombre Completo</div>
                        <div class="data-value"><?= htmlspecialchars($triaje['nombres'] . ' ' . $triaje['apellidos']) ?></div>
                    </div>
                    <div class="col-md-2">
                        <div class="data-label">Edad</div>
                        <div class="data-value"><?= $triaje['edad'] ?> Años</div>
                    </div>
                </div>

                <div class="section-title">2. Motivo de Consulta</div>
                <div class="mb-3">
                    <div class="data-value" style="border:none; background:#f9f9f9; padding:10px; border-radius:5px;">
                        <?= nl2br(htmlspecialchars($triaje['motivo_consulta'])) ?>
                    </div>
                </div>

                <div class="section-title">3. Signos Vitales</div>
                <div class="vitals-grid">
                    <div class="vital-item">
                        <div class="data-label">Tensión Art.</div>
                        <div class="vital-val"><?= $triaje['ta_sist'] ?>/<?= $triaje['ta_diast'] ?></div>
                        <div class="vital-unit">mmHg</div>
                    </div>
                    <div class="vital-item">
                        <div class="data-label">Frec. Card.</div>
                        <div class="vital-val"><?= $triaje['fc'] ?></div>
                        <div class="vital-unit">bpm</div>
                    </div>
                    <div class="vital-item">
                        <div class="data-label">Frec. Resp.</div>
                        <div class="vital-val"><?= $triaje['fr'] ?></div>
                        <div class="vital-unit">rpm</div>
                    </div>
                    <div class="vital-item">
                        <div class="data-label">Temp.</div>
                        <div class="vital-val"><?= $triaje['temperatura'] ?>°</div>
                        <div class="vital-unit">Celsius</div>
                    </div>
                    <div class="vital-item">
                        <div class="data-label">Saturación</div>
                        <div class="vital-val"><?= $triaje['saturacion'] ?>%</div>
                        <div class="vital-unit">O₂</div>
                    </div>
                    <div class="vital-item">
                        <div class="data-label">Dolor (EVA)</div>
                        <div class="vital-val"><?= $triaje['dolor'] ?>/10</div>
                        <div class="vital-unit">Nivel</div>
                    </div>
                    <div class="vital-item" style="grid-column: span 2;">
                        <div class="data-label">Conciencia</div>
                        <div class="vital-val" style="font-size:0.9rem; margin-top:5px;"><?= $triaje['conciencia'] ?></div>
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                
                <div class="section-title">4. Clasificación</div>
                
                <?php 
                    $prioridad = $triaje['prioridad_final'] ?: $triaje['prioridad_calculada'];
                    $color_class = ($prioridad == 'ROJO') ? 'bg-rojo' : (($prioridad == 'NARANJA') ? 'bg-naranja' : 'bg-verde');
                ?>

                <div class="priority-box <?= $color_class ?>">
                    <div class="priority-lbl">Nivel de Prioridad</div>
                    <div class="priority-val"><?= $prioridad ?></div>
                </div>

                <?php if($triaje['prioridad_final'] && $triaje['prioridad_final'] != $triaje['prioridad_calculada']): ?>
                    <div class="alert alert-warning p-2 mt-3" style="font-size:0.8rem; border-left:3px solid #ffc107;">
                        <strong>Nota de Cambio:</strong><br>
                        Sistema sugirió: <?= $triaje['prioridad_calculada'] ?>.<br>
                        <em>"<?= htmlspecialchars($triaje['razon_sobrescrito']) ?>"</em>
                    </div>
                <?php endif; ?>

                <div class="mt-5 pt-5 text-center">
                    <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 10px auto;"></div>
                    <div class="data-label">Médico Responsable</div>
                    <div style="font-size:0.9rem; font-weight:600;"><?= htmlspecialchars($triaje['medico_nombre']) ?></div>
                </div>

            </div>
        </div>

        <div class="section-title mt-4">5. Observaciones Clínicas</div>
        <div style="min-height: 60px; border:1px solid #eee; padding:15px; border-radius:5px; background:#fcfcfc; font-size:0.95rem;">
            <?= nl2br(htmlspecialchars($triaje['observaciones'] ?: 'Sin observaciones registradas.')) ?>
        </div>

    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>