<?php
// nuevo_triaje.php - Formulario de Triaje (Corrección: Creación de Pacientes)

session_start();
if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ambulatorio");
$conn->set_charset("utf8mb4");

// --- MOTOR DE REGLAS ---
function evaluarCondicionMedica($valor_paciente, $condicion_regla) {
    if ($valor_paciente === null || $valor_paciente === '') return false;
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

// Variables
$cedula = $_POST['cedula'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$fecha_nac = $_POST['fecha_nacimiento'] ?? ''; // NUEVO
$sexo = $_POST['sexo'] ?? '';                  // NUEVO
$edad = $_POST['edad'] ?? '';
// Signos
$ta_sist = $_POST['ta_sist'] ?? '';
$ta_diast = $_POST['ta_diast'] ?? '';
$fc = $_POST['fc'] ?? '';
$fr = $_POST['fr'] ?? '';
$temperatura = $_POST['temperatura'] ?? '';
$saturacion = $_POST['saturacion'] ?? '';
$dolor = $_POST['dolor'] ?? '0';
$conciencia = $_POST['conciencia'] ?? 'ALERTA';
// Evaluación
$motivo = $_POST['motivo'] ?? '';
$observaciones = $_POST['observaciones'] ?? '';
$prioridad_final = $_POST['prioridad_final'] ?? '';
$razon_sobrescrito = $_POST['razon_sobrescrito'] ?? '';

$mensaje = ''; 
$prioridad_calculada = ''; 
$mostrar_resultado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. MOTOR DE REGLAS
    $prioridad_calculada = 'VERDE'; 
    $res_reglas = $conn->query("SELECT prioridad, condiciones FROM reglas_triaje WHERE activo = 1 ORDER BY CASE prioridad WHEN 'ROJO' THEN 1 WHEN 'NARANJA' THEN 2 ELSE 3 END");
    
    while ($regla = $res_reglas->fetch_assoc()) {
        $condiciones = json_decode($regla['condiciones'], true);
        $cumple_regla = true;
        foreach ($condiciones as $campo_idx => $val_cond) {
            $campo_limpio = preg_replace('/_\d+$/', '', $campo_idx);
            if (!evaluarCondicionMedica($_POST[$campo_limpio] ?? '', $val_cond)) { 
                $cumple_regla = false; break; 
            }
        }
        if ($cumple_regla) { $prioridad_calculada = $regla['prioridad']; break; }
    }
    
    $mostrar_resultado = true;

    // 2. GUARDAR DATOS
    if (isset($_POST['guardar'])) {
        
        // A. GESTIÓN DEL PACIENTE (CREAR O ACTUALIZAR)
        $stmt_check = $conn->prepare("SELECT id FROM pacientes WHERE cedula = ?");
        $stmt_check->bind_param("s", $cedula);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($row = $res_check->fetch_assoc()) {
            // El paciente EXISTE: Actualizamos sus datos básicos para mantenerlo al día
            $paciente_id = $row['id'];
            $stmt_upd = $conn->prepare("UPDATE pacientes SET nombres=?, apellidos=?, fecha_nacimiento=?, sexo=? WHERE id=?");
            $stmt_upd->bind_param("ssssi", $nombres, $apellidos, $fecha_nac, $sexo, $paciente_id);
            $stmt_upd->execute();
        } else {
            // El paciente NO EXISTE: Lo creamos
            $stmt_ins = $conn->prepare("INSERT INTO pacientes (cedula, nombres, apellidos, fecha_nacimiento, sexo) VALUES (?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("sssss", $cedula, $nombres, $apellidos, $fecha_nac, $sexo);
            if ($stmt_ins->execute()) {
                $paciente_id = $conn->insert_id;
            } else {
                $mensaje = '<div class="alert alert-danger">Error creando paciente: ' . $conn->error . '</div>';
                $paciente_id = 0;
            }
        }

        // B. GUARDAR TRIAJE (Solo si tenemos ID de paciente válido)
        if ($paciente_id > 0) {
            $p_final = !empty($prioridad_final) ? $prioridad_final : $prioridad_calculada;

            $sql1 = "INSERT INTO triaje (paciente_id, edad, ta_sist, ta_diast, fc, fr, temperatura, saturacion, dolor, usuario_id, fecha_triaje) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("iiiiiiidii", $paciente_id, $edad, $ta_sist, $ta_diast, $fc, $fr, $temperatura, $saturacion, $dolor, $_SESSION['id_usu']);
            
            if ($stmt1->execute()) {
                $nuevo_triaje_id = $conn->insert_id;
                $sql2 = "UPDATE triaje SET conciencia = ?, motivo_consulta = ?, observaciones = ?, prioridad_calculada = ?, prioridad_final = ?, razon_sobrescrito = ? WHERE id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("ssssssi", $conciencia, $motivo, $observaciones, $prioridad_calculada, $p_final, $razon_sobrescrito, $nuevo_triaje_id);
                $stmt2->execute();

                $mensaje = '<div class="alert alert-success fw-bold text-center shadow-sm border-0">¡Guardado! Paciente y Triaje registrados.</div>';
                echo "<script>setTimeout(() => { window.location.href = 'triaje.php'; }, 2000);</script>";
            } else {
                $mensaje = '<div class="alert alert-danger shadow-sm">Error guardando triaje: ' . $conn->error . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nuevo Triaje | Ambulatorio</title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <style>
    :root { --primary-red: #aa0b0b; --primary-blue: #003366; --bg-light: #eef2f6; --white: #ffffff; }
    body { background: var(--bg-light); font-family: 'Segoe UI', system-ui, sans-serif; padding-bottom: 60px; }
    .navbar { background: linear-gradient(90deg, var(--primary-red) 0%, var(--primary-blue) 100%); padding: 10px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { color: white !important; font-weight: 600; font-size: 1.1rem; }
    .btn-back { color: white; border: 1px solid rgba(255,255,255,0.5); border-radius: 20px; padding: 5px 15px; font-size: 0.9rem; text-decoration: none; transition: 0.3s; }
    .btn-back:hover { background: rgba(255,255,255,0.2); color: white; }
    .main-card { background: var(--white); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; max-width: 1000px; margin: 30px auto; }
    .form-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
    .form-header h2 { color: var(--primary-blue); font-weight: 800; margin: 0; }
    .section-title { color: var(--primary-red); font-size: 0.95rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 20px; margin-bottom: 15px; border-left: 4px solid var(--primary-red); padding-left: 10px; background: #fff5f5; padding: 8px 10px; border-radius: 0 5px 5px 0; }
    .form-label { font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 4px; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid #dee2e6; padding: 10px; font-size: 0.95rem; }
    .form-control:focus, .form-select:focus { border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
    .result-box { background: #f8f9fa; border-radius: 15px; padding: 30px; margin-top: 30px; text-align: center; border: 2px dashed #ccc; animation: fadeIn 0.5s ease-in-out; }
    .prio-badge { font-size: 2.5rem; font-weight: 800; padding: 15px 40px; border-radius: 15px; display: inline-block; color: white; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .bg-rojo { background: #d32f2f; } .bg-naranja { background: #f57c00; } .bg-verde { background: #388e3c; }
    .btn-calc { background: var(--primary-blue); color: white; border: none; padding: 12px 40px; border-radius: 50px; font-weight: 600; width: 100%; transition: 0.3s; }
    .btn-calc:hover { background: #002244; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); color: white;}
    .btn-save { background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; width: 100%; font-size: 1.1rem; }
    .btn-save:hover { background: #218838; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" />
        <span>Registro de Triaje</span>
      </a>
      <div class="ms-auto">
        <a class="btn-back" href="triaje.php">← Cancelar</a>
      </div>
    </div>
  </nav>

  <div style="height: 80px;"></div>

  <div class="container">
    <div class="main-card">
        
        <div class="form-header">
            <h2>Evaluación de Paciente</h2>
            <p>Datos personales y signos vitales para clasificación.</p>
        </div>

        <?= $mensaje ?>

        <form method="POST" id="formTriaje">
            
            <div class="row g-3">
                
                <div class="col-12"><div class="section-title">Datos Personales</div></div>
                
                <div class="col-md-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" name="cedula" class="form-control" placeholder="Ej: 12345678" value="<?= htmlspecialchars($cedula) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombres</label>
                    <input type="text" name="nombres" class="form-control" value="<?= htmlspecialchars($nombres) ?>" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control" value="<?= htmlspecialchars($apellidos) ?>" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Fecha Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?= htmlspecialchars($fecha_nac) ?>" onchange="calcularEdad()">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sexo</label>
                    <select name="sexo" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="M" <?= $sexo == 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $sexo == 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Edad (Calculada)</label>
                    <input type="number" name="edad" id="edad" class="form-control bg-light" value="<?= htmlspecialchars($edad) ?>" readonly>
                </div>

                <div class="col-12"><div class="section-title">Signos Vitales</div></div>
                
                <div class="col-md-2">
                    <label class="form-label">T.A. Sistólica</label>
                    <input type="number" name="ta_sist" class="form-control" placeholder="mmHg" value="<?= htmlspecialchars($ta_sist) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">T.A. Diastólica</label>
                    <input type="number" name="ta_diast" class="form-control" placeholder="mmHg" value="<?= htmlspecialchars($ta_diast) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Frec. Cardíaca</label>
                    <input type="number" name="fc" class="form-control" placeholder="lpm" value="<?= htmlspecialchars($fc) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Frec. Resp.</label>
                    <input type="number" name="fr" class="form-control" placeholder="rpm" value="<?= htmlspecialchars($fr) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Temp. (°C)</label>
                    <input type="number" step="0.1" name="temperatura" class="form-control" value="<?= htmlspecialchars($temperatura) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sat. O₂ (%)</label>
                    <input type="number" name="saturacion" class="form-control" value="<?= htmlspecialchars($saturacion) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nivel de Conciencia</label>
                    <select name="conciencia" class="form-select">
                        <option value="ALERTA" <?= $conciencia=='ALERTA'?'selected':'' ?>>Alerta (Normal)</option>
                        <option value="SOMNOLIENTO" <?= $conciencia=='SOMNOLIENTO'?'selected':'' ?>>Somnoliento</option>
                        <option value="RESPONDE_A_VOZ" <?= $conciencia=='RESPONDE_A_VOZ'?'selected':'' ?>>Responde a Voz</option>
                        <option value="RESPONDE_A_DOLOR" <?= $conciencia=='RESPONDE_A_DOLOR'?'selected':'' ?>>Responde a Dolor</option>
                        <option value="INCONSCIENTE" <?= $conciencia=='INCONSCIENTE'?'selected':'' ?>>Inconsciente</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Escala de Dolor (0 - 10)</label>
                    <select name="dolor" class="form-select">
                        <option value="0" <?= ($dolor == '0') ? 'selected' : '' ?>>0 - Sin Dolor</option>
                        <option value="1" <?= ($dolor == '1') ? 'selected' : '' ?>>1 - Muy Leve</option>
                        <option value="2" <?= ($dolor == '2') ? 'selected' : '' ?>>2 - Leve</option>
                        <option value="3" <?= ($dolor == '3') ? 'selected' : '' ?>>3 - Moderado</option>
                        <option value="4" <?= ($dolor == '4') ? 'selected' : '' ?>>4 - Moderado</option>
                        <option value="5" <?= ($dolor == '5') ? 'selected' : '' ?>>5 - Moderado (Distrae)</option>
                        <option value="6" <?= ($dolor == '6') ? 'selected' : '' ?>>6 - Moderado (Intenso)</option>
                        <option value="7" <?= ($dolor == '7') ? 'selected' : '' ?>>7 - Severo</option>
                        <option value="8" <?= ($dolor == '8') ? 'selected' : '' ?>>8 - Muy Severo</option>
                        <option value="9" <?= ($dolor == '9') ? 'selected' : '' ?>>9 - Insoportable</option>
                        <option value="10" <?= ($dolor == '10') ? 'selected' : '' ?>>10 - Peor dolor imaginable</option>
                    </select>
                </div>

                <div class="col-12"><div class="section-title">Evaluación Médica</div></div>
                <div class="col-md-6">
                    <label class="form-label">Motivo de Consulta</label>
                    <textarea name="motivo" class="form-control" rows="2" ><?= htmlspecialchars($motivo) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"><?= htmlspecialchars($observaciones) ?></textarea>
                </div>

            </div>

            <div class="row mt-4">
                <div class="col-md-6 offset-md-3">
                    <button type="submit" name="calcular" class="btn-calc"> ANALIZAR GRAVEDAD</button>
                </div>
            </div>

            <?php if ($mostrar_resultado): ?>
                <?php 
                    $color_class = match($prioridad_calculada) {
                        'ROJO' => 'bg-rojo',
                        'NARANJA' => 'bg-naranja',
                        default => 'bg-verde'
                    };
                ?>
                <div class="result-box">
                    <h5 class="text-muted text-uppercase fw-bold mb-3">Resultado Sugerido</h5>
                    <div class="prio-badge <?= $color_class ?>"><?= $prioridad_calculada ?></div>

                    <div class="row justify-content-center mt-4 pt-4 border-top">
                        <div class="col-md-6 text-start">
                            <label class="form-label fw-bold">Confirmación Profesional</label>
                            <select name="prioridad_final" class="form-select mb-3 border-2">
                                <option value="">(Usar resultado calculado)</option>
                                <option value="ROJO">Cambiar a ROJO (Emergencia)</option>
                                <option value="NARANJA">Cambiar a NARANJA (Urgencia)</option>
                                <option value="VERDE">Cambiar a VERDE (No Urgente)</option>
                            </select>
                            <input type="text" name="razon_sobrescrito" class="form-control mb-4" placeholder="Justificación (si cambia el nivel)">
                            <button type="submit" name="guardar" class="btn-save">✓ FINALIZAR Y GUARDAR</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </form>
    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    function calcularEdad() {
        const fechaNac = document.getElementById('fecha_nacimiento').value;
        if(fechaNac) {
            const hoy = new Date();
            const nacimiento = new Date(fechaNac);
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
                edad--;
            }
            document.getElementById('edad').value = edad;
        }
    }
  </script>
</body>
</html>