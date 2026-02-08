<?php
// triaje.php - Módulo de Triaje (Diseño Corporativo + Impresión Horizontal Centrada)

session_start();

if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// === PAGINACIÓN ===
$por_pagina = 15;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// === FILTROS ===
$where = [];
$parametros = [];
$tipos = '';

if (!empty($_GET['cedula'])) {
    $where[] = "p.cedula LIKE ?";
    $parametros[] = '%' . trim($_GET['cedula']) . '%';
    $tipos .= 's';
}

if (!empty($_GET['dia'])) {
    $where[] = "DAY(t.fecha_triaje) = ?";
    $parametros[] = (int)$_GET['dia'];
    $tipos .= 'i';
}

if (!empty($_GET['mes'])) {
    $where[] = "MONTH(t.fecha_triaje) = ? AND YEAR(t.fecha_triaje) = YEAR(CURDATE())";
    $parametros[] = (int)$_GET['mes'];
    $tipos .= 'i';
}

if (!empty($_GET['anio'])) {
    $where[] = "YEAR(t.fecha_triaje) = ?";
    $parametros[] = (int)$_GET['anio'];
    $tipos .= 'i';
}

if (!empty($_GET['color'])) {
    $where[] = "(COALESCE(t.prioridad_final, t.prioridad_calculada) = ?)";
    $parametros[] = $_GET['color'];
    $tipos .= 's';
}

