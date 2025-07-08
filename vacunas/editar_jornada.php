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

// Obtener el ID de la jornada
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de jornada no válido.');
}
$jornada_id = intval($_GET['id']);

// Cargar datos de la jornada
$stmt = $pdo->prepare('SELECT * FROM jornadas WHERE id = ?');
$stmt->execute([$jornada_id]);
$jornada = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$jornada) {
    die('Jornada no encontrada.');
}

// Cargar pacientes de la jornada
$stmt = $pdo->prepare('SELECT * FROM jornada_pacientes WHERE jornada_id = ?');
$stmt->execute([$jornada_id]);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar lotes de la jornada
$stmt = $pdo->prepare('SELECT * FROM jornada_lotes WHERE jornada_id = ?');
$stmt->execute([$jornada_id]);
$lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Indexar lotes por nombre para facilitar el llenado
$lotes_map = [];
$lotes_otros = [];
foreach ($lotes as $lote) {
    if (isset($lote['biologico']) && $lote['biologico'] === 'OTRO') {
        $lotes_otros[] = $lote;
    } else if (isset($lote['biologico'])) {
        $lotes_map[$lote['biologico']] = $lote;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Registro de Vacunación</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="registro_vacunas.css">
</head>
<body>

            <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg fixed-top shadow">
        <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../icons/logo.png" alt="Logo Ambulatorio" style="height: 40px; margin-right: 10px;" />
            Ambulatorio Urbano I Libertador
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="../dashboard.php">Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="jornada.php">Atras</a></li>
            </ul>
        </div>
        </div>
    </nav>
    <!-- Espacio para que el contenido no quede debajo de la navbar fija -->
    <div style="height: 70px;"></div>

    <div class="container">
        <div class="notification" id="notification" style="display:none;">
            <i class="fas fa-check-circle"></i> <span id="notification-text"></span>
        </div>
        <div class="header">
            <h1>DIRECTOR GENERAL DE EPIDEMIOLOGÍA</h1>
            <h1>DIRECTOR DE INMUNIZACIONES</h1>
            <h1>SISTEMA DE INFORMACIÓN DEL PROGRAMA AMPLIADO DE INMUNIZACIONES</h1>
            <h2>REGISTRO DIARIO DE VACUNACIÓN</h2>
        </div>
        
        <form id="vaccination-form" method="POST" action="actualizar.php">
            <input type="hidden" name="jornada_id" value="<?php echo isset($jornada_id) ? htmlspecialchars($jornada_id) : ''; ?>">
            <div class="form-section">
                <div class="form-row">
                    <div class="form-field">
                        <span>1. FECHA:</span>
                        <input type="date" name="fecha" required value="<?php echo htmlspecialchars($jornada['fecha']); ?>">
                    </div>
                    <div class="form-field">
                        <span>2. ÁSIG:</span>
                        <input type="text" name="asig" maxlength="10" required value="<?php echo htmlspecialchars($jornada['asig']); ?>">
                    </div>
                    <div class="form-field">
                        <span>3. Código:</span>
                        <input type="text" name="codigo" required value="<?php echo htmlspecialchars($jornada['codigo']); ?>">
                    </div>
                    <div class="form-field">
                        <span>4. Establecimiento:</span>
                        <input type="text" name="establecimiento" required value="<?php echo htmlspecialchars($jornada['establecimiento']); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <span>5. Responsables:</span>
                        <input type="text" name="responsables" required value="<?php echo htmlspecialchars($jornada['responsables']); ?>">
                    </div>
                    <div class="form-field">
                        <span>6. Hora Inicio:</span>
                        <input type="time" name="hora_inicio" required value="<?php echo htmlspecialchars($jornada['hora_inicio']); ?>">
                    </div>
                    <div class="form-field">
                        <span>7. Hora Fin:</span>
                        <input type="time" name="hora_fin" required value="<?php echo htmlspecialchars($jornada['hora_fin']); ?>">
                    </div>
                </div>
                <div class="estrategia-container">
                    <div class="form-field">
                        <span>8. Estrategia de vacunación:</span>
                    </div>
                    <div class="estrategia-buttons">
                        <?php $estrategias = ['rutina','intramural','jornada','extramural']; foreach($estrategias as $estrat): ?>
                        <button type="button" class="estrategia-btn<?php if($jornada['estrategia']===$estrat) echo ' active'; ?>" data-estrategia="<?php echo $estrat; ?>"><?php echo ucfirst($estrat); ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="estrategia-donde">
                        <span>Donde:</span>
                        <input type="text" name="estrategia_donde" placeholder="Escriba el lugar" value="<?php echo htmlspecialchars($jornada['estrategia_donde']); ?>">
                    </div>
                    <input type="hidden" name="estrategia" value="<?php echo htmlspecialchars($jornada['estrategia']); ?>">
                </div>
            </div>
            <div class="table-container">
                <table id="vaccination-table">
                    <thead>
                        <tr class="encabezado-principal">
                            <th class="serial-col">Nº</th>
                            <th class="nombre-col">9. NOMBRE</th>
                            <th class="apellido-col">10. APELLIDO</th>
                            <th class="fecha-col">11. FECHA NACIMIENTO</th>
                            <th class="nacionalidad-col">12. NACIONALIDAD</th>
                            <th class="documento-col">13. N° CÉDULA</th>
                            <th class="hijo-col">14. ORDEN HIJO</th>
                            <th class="direccion-col">15. DIRECCIÓN</th>
                            <th class="etnia-col">16. PUEBLO/ETNIA</th>
                            <th class="edad-col">17. EDAD</th>
                            <th class="sexo-col">18. SEXO</th>
                            <th class="grupo-col">19. GRUPOS ESPECIALES</th>
                            
                            <!-- Encabezado de vacunas -->
                            <th colspan="15" class="vacunas-header">20. VACUNAS</th>
                            
                            <th class="obs-col">21. OBSERVACIONES</th>
                            <th class="acciones-col">ACCIONES</th>
                        </tr>
                        <tr class="encabezado-secundario">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <!-- Columnas de vacunas individuales -->
                            <th class="vacuna-col sticky-vacuna">BCG</th>
                            <th class="vacuna-col sticky-vacuna">Hepatitis B</th>
                            <th class="vacuna-col sticky-vacuna">Rotavirus</th>
                            <th class="vacuna-col sticky-vacuna">Pentavalente</th>
                            <th class="vacuna-col sticky-vacuna">Polio Inyectable</th>
                            <th class="vacuna-col sticky-vacuna">Polio Oral</th>
                            <th class="vacuna-col sticky-vacuna">Neumococo Conjugada</th>
                            <th class="vacuna-col sticky-vacuna">Influenza Estacional</th>
                            <th class="vacuna-col sticky-vacuna">Fiebre Amarilla</th>
                            <th class="vacuna-col sticky-vacuna">SARAMPIÓN/ RUBÉOLA/ PAROTIDITIS</th>
                            <th class="vacuna-col sticky-vacuna">Toxoide Tetánico Diftérico</th>
                            <th class="vacuna-col sticky-vacuna">Neumococo Polisacárida</th>
                            <th class="vacuna-col sticky-vacuna">Meningocócica B-C</th>
                            <th class="vacuna-col sticky-vacuna">Anti-Rábica Humana PRE</th>
                            <th class="vacuna-col sticky-vacuna">Anti-Rábica Humana POST</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pacientes) === 0): ?>
                        <tr>
                            <td class="serial">1</td>
                            <td><input type="text" name="nombre[]" required></td>
                            <td><input type="text" name="apellido[]" required></td>
                            <td><input type="date" name="fecha_nacimiento[]" required></td>
                            <td>
                                <select name="nacionalidad[]">
                                    <option value="">Seleccionar</option>
                                    <option value="Ecuatoriana">Ecuatoriana</option>
                                    <option value="Colombiana">Colombiana</option>
                                    <option value="Peruana">Peruana</option>
                                    <option value="Venezolana">Venezolana</option>
                                    <option value="Otra">Otra</option>
                                </select>
                            </td>
                            <td><input type="text" name="documento[]" required></td>
                            <td><input type="number" name="orden_hijo[]" min="1" max="20"></td>
                            <td><input type="text" name="direccion[]"></td>
                            <td><input type="text" name="etnia[]"></td>
                            <td><input type="number" name="edad[]" min="0" max="120"></td>
                            <td>
                                <select name="sexo[]">
                                    <option value="M">M</option>
                                    <option value="F">F</option>
                                </select>
                            </td>
                            <td><input type="text" name="grupo_especial[]"></td>
                            
                            <!-- Columnas para cada vacuna -->
                            <!-- BCG -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
                                </div>
                                <input type="hidden" name="dosis_bcg[]" value="">
                            </td>
                            <!-- HEPATITIS B -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                </div>
                                <input type="hidden" name="dosis_hepb[]" value="">
                            </td>
                            <!-- ROTAVIRUS -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                </div>
                                <input type="hidden" name="dosis_rotavirus[]" value="">
                            </td>
                            <!-- PENTAVALENTE -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                    <button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button>
                                </div>
                                <input type="hidden" name="dosis_pentavalente[]" value="">
                            </td>
                            <!-- POLIO INYECTABLE -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                </div>
                                <input type="hidden" name="dosis_polio_iny[]" value="">
                            </td>
                            <!-- POLIO ORAL -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                    <button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                </div>
                                <input type="hidden" name="dosis_polio_oral[]" value="">
                            </td>
                            <!-- NEUMOCOCO CONJUGADA -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                </div>
                                <input type="hidden" name="dosis_neumo_conj[]" value="">
                            </td>
                            <!-- INFLUENZA ESTACIONAL -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d12" data-dosis="DE">DE</button>
                                </div>
                                <input type="hidden" name="dosis_influenza[]" value="">
                            </td>
                            <!-- FIEBRE AMARILLA -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
                                </div>
                                <input type="hidden" name="dosis_fiebre_ama[]" value="">
                            </td>
                            <!-- SARAMPIÓN RUBÉOLA PAROTIDITIS (SRP) -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                </div>
                                <input type="hidden" name="dosis_srp[]" value="">
                            </td>
                            <!-- TOXOIDE TETÁNICO DIFTÉRICO -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d4" data-dosis="4D">4D</button>
                                    <button type="button" class="dosis-btn d5" data-dosis="5D">5D</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                </div>
                                <input type="hidden" name="dosis_toxoide[]" value="">
                            </td>
                            <!-- NEUMOCOCO POLISACÁRIDA -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                </div>
                                <input type="hidden" name="dosis_neumo_poli[]" value="">
                            </td>
                            <!-- MENINGOCÓCICA B-C -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                </div>
                                <input type="hidden" name="dosis_meningo[]" value="">
                            </td>
                            <!-- ANTI-RÁBICA HUMANA PRE -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                </div>
                                <input type="hidden" name="dosis_rabia_pre[]" value="">
                            </td>
                            <!-- ANTI-RÁBICA HUMANA POST -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d4" data-dosis="4D">4D</button>
                                    <button type="button" class="dosis-btn d5" data-dosis="5D">5D</button>
                                    <button type="button" class="dosis-btn d6" data-dosis="6D">6D</button>
                                    <button type="button" class="dosis-btn d7" data-dosis="7D">7D</button>
                                </div>
                                <input type="hidden" name="dosis_rabia_post[]" value="">
                            </td>
                            <td><input type="text" name="observaciones[]"></td>
                            <td>
                                <button type="button" class="delete-row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pacientes as $i => $p): ?>
