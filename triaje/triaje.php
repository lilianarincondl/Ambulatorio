<?php
// triaje.php - Gestión de Triaje Médico

session_start();

// Validación de sesión
if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

// Conexión a BD
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Conexión fallida: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

// === PAGINACIÓN ===
$por_pagina = 15;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $por_pagina;

// === FILTROS ===
$where = [];
$parametros = [];
$tipos = '';

// 1. Cédula
if (!empty($_GET['cedula'])) {
    $where[] = "p.cedula LIKE ?";
    $parametros[] = '%' . trim($_GET['cedula']) . '%';
    $tipos .= 's';
}
// 2. Fecha (Día)
if (!empty($_GET['dia'])) {
    $where[] = "DATE(t.fecha_triaje) = ?";
    $parametros[] = $_GET['dia'];
    $tipos .= 's';
}
// 3. Mes
if (!empty($_GET['mes'])) {
    $where[] = "MONTH(t.fecha_triaje) = ? AND YEAR(t.fecha_triaje) = YEAR(CURDATE())";
    $parametros[] = (int)$_GET['mes'];
    $tipos .= 'i';
}
// 4. Año
if (!empty($_GET['anio'])) {
    $where[] = "YEAR(t.fecha_triaje) = ?";
    $parametros[] = (int)$_GET['anio'];
    $tipos .= 'i';
}
// 5. Color
if (!empty($_GET['color'])) {
    $where[] = "(COALESCE(t.prioridad_final, t.prioridad_calculada) = ? OR UPPER(COALESCE(t.prioridad_final, t.prioridad_calculada)) = UPPER(?))";
    $parametros[] = $_GET['color'];
    $parametros[] = $_GET['color'];
    $tipos .= 'ss';
}
// 6. Médico
if (!empty($_GET['medico'])) {
    $where[] = "t.usuario_id = ?";
    $parametros[] = (int)$_GET['medico'];
    $tipos .= 'i';
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Consulta Principal
$sql = "SELECT t.id, t.fecha_triaje, t.prioridad_calculada, t.prioridad_final,
               p.cedula, CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo,
               um.nombre AS medico
        FROM triaje t
        INNER JOIN pacientes p ON t.paciente_id = p.id
        INNER JOIN usuario_medico um ON t.usuario_id = um.id
        $where_sql
        ORDER BY t.fecha_triaje DESC
        LIMIT $por_pagina OFFSET $offset";

$stmt = $conn->prepare($sql);
if ($tipos) $stmt->bind_param($tipos, ...$parametros);
$stmt->execute();
$triajes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Total Paginación
$sql_total = "SELECT COUNT(*) as total FROM triaje t INNER JOIN pacientes p ON t.paciente_id = p.id $where_sql";
$stmt_total = $conn->prepare($sql_total);
if ($tipos) $stmt_total->bind_param($tipos, ...$parametros);
$stmt_total->execute();
$total_registros = $stmt_total->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);