if (!empty($_GET['medico'])) {
    $where[] = "t.usuario_id = ?";
    $parametros[] = (int)$_GET['medico'];
    $tipos .= 'i';
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Consulta Principal
$sql = "
    SELECT 
        t.id, t.fecha_triaje, t.prioridad_calculada, t.prioridad_final,
        p.cedula, 
        CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
        um.nombre AS medico
    FROM triaje t
    INNER JOIN pacientes p ON t.paciente_id = p.id
    INNER JOIN usuario_medico um ON t.usuario_id = um.id
    $where_sql
    ORDER BY t.fecha_triaje DESC
    LIMIT $por_pagina OFFSET $offset
";

$stmt = $conn->prepare($sql);
if ($tipos) $stmt->bind_param($tipos, ...$parametros);
$stmt->execute();
$result = $stmt->get_result();
$triajes = $result->fetch_all(MYSQLI_ASSOC);

// Total Registros
$sql_total = "SELECT COUNT(*) as total FROM triaje t INNER JOIN pacientes p ON t.paciente_id = p.id $where_sql";
$stmt_total = $conn->prepare($sql_total);
if ($tipos) $stmt_total->bind_param($tipos, ...$parametros);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_row = $total_result->fetch_assoc();
$total_registros = $total_row['total'];
$total_paginas = ceil($total_registros / $por_pagina);

$sql_medicos = "SELECT id, nombre FROM usuario_medico WHERE nombre != 'Admin' ORDER BY nombre ASC";
$result_medicos = $conn->query($sql_medicos);
$medicos = $result_medicos->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestión de Triaje | Ambulatorio</title>
  
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    /* --- ESTILOS GENERALES (Pantalla) --- */
    body {
      background: #eef2f6;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }

    /* Navbar degradado */
    .navbar {
      background: linear-gradient(90deg, #aa0b0b 0%, #003366 100%);
      padding: 10px 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .navbar-brand { color: white !important; font-weight: 600; font-size: 1.1rem; }

    /* Botón Volver */
    .btn-volver {
        color: white; border: 1px solid rgba(255,255,255,0.5); padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 14px; transition: 0.3s;
    }
    .btn-volver:hover { background: rgba(255,255,255,0.2); color: white; }

    /* Tarjeta Flotante */
    .main-card {
      background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 30px; margin-top: 30px; margin-bottom: 30px;
    }

    /* Filtros */
    .filter-bar {
        background: #f8f9fa; padding: 15px; border-radius: 15px; border: 1px solid #eee; margin-bottom: 25px;
    }
    .form-label { font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 2px; }
    .form-control, .form-select { border-radius: 8px; font-size: 0.9rem; }

    /* Tabla Pantalla */
    .table-container { border-radius: 15px; overflow: hidden; border: 1px solid #eee; }
    .table thead { background-color: #003366; color: white; }
    .table th { font-weight: 500; padding: 12px; border: none; text-align: center; vertical-align: middle; }
    .table td { padding: 10px; vertical-align: middle; text-align: center; color: #555; }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }

    /* Badges (Pantalla) */
    .badge-prio {
        padding: 6px 15px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; display: inline-block; min-width: 90px; border: 1px solid transparent;
    }
    .prio-rojo { background-color: #ffebee; color: #c62828; border-color: #ffcdd2; }
    .prio-naranja { background-color: #fff3e0; color: #ef6c00; border-color: #ffe0b2; }
    .prio-verde { background-color: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }

    /* Botones Acción */
    .btn-action {
        width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; text-decoration: none; background: #e3f2fd; color: #003366;
    }
    .btn-action:hover { background: #003366; color: white; }

    /* Paginación */
    .pagination .page-item .page-link { color: #003366; border-radius: 5px; margin: 0 3px; border: 1px solid #dee2e6; }
    .pagination .page-item.active .page-link { background-color: #003366; border-color: #003366; color: white; }

    /* Ocultar elementos de impresión en pantalla */
    .print-header, .print-footer { display: none; }

    /* ========================================= */
    /* === DISEÑO DE IMPRESIÓN (OPTIMIZADO) === */
    /* ========================================= */
    @media print {
        /* Configuración de la página: Horizontal y Márgenes */
        @page { 
            size: landscape; 
            margin: 10mm; 
        }

        /* 1. Ocultar interfaz de usuario */
        .navbar, .d-print-none, .filter-bar, .pagination, .col-acciones, .btn { 
            display: none !important; 
        }
        
        /* 2. Ajustes de Contenedor para Centrado */
        body { 
            background: white; 
            padding: 0; 
            margin: 0; 
            font-family: Arial, sans-serif;
            zoom: 90%; /* Reduce un poco para asegurar que quepa todo */
        }
        
        .main-card { 
            box-shadow: none; 
            border: none; 
            padding: 0; 
            margin: 0 auto; 
            width: 100%; 
            max-width: 100%; 
        }
        
        .container { 
            width: 100% !important; 
            max-width: 100% !important; 
            padding: 0; 
            margin: 0 auto; 
            text-align: center; /* Centrar contenido */
        }
        
        /* 3. Encabezado Oficial */
        .print-header {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #aa0b0b;
            margin-bottom: 20px;
            padding-bottom: 10px;
            text-align: left; /* Volver a alinear texto interno a la izquierda */
        }
        .print-logo { height: 50px; }
        .print-title h1 { font-size: 16pt; color: #003366; margin: 0; font-weight: bold; }
        .print-title p { margin: 0; color: #555; font-size: 10pt; }
        .print-meta { text-align: right; font-size: 9pt; color: #777; }

        /* 4. Tabla Profesional Centrada */
        .table-container { 
            border: none; 
            display: block;
            width: 100%;
        }
        
        .table { 
            width: 100% !important; /* Forzar ancho completo */
            margin: 0 auto; /* Centrar tabla */
            border-collapse: collapse; 
            font-size: 10pt; 
        }
        
        .table thead {
            background-color: #f0f0f0 !important;
            color: black !important;
            border-top: 1px solid #000;
            border-bottom: 2px solid #000;
        }
        
        .table th { 
            padding: 10px 5px; 
            border: 1px solid #ccc;
            text-transform: uppercase;
            font-size: 9pt;
            text-align: center;
        }
        
        .table td { 
            border: 1px solid #ccc; 
            padding: 8px 5px; 
            color: black;
            text-align: center;
        }

        /* 5. Badges con Borde (Ahorro Tinta) */
        .badge-prio {
            border-width: 2px !important;
            border-style: solid !important;
            background: transparent !important;
            color: black !important;
            padding: 2px 8px;
            font-weight: bold;
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
        }
        /* Colores del borde al imprimir */
        .prio-rojo { border-color: #dc3545 !important; color: #dc3545 !important; }
        .prio-naranja { border-color: #fd7e14 !important; color: #fd7e14 !important; }
        .prio-verde { border-color: #198754 !important; color: #198754 !important; }

        /* 6. Pie de Página */
        .print-footer {
            display: block !important;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
            background: white;
        }
    }

    /* Ocultar encabezado de impresión en pantalla normal */
    .print-header { display: none; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top d-print-none">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" />
        <span>Gestión de Triaje</span>
      </a>
      <div class="ms-auto">
        <a class="btn-volver" href="../dashboard.php">← Volver al Inicio</a>
      </div>
    </div>
  </nav>

  <div style="height: 70px;" class="d-print-none"></div>

  <div class="container">
    <div class="main-card">
      
      <div class="print-header">
          <div style="display:flex; align-items:center; gap:15px;">
              <img src="../icons/logo.png" class="print-logo" alt="Logo">
              <div class="print-title">
                  <h1>AMBULATORIO URBANO I LIBERTADOR</h1>
                  <p>Reporte Oficial de Pacientes Triageados</p>
              </div>
          </div>
          <div class="print-meta">
              Fecha: <?= date('d/m/Y') ?><br>
              Hora: <?= date('h:i A') ?><br>
              Usuario: <?= htmlspecialchars($_SESSION['usuario'] ?? 'Admin') ?>
          </div>
      </div>

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 d-print-none">
          <div>
              <h2 style="color: #003366; font-weight: 700; margin: 0;">Lista de Triajes</h2>
              <p class="text-muted m-0">Clasificación y priorización de pacientes</p>
          </div>
          <div class="d-flex gap-2">
              <a href="reglas_triaje.php" class="btn btn-warning fw-bold" style="background: #ffecb3; border:none; color: #664d03;">
                  ⚙ Reglas
              </a>
              <a href="nuevo_triaje.php" class="btn btn-danger fw-bold" style="background: #aa0b0b; border:none;">
                  + Nuevo Triaje
              </a>
              <button onclick="window.print()" class="btn btn-primary fw-bold" style="background: #003366; border:none;">
                  🖨 Imprimir Lista
              </button>
          </div>
      </div>

      <div class="filter-bar d-print-none">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cédula</label>
                <input type="text" name="cedula" class="form-control" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['cedula'] ?? '') ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Médico</label>
                <select name="medico" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach($medicos as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= (($_GET['medico'] ?? '') == $m['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Prioridad</label>
                <select name="color" class="form-select">
                    <option value="">Todas</option>
                    <option value="ROJO" <?= ($_GET['color'] ?? '') == 'ROJO' ? 'selected' : '' ?>>🔴 ROJO</option>
                    <option value="NARANJA" <?= ($_GET['color'] ?? '') == 'NARANJA' ? 'selected' : '' ?>>🟠 NARANJA</option>
                    <option value="VERDE" <?= ($_GET['color'] ?? '') == 'VERDE' ? 'selected' : '' ?>>🟢 VERDE</option>
                </select>
            </div>

            <div class="col-md-1">
                <label class="form-label">Día</label>
                <select name="dia" class="form-select">
                    <option value="">-</option>
                    <?php for($i=1; $i<=31; $i++): ?>
                        <option value="<?= $i ?>" <?= ($_GET['dia'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Mes</label>
                <select name="mes" class="form-select">
                    <option value="">-</option>
                    <?php 
                    $meses = [1=>"Ene", 2=>"Feb", 3=>"Mar", 4=>"Abr", 5=>"May", 6=>"Jun", 7=>"Jul", 8=>"Ago", 9=>"Sep", 10=>"Oct", 11=>"Nov", 12=>"Dic"];
                    foreach($meses as $num => $nom): ?>
                        <option value="<?= $num ?>" <?= ($_GET['mes'] ?? '') == $num ? 'selected' : '' ?>><?= $nom ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100" style="background: #003366; border:none;">Filtrar</button>
                <a href="triaje.php" class="btn btn-light border">✕</a>
            </div>
        </form>
      </div>

      <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Nombres y Apellidos</th>
                        <th>Cédula</th>
                        <th>Médico</th>
                        <th>Prioridad Calc.</th>
                        <th>Prioridad Final</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($triajes)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No hay registros con estos filtros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($triajes as $t): 
                            // Determinar clases de color para badges
                            $p_calc = $t['prioridad_calculada'];
                            $p_fin = $t['prioridad_final'];
                            
                            $cls_calc = 'prio-' . strtolower($p_calc);
                            $cls_fin = $p_fin ? 'prio-' . strtolower($p_fin) : '';
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($t['fecha_triaje'])) ?></td>
                            <td class="fw-bold text-start ps-4"><?= htmlspecialchars($t['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($t['cedula']) ?></td>
                            <td><?= htmlspecialchars($t['medico']) ?></td>
                            
                            <td>
                                <span class="badge-prio <?= $cls_calc ?>"><?= $p_calc ?></span>
                            </td>
                            
                            <td>
                                <?php if ($p_fin): ?>
                                    <span class="badge-prio <?= $cls_fin ?>"><?= $p_fin ?></span>
                                    <div class="d-print-none" style="font-size:0.7rem; color:#888;">(Modificado)</div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="col-acciones">
                                <a href="ver_triaje.php?id=<?= $t['id'] ?>" class="btn-action btn-view" title="Ver Detalles">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
      </div>

      <?php if ($total_paginas > 1): ?>
        <nav aria-label="Paginación" class="mt-4 d-print-none">
            <ul class="pagination justify-content-center">
                <?php for($p=1; $p<=$total_paginas; $p++): ?>
                    <li class="page-item <?= $p == $pagina_actual ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $p ?>&<?= http_build_query(array_merge($_GET, ['pagina' => $p])) ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
      <?php endif; ?>

      <div class="print-footer">
          Documento generado el <?= date('d/m/Y') ?>. Sistema de Gestión Ambulatorio Urbano I Libertador.
      </div>

    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>