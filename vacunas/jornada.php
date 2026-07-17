<?php
// jornadas.php - Gestión de Jornadas de Vacunación

// Configuración de la conexión a la base de datos
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

// Procesamiento de operaciones CRUD
$mensaje = "";
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM jornadas WHERE id = ?");
    if ($stmt->execute([$id])) {
        // Redirigir para limpiar URL
        header("Location: jornadas.php?msg=eliminado");
        exit();
    }
}

// Búsqueda con múltiples criterios
$condicion = "";
$parametro = [];
$filtros = [];

if (isset($_GET['buscar'])) {
    // Filtro por fecha
    $dia = $_GET['dia_busqueda'] ?? '';
    $mes = $_GET['mes_busqueda'] ?? '';
    $anio = $_GET['anio_busqueda'] ?? '';
    
    if ($dia && $mes && $anio) {
        $filtros[] = "DAY(fecha) = :dia AND MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        $parametro[':dia'] = $dia; $parametro[':mes'] = $mes; $parametro[':anio'] = $anio;
    } elseif ($mes && $anio) {
        $filtros[] = "MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        $parametro[':mes'] = $mes; $parametro[':anio'] = $anio;
    } elseif ($anio) {
        $filtros[] = "YEAR(fecha) = :anio";
        $parametro[':anio'] = $anio;
    } elseif ($mes) {
        $filtros[] = "MONTH(fecha) = :mes";
        $parametro[':mes'] = $mes;
    }
    
    // Filtro por texto
    if (!empty($_GET['texto_busqueda'])) {
        $filtros[] = "(establecimiento LIKE :texto OR responsables LIKE :texto)";
        $parametro[':texto'] = '%' . $_GET['texto_busqueda'] . '%';
    }
    
    if (!empty($filtros)) {
        $condicion = "WHERE " . implode(" AND ", $filtros);
    }
}