<tr>
    <td class="serial"><?php echo $i+1; ?></td>
    <td><input type="text" name="nombre[]" required value="<?php echo htmlspecialchars($p['nombre']); ?>"></td>
    <td><input type="text" name="apellido[]" required value="<?php echo htmlspecialchars($p['apellido']); ?>"></td>
    <td><input type="date" name="fecha_nacimiento[]" required value="<?php echo htmlspecialchars($p['fecha_nacimiento']); ?>"></td>
    <td>
        <select name="nacionalidad[]">
            <option value="">Seleccionar</option>
            <?php $nacs = ['Ecuatoriana','Colombiana','Peruana','Venezolana','Otra']; foreach($nacs as $nac): ?>
            <option value="<?php echo $nac; ?>"<?php if($p['nacionalidad']===$nac) echo ' selected'; ?>><?php echo $nac; ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input type="text" name="documento[]" required value="<?php echo htmlspecialchars($p['documento']); ?>"></td>
    <td><input type="number" name="orden_hijo[]" min="1" max="20" value="<?php echo ($p['orden_hijo'] === null || $p['orden_hijo'] === '' || $p['orden_hijo'] == 0) ? '' : htmlspecialchars($p['orden_hijo']); ?>"></td>
    <td><input type="text" name="direccion[]" value="<?php echo htmlspecialchars($p['direccion']); ?>"></td>
    <td><input type="text" name="etnia[]" value="<?php echo htmlspecialchars($p['etnia']); ?>"></td>
    <td><input type="number" name="edad[]" min="0" max="120" value="<?php echo htmlspecialchars($p['edad']); ?>"></td>
    <td>
        <select name="sexo[]">
            <option value="M"<?php if($p['sexo']==='M') echo ' selected'; ?>>M</option>
            <option value="F"<?php if($p['sexo']==='F') echo ' selected'; ?>>F</option>
        </select>
    </td>
    <td><input type="text" name="grupo_especial[]" value="<?php echo htmlspecialchars($p['grupo_especial']); ?>"></td>
    <?php
    // Vacunas y dosis
    $vacunas = [
        'bcg','hepb','rotavirus','pentavalente','polio_iny','polio_oral','neumo_conj','influenza','fiebre_ama','srp','toxoide','neumo_poli','meningo','rabia_pre','rabia_post'
    ];
    $dosis_btns = [
        'bcg' => [['DU','d11']],
        'hepb' => [['DU','d11'],['DA','d10'],['1D','d1'],['2D','d2'],['3D','d3']],
        'rotavirus' => [['1D','d1'],['2D','d2']],
        'pentavalente' => [['1D','d1'],['2D','d2'],['3D','d3'],['1REF','d8'],['2REF','d9']],
        'polio_iny' => [['1D','d1'],['2D','d2'],['3D','d3']],
        'polio_oral' => [['1D','d1'],['2D','d2'],['3D','d3'],['1REF','d8'],['2REF','d9'],['DA','d10']],
        'neumo_conj' => [['1D','d1'],['2D','d2'],['1REF','d8']],
        'influenza' => [['1D','d1'],['2D','d2'],['DE','d12']],
        'fiebre_ama' => [['DU','d11']],
        'srp' => [['1D','d1'],['2D','d2'],['DA','d10']],
        'toxoide' => [['1D','d1'],['2D','d2'],['3D','d3'],['4D','d4'],['5D','d5'],['DA','d10']],
        'neumo_poli' => [['1D','d1'],['1REF','d8']],
        'meningo' => [['1D','d1'],['2D','d2']],
        'rabia_pre' => [['1D','d1'],['2D','d2'],['1REF','d8']],
        'rabia_post' => [['1D','d1'],['2D','d2'],['3D','d3'],['4D','d4'],['5D','d5'],['6D','d6'],['7D','d7']],
    ];
    foreach ($vacunas as $vac):
        $dosis = $p['dosis_'.$vac];
    ?>
    <td>
        <div class="dosis-container">
            <?php foreach($dosis_btns[$vac] as $btn): ?>
                <button type="button" class="dosis-btn <?php echo $btn[1]; ?>" data-dosis="<?php echo $btn[0]; ?>"><?php echo $btn[0]; ?></button>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="dosis_<?php echo $vac; ?>[]" value="<?php echo htmlspecialchars($dosis); ?>">
    </td>
    <?php endforeach; ?>
    <td><input type="text" name="observaciones[]" value="<?php echo htmlspecialchars($p['observaciones']); ?>"></td>
    <td>
        <button type="button" class="delete-row"><i class="fas fa-trash"></i></button>
    </td>
