<?php
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
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM registros WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensaje = "Registro eliminado correctamente";
    }
}

// Búsqueda con múltiples criterios
$condicion = "";
$parametro = [];
$filtros = [];

if (isset($_GET['buscar'])) {
    // Filtro por día, mes y año
    $filtro_dia = !empty($_GET['dia_busqueda']);
    $filtro_mes = !empty($_GET['mes_busqueda']);
    $filtro_anio = !empty($_GET['anio_busqueda']);
    
    // Si se especifica día, debe tener mes y año
    if ($filtro_dia && $filtro_mes && $filtro_anio) {
        $filtros[] = "DAY(fecha) = :dia AND MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        $parametro[':dia'] = $_GET['dia_busqueda'];
        $parametro[':mes'] = $_GET['mes_busqueda'];
        $parametro[':anio'] = $_GET['anio_busqueda'];
    } 
    // Filtro por mes y año (sin día)
    elseif ($filtro_mes && $filtro_anio) {
        $filtros[] = "MONTH(fecha) = :mes AND YEAR(fecha) = :anio";
        $parametro[':mes'] = $_GET['mes_busqueda'];
        $parametro[':anio'] = $_GET['anio_busqueda'];
    } 
    // Filtro solo por mes
    elseif ($filtro_mes) {
        $filtros[] = "MONTH(fecha) = :mes";
        $parametro[':mes'] = $_GET['mes_busqueda'];
    } 
    // Filtro solo por año
    elseif ($filtro_anio) {
        $filtros[] = "YEAR(fecha) = :anio";
        $parametro[':anio'] = $_GET['anio_busqueda'];
    }
    
    // Filtro por texto (busca en establecimiento o responsable)
    if (!empty($_GET['texto_busqueda'])) {
        $filtros[] = "(establecimiento LIKE :texto OR responsable LIKE :texto)";
        $parametro[':texto'] = '%' . $_GET['texto_busqueda'] . '%';
    }
    
    // Combinar filtros
    if (!empty($filtros)) {
        $condicion = "WHERE " . implode(" AND ", $filtros);
    }
}

// Obtener registros
$sql = "SELECT * FROM registros $condicion ORDER BY fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($parametro);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Registros</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <style>
        body { background-color: #FFFFFF; }
        .navbar { background-color: #87CEEB !important; }
        .contenedor-tabla {
            margin: 20px auto;
            padding: 0 15px;
        }
        .acciones-cell { width: 150px; }
    
        /* Bordes más gruesos */
        .table-bordered {
            border-width: 2px !important;
        }
        .table-bordered th,
        .table-bordered td {
            border-width: 1px !important;
            border-color: #212529 !important;
        }
        .table-bordered thead th {
            border-bottom-width: 2px !important;
        }
        
        /* Texto centrado en toda la tabla */
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        
        /* Scroll para el cuerpo de la tabla */
        .tabla-contenedor {
            max-height: 400px; /* Altura para 6 registros aprox */
            overflow-y: auto;
        }
        
        /* Cabecera fija */
        thead tr {
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);
        }
        
        /* Estilo para los campos de búsqueda */
        .campo-busqueda {
            width: auto; /* Ancho automático */
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        /* Barra de texto más ancha */
        .texto-busqueda {
            width: 250px; /* Más ancho para texto */
        }
        
        /* Select más angostos */
        .dia-select, .mes-select, .anio-input {
            width: 80px;
        }
        
        /* Contenedor flexible para campos de fecha */
        .filtros-fecha {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
    </style>
</head>
<body>
    <!-- Navegacion -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Sistema de Registros</a>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- BARRA DE BÚSQUEDA Y BOTÓN NUEVO -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" class="d-flex flex-wrap align-items-center">
                    <div class="filtros-fecha me-2 mb-2">
                        <!-- Filtro por día -->
                        <select name="dia_busqueda" class="form-select campo-busqueda dia-select">
                            <option value="">Día</option>
                            <?php for ($d = 1; $d <= 31; $d++): ?>
                                <option value="<?= $d ?>" <?= isset($_GET['dia_busqueda']) && $_GET['dia_busqueda'] == $d ? 'selected' : '' ?>>
                                    <?= $d ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        
                        <!-- Filtro por mes -->
                        <select name="mes_busqueda" class="form-select campo-busqueda mes-select">
                            <option value="">Mes</option>
                            <option value="1" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '1' ? 'selected' : '' ?>>Ene</option>
                            <option value="2" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '2' ? 'selected' : '' ?>>Feb</option>
                            <option value="3" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '3' ? 'selected' : '' ?>>Mar</option>
                            <option value="4" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '4' ? 'selected' : '' ?>>Abr</option>
                            <option value="5" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '5' ? 'selected' : '' ?>>May</option>
                            <option value="6" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '6' ? 'selected' : '' ?>>Jun</option>
                            <option value="7" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '7' ? 'selected' : '' ?>>Jul</option>
                            <option value="8" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '8' ? 'selected' : '' ?>>Ago</option>
                            <option value="9" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '9' ? 'selected' : '' ?>>Sep</option>
                            <option value="10" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '10' ? 'selected' : '' ?>>Oct</option>
                            <option value="11" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '11' ? 'selected' : '' ?>>Nov</option>
                            <option value="12" <?= isset($_GET['mes_busqueda']) && $_GET['mes_busqueda'] == '12' ? 'selected' : '' ?>>Dic</option>
                        </select>
                        
                        <!-- Filtro por año -->
                        <input type="number" name="anio_busqueda" min="2000" max="2099" step="1" 
                               class="form-control campo-busqueda anio-input"
                               placeholder="Año"
                               value="<?= $_GET['anio_busqueda'] ?? '' ?>">
                    </div>
                    
                    <!-- Campo de búsqueda única para establecimiento y responsable -->
                    <input type="text" name="texto_busqueda" class="form-control campo-busqueda texto-busqueda me-2 mb-2"
                           placeholder="Buscar establecimiento o responsable" 
                           value="<?= $_GET['texto_busqueda'] ?? '' ?>">
                    
                           <!-- Boton de limpiar busqueda -->
                    <div class="d-flex mb-2">
                        <button type="submit" name="buscar" class="btn btn-primary me-2">Buscar</button>
                        <a href="?" class="btn btn-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="nuevo_registro.php" class="btn btn-success">Nuevo Registro</a>
            </div>
        </div>

        <!-- TABLA DE REGISTROS CON SCROLL -->
        <div class="contenedor-tabla">
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-info"><?= $mensaje ?></div>
            <?php endif; ?>
            
            <div class="tabla-contenedor">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Establecimiento</th>
                            <th>Responsable</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No se encontraron registros</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registros as $r): ?>
                            <tr>
                                <!-- Formatear fecha como día/mes/año -->
                                <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                                <td><?= htmlspecialchars($r['establecimiento']) ?></td>
                                <td><?= htmlspecialchars($r['responsable']) ?></td>
                                <td class="acciones-cell">
                                    <a href="editar.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="generar_pdf.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">PDF</a>
                                    <a href="?eliminar=<?= $r['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>