// Obtener registros
$sql = "SELECT * FROM jornadas $condicion ORDER BY fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($parametro);
$registros = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jornadas de Vacunación</title>
    
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    
    <style>
        /* --- ESTILOS GENERALES --- */
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
        .btn-volver:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

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
            padding: 15px;
            border-radius: 15px;
            border: 1px solid #eee;
            margin-bottom: 25px;
        }

        .form-select-sm, .form-control-sm {
            border-radius: 8px;
            border: 1px solid #ced4da;
        }

        /* --- TABLA --- */
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
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            color: #555;
            text-align: center;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* --- BOTONES ACCIÓN --- */
        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }

        /* Botón Ver (Azul Claro) */
        .btn-view { background: #e3f2fd; color: #003366; }
        .btn-view:hover { background: #003366; color: white; }

        /* Botón PDF (Morado/Gris) */
        .btn-pdf { background: #e0e0f8; color: #283593; }
        .btn-pdf:hover { background: #283593; color: white; }

        .btn-edit { background: #fff3cd; color: #856404; }
        .btn-edit:hover { background: #ffc107; color: black; }

        .btn-delete { background: #ffebee; color: #aa0b0b; }
        .btn-delete:hover { background: #aa0b0b; color: white; }

        .badge-date {
            background: #e3f2fd;
            color: #003366;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" onerror="this.style.display='none'"/>
                <span>Gestión de Jornadas</span>
            </a>
            <div class="ms-auto">
                <a class="btn-volver" href="../dashboard.php">← Volver al Inicio</a>
            </div>
        </div>
    </nav>

    <div style="height: 70px;"></div>

    <div class="container">
        <div class="main-card">
            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 style="color: #003366; font-weight: 700; margin: 0;">Jornadas de Vacunación</h2>
                    <p class="text-muted m-0">Registro histórico de operativos realizados</p>
                </div>
                <a href="registrar_jornada.php" class="btn btn-danger" style="background: #aa0b0b; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500;">
                    + Nueva Jornada
                </a>
            </div>

            <div class="filter-bar">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto fw-bold text-muted small">FILTRAR POR:</div>
                    
                    <div class="col-auto">
                        <select name="dia_busqueda" class="form-select form-select-sm" style="width: 70px;">
                            <option value="">Día</option>
                            <?php for ($d = 1; $d <= 31; $d++): ?>
                                <option value="<?= $d ?>" <?= isset($_GET['dia_busqueda']) && $_GET['dia_busqueda'] == $d ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-auto">
                        <select name="mes_busqueda" class="form-select form-select-sm">
                            <option value="">Mes</option>
                            <option value="1" <?= ($_GET['mes_busqueda'] ?? '') == '1' ? 'selected' : '' ?>>Enero</option>
                            <option value="2" <?= ($_GET['mes_busqueda'] ?? '') == '2' ? 'selected' : '' ?>>Febrero</option>
                            <option value="3" <?= ($_GET['mes_busqueda'] ?? '') == '3' ? 'selected' : '' ?>>Marzo</option>
                            <option value="4" <?= ($_GET['mes_busqueda'] ?? '') == '4' ? 'selected' : '' ?>>Abril</option>
                            <option value="5" <?= ($_GET['mes_busqueda'] ?? '') == '5' ? 'selected' : '' ?>>Mayo</option>
                            <option value="6" <?= ($_GET['mes_busqueda'] ?? '') == '6' ? 'selected' : '' ?>>Junio</option>
                            <option value="7" <?= ($_GET['mes_busqueda'] ?? '') == '7' ? 'selected' : '' ?>>Julio</option>
                            <option value="8" <?= ($_GET['mes_busqueda'] ?? '') == '8' ? 'selected' : '' ?>>Agosto</option>
                            <option value="9" <?= ($_GET['mes_busqueda'] ?? '') == '9' ? 'selected' : '' ?>>Septiembre</option>
                            <option value="10" <?= ($_GET['mes_busqueda'] ?? '') == '10' ? 'selected' : '' ?>>Octubre</option>
                            <option value="11" <?= ($_GET['mes_busqueda'] ?? '') == '11' ? 'selected' : '' ?>>Noviembre</option>
                            <option value="12" <?= ($_GET['mes_busqueda'] ?? '') == '12' ? 'selected' : '' ?>>Diciembre</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <input type="number" name="anio_busqueda" class="form-control form-select-sm" placeholder="Año" style="width: 80px;" value="<?= $_GET['anio_busqueda'] ?? '' ?>">
                    </div>

                    <div class="col">
                        <input type="text" name="texto_busqueda" class="form-control form-select-sm" placeholder="Buscar lugar o responsable..." value="<?= $_GET['texto_busqueda'] ?? '' ?>">
                    </div>

                    <div class="col-auto">
                        <button type="submit" name="buscar" class="btn btn-sm btn-primary px-3" style="background: #003366; border:none;">Buscar</button>
                        <a href="jornadas.php" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Establecimiento / Lugar</th>
                                <th>Responsables</th>
                                <!-- Se aumentó el ancho para que quepan los 4 botones -->
                                <th style="width: 180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($registros)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        No se encontraron registros de jornadas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($registros as $r): ?>
                                <tr>
                                    <td>
                                        <span class="badge-date">
                                            <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 500; text-align: left; padding-left: 20px;">
                                        <?= htmlspecialchars($r['establecimiento']) ?>
                                    </td>
                                    <td style="text-align: left;">
                                        <?= htmlspecialchars($r['responsables']) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Botón Ver (Ojo) -->
                                            <a href="ver_jornada.php?id=<?= $r['id'] ?>" class="btn-action btn-view" title="Ver Detalles">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                                            </a>

                                            <!-- Botón Editar -->
                                            <a href="editar_jornada.php?id=<?= $r['id'] ?>" class="btn-action btn-edit" title="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                                            </a>
                                            
                                            <!-- Botón Eliminar -->
                                            <a href="jornadas.php?eliminar=<?= $r['id'] ?>" class="btn-action btn-delete" 
                                               onclick="return confirm('¿Estás seguro de eliminar esta jornada?')" title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>