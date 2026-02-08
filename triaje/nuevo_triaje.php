<?php
session_start();
if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ambulatorio");
$conn->set_charset("utf8mb4");

// Función de Evaluación (Motor de Reglas)
function evaluarCondicionMedica($valor_paciente, $condicion_regla) {
    if ($valor_paciente === null || trim($valor_paciente) === '') {
        return false; 
    }

    preg_match('/^(<=|>=|<|>|=)?\s*(.*)$/', $condicion_regla, $matches);
    $operador = $matches[1] ?: '=';
    $valor_referencia = $matches[2];

    if (!is_numeric($valor_paciente) || !is_numeric($valor_referencia)) {
        return ($operador === '=') ? (trim($valor_paciente) == trim($valor_referencia)) : false;
    }

    $v_paciente = (float)$valor_paciente;
    $v_ref = (float)$valor_referencia;

    switch ($operador) {
        case '>':  return $v_paciente > $v_ref;
        case '<':  return $v_paciente < $v_ref;
        case '>=': return $v_paciente >= $v_ref;
        case '<=': return $v_paciente <= $v_ref;
        case '=':  return $v_paciente == $v_ref;
        default:   return false;
    }
}

// Captura de variables
$cedula = $_POST['cedula'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
// EDAD NO OBLIGATORIA: Si está vacía se guarda como null
$edad = (isset($_POST['edad']) && trim($_POST['edad']) !== '') ? $_POST['edad'] : null;

$ta_sist     = (isset($_POST['ta_sist']) && trim($_POST['ta_sist']) !== '') ? $_POST['ta_sist'] : null;
$ta_diast    = (isset($_POST['ta_diast']) && trim($_POST['ta_diast']) !== '') ? $_POST['ta_diast'] : null;
$fc          = (isset($_POST['fc']) && trim($_POST['fc']) !== '') ? $_POST['fc'] : null;
$fr          = (isset($_POST['fr']) && trim($_POST['fr']) !== '') ? $_POST['fr'] : null;
$temperatura = (isset($_POST['temperatura']) && trim($_POST['temperatura']) !== '') ? $_POST['temperatura'] : null;
$saturacion  = (isset($_POST['saturacion']) && trim($_POST['saturacion']) !== '') ? $_POST['saturacion'] : null;
$dolor       = (isset($_POST['dolor']) && trim($_POST['dolor']) !== '') ? $_POST['dolor'] : null;

$conciencia = $_POST['conciencia'] ?? 'ALERTA';
$motivo = $_POST['motivo'] ?? '';
$observaciones = $_POST['observaciones'] ?? '';
$prioridad_final = $_POST['prioridad_final'] ?? '';
$razon_sobrescrito = $_POST['razon_sobrescrito'] ?? '';

$mensaje = ''; $prioridad_calculada = ''; $mostrar_resultado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // MOTOR DE REGLAS
    $prioridad_calculada = 'VERDE';
    $res_reglas = $conn->query("SELECT prioridad, condiciones FROM reglas_triaje WHERE activo = 1 ORDER BY CASE prioridad WHEN 'ROJO' THEN 1 WHEN 'NARANJA' THEN 2 ELSE 3 END");
    while ($regla = $res_reglas->fetch_assoc()) {
        $condiciones = json_decode($regla['condiciones'], true);
        $cumple_regla = true;
        foreach ($condiciones as $campo_idx => $val_cond) {
            $campo_limpio = preg_replace('/_\d+$/', '', $campo_idx);
            if (!evaluarCondicionMedica($_POST[$campo_limpio] ?? '', $val_cond)) { $cumple_regla = false; break; }
        }
        if ($cumple_regla) { $prioridad_calculada = $regla['prioridad']; break; }
    }
    $mostrar_resultado = true;

    if (isset($_POST['guardar'])) {
        $stmt_pac = $conn->prepare("SELECT id FROM pacientes WHERE cedula = ?");
        $stmt_pac->bind_param("s", $cedula); $stmt_pac->execute();
        $res_pac = $stmt_pac->get_result();
        if ($row_p = $res_pac->fetch_assoc()) { $paciente_id = $row_p['id']; } 
        else {
            $stmt_ins = $conn->prepare("INSERT INTO pacientes (cedula, nombres, apellidos) VALUES (?, ?, ?)");
            $stmt_ins->bind_param("sss", $cedula, $nombres, $apellidos); $stmt_ins->execute();
            $paciente_id = $conn->insert_id;
        }

        $p_final = !empty($prioridad_final) ? $prioridad_final : $prioridad_calculada;

        $sql1 = "INSERT INTO triaje (
            paciente_id, edad, ta_sist, ta_diast, fc, fr, 
            temperatura, saturacion, dolor, usuario_id, fecha_triaje
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("iiiiiiidii", 
            $paciente_id, $edad, $ta_sist, $ta_diast, $fc, $fr, $temperatura, $saturacion, $dolor, $_SESSION['id_usu']
        );
        
        if ($stmt1->execute()) {
            $nuevo_triaje_id = $conn->insert_id;
            $sql2 = "UPDATE triaje SET 
                conciencia = ?, motivo_consulta = ?, observaciones = ?, 
                prioridad_calculada = ?, prioridad_final = ?, razon_sobrescrito = ? 
                WHERE id = ?";
            
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("ssssssi", 
                $conciencia, $motivo, $observaciones, $prioridad_calculada, $p_final, $razon_sobrescrito, $nuevo_triaje_id
            );
            $stmt2->execute();

            $mensaje = '<div class="alert alert-success fw-bold text-center">Triaje guardado, redirigiendo...</div>';
            echo "<script>setTimeout(() => { window.location.href = 'triaje.php'; }, 2000);</script>";
        } else {
            $mensaje = '<div class="alert alert-danger">Error: ' . $conn->error . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Triaje</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <style>
        body { background: #f1f5f9; padding-top: 70px; }
        .navbar { background-color: #aa0b0b; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .form-text { font-size: 0.8rem; color: #6c757d; }
        .prio-box { font-size: 2.2rem; font-weight: 800; padding: 15px; border-radius: 12px; display: inline-block; }
        .form-select {
            background-repeat: no-repeat;
            background-position: right .75rem center;
            background-size: 16px 12px;
            padding-right: 2.25rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow">
    <div class="container-fluid">
        <span class="navbar-brand text-white">Ambulatorio Urbano I Libertador</span>
        <a class="nav-link text-white border rounded px-3" href="triaje.php">Regresar</a>
    </div>
</nav>

<div class="container py-4">
    <h2 class="text-danger fw-bold">Nuevo Triaje Médico</h2><hr>
    <?= $mensaje ?>

    <form method="POST">
        <div class="card">
            <div class="card-header bg-white fw-bold text-primary">I. Datos del Paciente</div>
            <div class="card-body row g-3">
                <div class="col-md-3"><label>Cédula</label><input type="text" name="cedula" class="form-control" value="<?= $cedula ?>" required><div class="form-text">Ej: 25123456</div></div>
                <div class="col-md-3"><label>Nombres</label><input type="text" name="nombres" class="form-control" value="<?= $nombres ?>" required><div class="form-text">Ej: Juan Carlos</div></div>
                <div class="col-md-3"><label>Apellidos</label><input type="text" name="apellidos" class="form-control" value="<?= $apellidos ?>" required><div class="form-text">Ej: Pérez</div></div>
                <div class="col-md-3"><label>Edad</label><input type="number" name="edad" class="form-control" value="<?= $edad ?>"><div class="form-text">Ej: 25 (Opcional)</div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white fw-bold text-primary">II. Constantes Vitales</div>
            <div class="card-body row g-3">
                <div class="col-md-3"><label>T.A. Sistólica</label><input type="number" name="ta_sist" class="form-control" value="<?= $ta_sist ?>"><div class="form-text">Ej: 120</div></div>
                <div class="col-md-3"><label>T.A. Diastólica</label><input type="number" name="ta_diast" class="form-control" value="<?= $ta_diast ?>"><div class="form-text">Ej: 80</div></div>
                <div class="col-md-3"><label>Frec. Cardíaca</label><input type="number" name="fc" class="form-control" value="<?= $fc ?>"><div class="form-text">Ej: 75</div></div>
                <div class="col-md-3"><label>Frec. Respiratoria</label><input type="number" name="fr" class="form-control" value="<?= $fr ?>"><div class="form-text">Ej: 18</div></div>
                <div class="col-md-3"><label>Temperatura</label><input type="number" step="0.1" name="temperatura" class="form-control" value="<?= $temperatura ?>"><div class="form-text">Ej: 37.0</div></div>
                <div class="col-md-3"><label>Saturación O₂</label><input type="number" name="saturacion" class="form-control" value="<?= $saturacion ?>"><div class="form-text">Ej: 98</div></div>
                <div class="col-md-3"><label>Dolor (0-10)</label><input type="number" name="dolor" class="form-control" value="<?= $dolor ?>"><div class="form-text">Ej: 5</div></div>
                <div class="col-md-3">
                    <label>Conciencia</label>
                    <select name="conciencia" class="form-select">
                        <option value="ALERTA" <?= $conciencia=='ALERTA'?'selected':'' ?>>ALERTA</option>
                        <option value="SOMNOLIENTO" <?= $conciencia=='SOMNOLIENTO'?'selected':'' ?>>SOMNOLIENTO</option>
                        <option value="RESPONDE_A_VOZ" <?= $conciencia=='RESPONDE_A_VOZ'?'selected':'' ?>>RESPONDE A VOZ</option>
                        <option value="RESPONDE_A_DOLOR" <?= $conciencia=='RESPONDE_A_DOLOR'?'selected':'' ?>>RESPONDE A DOLOR</option>
                        <option value="INCONSCIENTE" <?= $conciencia=='INCONSCIENTE'?'selected':'' ?>>INCONSCIENTE</option>
                    </select>
                    <div class="form-text">Seleccione estado del paciente</div>
                </div>
                <div class="col-md-6"><label>Motivo de Consulta</label><textarea name="motivo" class="form-control" rows="2"><?= $motivo ?></textarea></div>
                <div class="col-md-6"><label>Observaciones Médicas</label><textarea name="observaciones" class="form-control" rows="2"><?= $observaciones ?></textarea></div>
            </div>
        </div>

        <div class="text-center my-4"><button type="submit" name="calcular" class="btn btn-primary btn-lg px-5">Analizar Triaje</button></div>

        <?php if ($mostrar_resultado): ?>
        <div class="card text-center py-4 border-<?= $prioridad_calculada=='ROJO'?'danger':($prioridad_calculada=='NARANJA'?'warning':'success') ?>">
            <h4 class="text-muted">Resultado Sugerido</h4>
            <div class="my-3"><span class="prio-box <?= $prioridad_calculada=='ROJO'?'bg-danger text-white':($prioridad_calculada=='NARANJA'?'bg-warning text-dark':'bg-success text-white') ?>"><?= $prioridad_calculada ?></span></div>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <label class="fw-bold">Validación Final</label>
                    <select name="prioridad_final" class="form-select mb-2">
                        <option value="">Confirmar sugerida</option>
                        <option value="ROJO">ROJO</option>
                        <option value="NARANJA">NARANJA</option>
                        <option value="VERDE">VERDE</option>
                    </select>
                    <input type="text" name="razon_sobrescrito" class="form-control mb-3" placeholder="Nota opcional">
                    <button type="submit" name="guardar" class="btn btn-success w-100 shadow">Guardar Registro</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </form>
</div>
</body>
</html>