// Médicos para filtro
$medicos = $conn->query("SELECT id, nombre FROM usuario_medico WHERE nombre != 'Admin' ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$stmt_total->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Triaje | Ambulatorio</title>
  
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    /* --- ESTILO GENERAL --- */
    body {
      background: #eef2f6;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }

    /* --- NAVBAR --- */
    .navbar {
      background: linear-gradient(90deg, #aa0b0b 0%, #003366 100%);
      padding: 10px 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .navbar-brand {
      font-weight: 600;
      color: white !important;
      font-size: 1.1rem;
    }

    .btn-volver {
        color: white;
        border: 1px solid rgba(255,255,255,0.5);
        padding: 5px 15px;
        border-radius: 20px;
        text-decoration: none;
        font-size: 14px;
        transition: 0.3s;
    }
    .btn-volver:hover { background: rgba(255,255,255,0.2); color: white; }

    /* --- TARJETA PRINCIPAL --- */
    .main-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      padding: 30px;
      margin-top: 30px;
      margin-bottom: 30px;
    }

    /* --- BARRA DE FILTROS --- */
    .filter-bar {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 15px;
        border: 1px solid #eee;
        margin-bottom: 25px;
    }

    .form-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #555;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        border-radius: 8px;
        font-size: 0.9rem;
    }

    /* --- TABLA MODERNA --- */
    .table-container {
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid #eee;
    }

    .table thead {
        background-color: #003366; /* Azul Médico */
        color: white;
    }

    .table th {
        font-weight: 500;
        padding: 15px;
        border: none;
        text-align: center;
        vertical-align: middle;
    }

    .table td {
        padding: 15px;
        vertical-align: middle;
        text-align: center;
        color: #555;
    }

    .table-hover tbody tr:hover { background-color: #f8f9fa; }

    /* --- ETIQUETAS DE PRIORIDAD --- */
    .badge-priority {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        display: inline-block;
        min-width: 80px;
    }

    .priority-rojo { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .priority-naranja { background-color: #fff3e0; color: #ef6c00; border: 1px solid #ffe0b2; }
    .priority-verde { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

    /* --- BOTONES --- */
    .btn-action {
        background: #e3f2fd;
        color: #003366;
        border: none;
        padding: 5px 15px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: 0.2s;
    }
    .btn-action:hover { background: #003366; color: white; }

    /* --- IMPRESIÓN --- */
    @media print {
        .navbar, .btn, .filter-bar, .d-print-none { display: none !important; }
        .main-card { box-shadow: none; border: none; padding: 0; margin: 0; }
        .table th { background-color: #eee !important; color: black !important; }
        .badge-priority { border: 1px solid #000; color: #000; background: none; }
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top d-print-none">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" />
        <span>Módulo de Triaje</span>
      </a>
      <div class="ms-auto">
        <a class="btn-volver" href="../dashboard.php">← Volver al Inicio</a>
      </div>
    </div>
  </nav>

  <div style="height: 70px;"></div>

  <div class="container">
    <div class="main-card">
      
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 d-print-none">
          <div>
              <h2 style="color: #003366; font-weight: 700; margin: 0;">Gestión de Triaje</h2>
              <p class="text-muted m-0">Priorización y clasificación de pacientes</p>
          </div>
          <div class="d-flex gap-2">
              <a href="reglas_triaje.php" class="btn btn-warning fw-bold text-dark" style="background: #ffecb3; border:none;">
                  ⚙ Reglas
              </a>
              <a href="nuevo_triaje.php" class="btn btn-danger fw-bold" style="background: #aa0b0b; border:none;">
                  + Nuevo Triaje
              </a>
              <button onclick="window.print()" class="btn btn-primary fw-bold" style="background: #003366; border:none;">
                  🖨 Imprimir
              </button>
          </div>
      </div>

      <div class="filter-bar d-print-none">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cédula Paciente</label>
                <input type="text" name="cedula" class="form-control" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['cedula'] ?? '') ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Médico Responsable</label>
                <select name="medico" class="form-select">
                    <option value="">Todos los médicos</option>
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

            <div class="col-md-2">
                <label class="form-label">Fecha (Día)</label>
                <input type="date" name="dia" class="form-control" value="<?= htmlspecialchars($_GET['dia'] ?? '') ?>">
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100" style="background: #003366; border:none;">Filtrar</button>
                <a href="triaje.php" class="btn btn-light border">x</a>
            </div>
        </form>
      </div>

      <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Paciente</th>
                        <th>Cédula</th>
                        <th>Médico</th>
                        <th>Nivel Calculado</th>
                        <th>Nivel Final</th>
                        <th class="d-print-none">Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($triajes)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron registros de triaje con estos filtros.</td></tr>
                    <?php else: ?>
                        <?php foreach ($triajes as $t): 
                            $clase_calc = 'priority-' . strtolower($t['prioridad_calculada']);
                            $clase_final = $t['prioridad_final'] ? 'priority-' . strtolower($t['prioridad_final']) : '';
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($t['fecha_triaje'])) ?></td>
                            <td class="fw-bold text-start ps-4"><?= htmlspecialchars($t['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($t['cedula']) ?></td>
                            <td><?= htmlspecialchars($t['medico']) ?></td>
                            
                            <td>
                                <span class="badge-priority <?= $clase_calc ?>">
                                    <?= $t['prioridad_calculada'] ?>
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($t['prioridad_final']): ?>
                                    <span class="badge-priority <?= $clase_final ?>">
                                        <?= $t['prioridad_final'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="d-print-none">
                                <a href="ver_triaje.php?id=<?= $t['id'] ?>" class="btn-action">
                                    Ver Ficha
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

    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>