</tr>
<?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="action-buttons" style="margin-bottom: 10px;">
                <button type="button" id="add-row" class="action-btn add-btn">
                    <i class="fas fa-plus-circle"></i> Agregar Fila
                </button>
            </div>

            <!-- Tabla de Números de Lote y Fechas de Vencimiento en el orden de la foto -->
            <h3 style="margin-top: 30px;">22. NÚMEROS DE LOTE Y FECHAS DE VENCIMIENTO</h3>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="tabla-lotes" border="1" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>BIOLÓGICO</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                                <th>BIOLÓGICO</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                                <th>BIOLÓGICO</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                                <th>BIOLÓGICO (OTROS)</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
$biologicos = [
    ['BCG','POLIO INYECTABLE','SARAMPIÓN/RUBÉOLA/PAROTIDITIS','OTRO'],
    ['HEPATITIS B','POLIO ORAL','TOXOIDE TETÁNICO DIFTÉRICO','OTRO'],
    ['HEPATITIS B (PEDIÁTRICO)','NEUMOCOCO CONJUGADA','NEUMOCOCO POLISACÁRIDA','OTRO'],
    ['ROTAVIRUS','INFLUENZA ESTACIONAL','MENINGOCÓCICA B-C','OTRO'],
    ['PENTAVALENTE','FIEBRE AMARILLA','RABIA HUMANA','OTRO'],
];
for ($fila = 0; $fila < 5; $fila++): ?>
<tr>
<?php for ($col = 0; $col < 4; $col++):
    $bio = $biologicos[$fila][$col];
    if ($bio === 'OTRO'):
        $lote_otro = isset($lotes_otros[$fila]) ? $lotes_otros[$fila] : ['nombre'=>'','lote'=>'','vencimiento'=>'','perdidas'=>'']; ?>
        <td><input type="text" name="lote_otro_nombre[]" value="<?php echo htmlspecialchars($lote_otro['nombre']); ?>"></td>
        <td><input type="text" name="lote_otro_lote[]" value="<?php echo htmlspecialchars($lote_otro['lote']); ?>"></td>
        <td><input type="date" name="lote_otro_venc[]" value="<?php echo htmlspecialchars($lote_otro['vencimiento']); ?>"></td>
        <td><input type="number" name="lote_otro_perdidas[]" min="0" value="<?php echo htmlspecialchars($lote_otro['perdidas']); ?>"></td>
    <?php else:
        $lote = isset($lotes_map[$bio]) ? $lotes_map[$bio] : ['lote'=>'','vencimiento'=>'','perdidas'=>''];
        $name = strtolower(str_replace([' ','/','(',')','á','é','í','ó','ú','ñ'],
            ['_','_','','','a','e','i','o','u','n'],$bio)); ?>
        <td><?php echo $bio; ?></td>
        <td><input type="text" name="lote_<?php echo $name; ?>" value="<?php echo htmlspecialchars($lote['lote']); ?>"></td>
        <td><input type="date" name="venc_<?php echo $name; ?>" value="<?php echo htmlspecialchars($lote['vencimiento']); ?>"></td>
        <td><input type="number" name="perdidas_<?php echo $name; ?>" min="0" value="<?php echo htmlspecialchars($lote['perdidas']); ?>"></td>
    <?php endif;
