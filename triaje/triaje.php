<?php
// triaje.php - Módulo de Triaje (Versión Final con Filtro de Día y Colores)

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

// CAMBIO 1: Filtro de Día como selector numérico
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
  <title>Triaje - Ambulatorio Urbano I Libertador</title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <style>
    body { background: #f1f5f9; }
    .navbar { background-color: #aa0b0b; }
    .navbar-brand, .nav-link { color: #fff !important; }
    
    /* CAMBIO 2: Estilos para colores de prioridad en la tabla */
    .text-rojo { color: #dc3545 !important; font-weight: bold; }
    .text-naranja { color: #fd7e14 !important; font-weight: bold; }
    .text-verde { color: #198754 !important; font-weight: bold; }

    @media print {
        .navbar, .d-print-none, .card-header, form, .pagination { display: none !important; }
        /* CAMBIO 3: Quitar columna acciones al imprimir */
        .col-acciones { display: none !important; }
        .container-fluid { width: 100%; margin: 0; padding: 0; }
        .table { width: 100% !important; border: 1px solid #000; }
        th, td { border: 1px solid #ddd !important; padding: 8px; }
    }
  </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top shadow d-print-none">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="../dashboard.php">
                <img src="../icons/logo.png" alt="Logo" style="height: 40px; margin-right: 10px;" />
                Ambulatorio Urbano I Libertador
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link fw-bold" href="../dashboard.php">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

  <div class="container-fluid py-5 mt-5">
    <div class="row mb-4 align-items-center">
      <div class="col">
        <h2 class="text-danger fw-bold">Módulo de Triaje</h2>
        <p class="text-muted">Registro y priorización inteligente de pacientes</p>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2 d-print-none">
        <a href="nuevo_triaje.php" class="btn btn-outline-success border-2 fw-bold">+ Nuevo Triaje</a>
        <a href="reglas_triaje.php" class="btn btn-outline-warning border-2 fw-bold">Administrar Reglas</a>
        <button onclick="window.print()" class="btn btn-outline-primary border-2 fw-bold">Imprimir Reporte</button>
      </div>
    </div>

    <div class="card shadow mb-4 d-print-none">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cédula Paciente</label>
                    <input type="text" name="cedula" class="form-control" value="<?= htmlspecialchars($_GET['cedula'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Día</label>
                    <select name="dia" class="form-select">
                        <option value="">Todos</option>
                        <?php for($i=1; $i<=31; $i++): ?>
                            <option value="<?= $i ?>" <?= ($_GET['dia'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mes</label>
                    <select name="mes" class="form-select">
                        <option value="">Todos</option>
                        <?php 
                        $meses = [1=>"Enero", 2=>"Febrero", 3=>"Marzo", 4=>"Abril", 5=>"Mayo", 6=>"Junio", 7=>"Julio", 8=>"Agosto", 9=>"Septiembre", 10=>"Octubre", 11=>"Noviembre", 12=>"Diciembre"];
                        foreach($meses as $num => $nombre): ?>
                            <option value="<?= $num ?>" <?= ($_GET['mes'] ?? '') == $num ? 'selected' : '' ?>><?= $nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Año</label>
                    <input type="number" name="anio" class="form-control" value="<?= htmlspecialchars($_GET['anio'] ?? date('Y')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Médico</label>
                    <select name="medico" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach($medicos as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= (($_GET['medico'] ?? '') == $m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="triaje.php" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-dark">
              <tr>
                <th>Fecha/Hora</th>
                <th>Nombres y Apellidos</th>
                <th>Cédula</th>
                <th>Médico</th>
                <th>Prioridad Calculada</th>
                <th>Prioridad Final</th>
                <th class="col-acciones">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($triajes as $t): 
                $clase_calc = "text-" . strtolower($t['prioridad_calculada']);
                $clase_final = $t['prioridad_final'] ? "text-" . strtolower($t['prioridad_final']) : "";
              ?>
                <tr>
                  <td><?= date('d/m/Y H:i', strtotime($t['fecha_triaje'])) ?></td>
                  <td><?= htmlspecialchars($t['nombre_completo']) ?></td>
                  <td><?= htmlspecialchars($t['cedula']) ?></td>
                  <td><?= htmlspecialchars($t['medico']) ?></td>
                  <td class="<?= $clase_calc ?>"><?= $t['prioridad_calculada'] ?></td>
                  <td class="<?= $clase_final ?>">
                    <?= $t['prioridad_final'] ? $t['prioridad_final'] . " (modificado)" : '<span class="text-muted">Sin cambio</span>' ?>
                  </td>
                  <td class="col-acciones">
                    <a href="ver_triaje.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info">Ver</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>