endfor; ?>
</tr>
<?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="action-btn save-btn">
                    <i class="fas fa-save"></i> Guardar Registro
                </button>
            </div>
        </form>
        
        <div class="footer">
            Sistema de Información del Programa Ampliado de Inmunizaciones &copy; 2023
        </div>
    </div>
    
    <div class="notification" id="notification">
        <i class="fas fa-check-circle"></i> <span id="notification-text">Registro guardado exitosamente!</span>
    </div>
    
    <script>
        // Mostrar notificación si hay mensaje en la URL
        (function() {
            function getParam(name) {
                const url = new URL(window.location.href);
                return url.searchParams.get(name);
            }
            const mensaje = getParam('mensaje');
            if (mensaje) {
                const notification = document.getElementById('notification');
                const notificationText = document.getElementById('notification-text');
                notificationText.textContent = decodeURIComponent(mensaje.replace(/\+/g, ' '));
                notification.style.display = 'block';
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                    notification.style.display = 'none';
                }, 3000);
            }
        })();

        // Función para actualizar números de fila
        function updateRowNumbers() {
            const rows = document.querySelectorAll('#vaccination-table tbody tr');
            rows.forEach((row, index) => {
                row.querySelector('.serial').textContent = index + 1;
            });
        }

        // Función para agregar eventos a botones de dosis (toggle y sincronización input oculto)
        function addDosisButtonEvents(row) {
            const dosisButtons = row.querySelectorAll('.dosis-btn');
            dosisButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Alternar clase activa
                    this.classList.toggle('active');
                    // Actualizar input oculto con todas las dosis activas
                    const hiddenInput = this.closest('td').querySelector('input[type="hidden"]');
                    const selectedDosis = Array.from(this.closest('td').querySelectorAll('.dosis-btn.active'))
                        .map(btn => btn.getAttribute('data-dosis'));
                    hiddenInput.value = selectedDosis.join(',');
                });
            });
        }

        // --- ACTIVAR BOTONES DE DOSIS SEGÚN INPUT OCULTO ---
        function activarBotonesDosisPorPaciente() {
            document.querySelectorAll('#vaccination-table tbody tr').forEach(function(row) {
                row.querySelectorAll('td').forEach(function(td) {
                    var hidden = td.querySelector('input[type="hidden"]');
                    if (hidden) {
                        var dosisArr = hidden.value ? hidden.value.split(',') : [];
                        td.querySelectorAll('.dosis-btn').forEach(function(btn) {
                            if (dosisArr.includes(btn.getAttribute('data-dosis'))) {
                                btn.classList.add('active');
                            } else {
                                btn.classList.remove('active');
                            }
                        });
                    }
                });
            });
        }

        // Ejecutar al cargar el script
        activarBotonesDosisPorPaciente();

        // Asignar eventos a botones de dosis y eliminar en filas existentes
        function asignarEventosFilasExistentes() {
            document.querySelectorAll('#vaccination-table tbody tr').forEach(function(row) {
                addDosisButtonEvents(row);
                row.querySelector('.delete-row').addEventListener('click', function() {
                    const inputs = row.querySelectorAll('input, select');
                    let isEmpty = true;
                    inputs.forEach(input => { if (input.value && input.value.trim() !== '') { isEmpty = false; } });
                    if (isEmpty || confirm('¿Está seguro que desea eliminar esta fila? Tiene datos ingresados.')) {
                        row.remove();
                        updateRowNumbers();
                        rowCount = getRowCount();
                    }
                });
            });
        }
        asignarEventosFilasExistentes();

        // Inicializar rowCount según las filas actuales
        function getRowCount() {
            return document.querySelectorAll('#vaccination-table tbody tr').length;
        }
        let rowCount = getRowCount();

        // Agregar fila
        document.getElementById('add-row').addEventListener('click', function() {
            const table = document.getElementById('vaccination-table').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            rowCount = getRowCount() + 1;
            newRow.innerHTML = `
<td class="serial">${rowCount}</td>
<td><input type="text" name="nombre[]" required></td>
<td><input type="text" name="apellido[]" required></td>
<td><input type="date" name="fecha_nacimiento[]" required></td>
<td>
    <select name="nacionalidad[]">
        <option value="">Seleccionar</option>
        <option value="Ecuatoriana">Ecuatoriana</option>
        <option value="Colombiana">Colombiana</option>
        <option value="Peruana">Peruana</option>
        <option value="Venezolana">Venezolana</option>
        <option value="Otra">Otra</option>
    </select>
</td>
<td><input type="text" name="documento[]" required></td>
<td><input type="number" name="orden_hijo[]" min="1" max="20"></td>
<td><input type="text" name="direccion[]"></td>
<td><input type="text" name="etnia[]"></td>
<td><input type="number" name="edad[]" min="0" max="120"></td>
<td>
    <select name="sexo[]">
        <option value="M">M</option>
        <option value="F">F</option>
    </select>
</td>
<td><input type="text" name="grupo_especial[]"></td>
<!-- BCG -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d11" data-dosis="DU">DU</button></div><input type="hidden" name="dosis_bcg[]" value=""></td>
<!-- Hepatitis B -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d11" data-dosis="DU">DU</button><button type="button" class="dosis-btn d10" data-dosis="DA">DA</button><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d3" data-dosis="3D">3D</button></div><input type="hidden" name="dosis_hepb[]" value=""></td>
<!-- ROTAVIRUS -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button></div><input type="hidden" name="dosis_rotavirus[]" value=""></td>
<!-- PENTAVALENTE -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d3" data-dosis="3D">3D</button><button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button><button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button></div><input type="hidden" name="dosis_pentavalente[]" value=""></td>
<!-- POLIO INYECTABLE -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d3" data-dosis="3D">3D</button></div><input type="hidden" name="dosis_polio_iny[]" value=""></td>
<!-- POLIO ORAL -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d3" data-dosis="3D">3D</button><button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button><button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button><button type="button" class="dosis-btn d10" data-dosis="DA">DA</button></div><input type="hidden" name="dosis_polio_oral[]" value=""></td>
<!-- NEUMOCOCO CONJUGADA -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button></div><input type="hidden" name="dosis_neumo_conj[]" value=""></td>
<!-- INFLUENZA ESTACIONAL -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d12" data-dosis="DE">DE</button></div><input type="hidden" name="dosis_influenza[]" value=""></td>
<!-- FIEBRE AMARILLA -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d11" data-dosis="DU">DU</button></div><input type="hidden" name="dosis_fiebre_ama[]" value=""></td>
<!-- SARAMPIÓN RUBÉOLA PAROTIDITIS (SRP) -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d10" data-dosis="DA">DA</button></div><input type="hidden" name="dosis_srp[]" value=""></td>
<!-- TOXOIDE TETÁNICO DIFTÉRICO -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d3" data-dosis="3D">3D</button><button type="button" class="dosis-btn d4" data-dosis="4D">4D</button><button type="button" class="dosis-btn d5" data-dosis="5D">5D</button><button type="button" class="dosis-btn d10" data-dosis="DA">DA</button></div><input type="hidden" name="dosis_toxoide[]" value=""></td>
<!-- NEUMOCOCO POLISACÁRIDA -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button></div><input type="hidden" name="dosis_neumo_poli[]" value=""></td>
<!-- MENINGOCÓCICA B-C -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button></div><input type="hidden" name="dosis_meningo[]" value=""></td>
<!-- ANTI-RÁBICA HUMANA PRE -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button></div><input type="hidden" name="dosis_rabia_pre[]" value=""></td>
<!-- ANTI-RÁBICA HUMANA POST -->
<td><div class="dosis-container"><button type="button" class="dosis-btn d1" data-dosis="1D">1D</button><button type="button" class="dosis-btn d2" data-dosis="2D">2D</button><button type="button" class="dosis-btn d3" data-dosis="3D">3D</button><button type="button" class="dosis-btn d4" data-dosis="4D">4D</button><button type="button" class="dosis-btn d5" data-dosis="5D">5D</button><button type="button" class="dosis-btn d6" data-dosis="6D">6D</button><button type="button" class="dosis-btn d7" data-dosis="7D">7D</button></div><input type="hidden" name="dosis_rabia_post[]" value=""></td>
<td><input type="text" name="observaciones[]"></td>
<td>
    <button type="button" class="delete-row"><i class="fas fa-trash"></i></button>
</td>
`;
            addDosisButtonEvents(newRow);
            newRow.querySelector('.delete-row').addEventListener('click', function() {
                const inputs = newRow.querySelectorAll('input, select');
                let isEmpty = true;
                inputs.forEach(input => { if (input.value && input.value.trim() !== '') { isEmpty = false; } });
                if (isEmpty || confirm('¿Está seguro que desea eliminar esta fila? Tiene datos ingresados.')) {
                    newRow.remove();
                    updateRowNumbers();
                    rowCount = getRowCount();
                }
            });
            updateRowNumbers();
        });

        // --- ESTRATEGIA ---
        // Estrategia
        var estrategia = document.querySelector('input[name="estrategia"]').value;
        if (estrategia) {
            document.querySelectorAll('.estrategia-btn').forEach(function(btn) {
                if (btn.getAttribute('data-estrategia') === estrategia) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
    </script>
    <style>
    .notification {
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: #28a745;
        color: #fff;
        padding: 16px 32px;
        border-radius: 8px;
        font-size: 1.1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.4s;
    }
    .notification.show {
        opacity: 1;
    }
    </style>
</body>
</html>
