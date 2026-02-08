<?php
// reglas_triaje.php - Versión con soporte para Selección Médica (Nivel de Conciencia)
session_start();
if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ambulatorio");
$conn->set_charset("utf8mb4");
$mensaje = '';

// Diccionario de campos y sus tipos
$nombres_campos = [
    'ta_sist' => 'Tensión Arterial Sistólica',
    'ta_diast' => 'Tensión Arterial Diastólica',
    'fc' => 'Frecuencia Cardíaca (lpm)',
    'fr' => 'Frecuencia Respiratoria (rpm)',
    'temperatura' => 'Temperatura (°C)',
    'saturacion' => 'Saturación de O2 (%)',
    'dolor' => 'Escala de Dolor (0-10)',
    'conciencia' => 'Nivel de Conciencia'
];

// Opciones para el campo de selección "conciencia" 
$opciones_conciencia = ['ALERTA', 'SOMNOLIENTO', 'RESPONDE_A_VOZ', 'RESPONDE_A_DOLOR', 'INCONSCIENTE'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $id = (int)$_POST['id'];
    $prioridad = $_POST['prioridad'];
    $descripcion = trim($_POST['descripcion']);
    $sintomas = trim($_POST['sintomas_alarma'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;

    $cond = [];
    if (isset($_POST['cond_campo'])) {
        foreach ($_POST['cond_campo'] as $i => $campo) {
            if ($campo == 'conciencia') {
                $val = $_POST['cond_valor_select'][$i] ?? '';
                if ($val !== '') $cond[$campo . '_' . $i] = '=' . $val;
            } else {
                $op = $_POST['cond_operador'][$i];
                $val = trim($_POST['cond_valor'][$i]);
                if ($campo && $op && $val !== '') $cond[$campo . '_' . $i] = $op . $val;
            }
        }
    }
    $cond_json = json_encode($cond);
    $stmt = $conn->prepare("UPDATE reglas_triaje SET prioridad = ?, descripcion = ?, condiciones = ?, sintomas_alarma = ?, activo = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $prioridad, $descripcion, $cond_json, $sintomas, $activo, $id);
    $stmt->execute();
    $mensaje = '<div class="alert alert-success shadow-sm">Regla médica actualizada con éxito.</div>';
}

$reglas = $conn->query("SELECT * FROM reglas_triaje ORDER BY FIELD(prioridad, 'ROJO', 'NARANJA', 'VERDE'), id DESC")->fetch_all(MYSQLI_ASSOC);

$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $edit = $conn->query("SELECT * FROM reglas_triaje WHERE id = $id_edit")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración de Reglas Médicas</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; padding-top: 80px; }
        .navbar { background: #aa0b0b !important; }
        .cond-bar { background: #fff; border-left: 4px solid #aa0b0b; border-radius: 8px; padding: 15px; margin-bottom: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .badge-rojo { background: #dc3545; } .badge-naranja { background: #fd7e14; } .badge-verde { background: #198754; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand text-white fw-bold" href="#">Ambulatorio Urbano I Libertador</a>
        <a href="triaje.php" class="btn btn-outline-light btn-sm">Volver al Módulo</a>
    </div>
</nav>

<div class="container">
    <?= $mensaje ?>

    <?php if ($edit): ?>
    <div class="card shadow-lg border-0 mb-5">
        <div class="card-header bg-danger text-white py-3">
            <h5 class="mb-0">Editando Criterio: <?= htmlspecialchars($edit['descripcion']) ?></h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" id="form-reglas">
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <input type="hidden" name="guardar" value="1">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nivel de Prioridad Sugerida</label>
                        <select name="prioridad" class="form-select border-danger">
                            <option value="ROJO" <?= $edit['prioridad']=='ROJO'?'selected':'' ?>>ROJO (Inmediata)</option>
                            <option value="NARANJA" <?= $edit['prioridad']=='NARANJA'?'selected':'' ?>>NARANJA (Urgente)</option>
                            <option value="VERDE" <?= $edit['prioridad']=='VERDE'?'selected':'' ?>>VERDE (No urgente)</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Descripción para Diagnóstico</label>
                        <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($edit['descripcion']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-danger">Condiciones de Activación Médica</label>
                    <div id="contenedor-condiciones">
                        <?php
                        $json_c = json_decode($edit['condiciones'] ?? '{}', true);
                        foreach ($json_c as $clave => $val_raw):
                            $campo_id = preg_replace('/_\d+$/', '', $clave);
                            preg_match('/^(<=|>=|<|>|=)?\s*(.*)/', $val_raw, $m);
                            $op = $m[1] ?? '=';
                            $val = $m[2] ?? $val_raw;
                        ?>
                        <div class="row g-2 mb-2 align-items-center cond-bar">
                            <div class="col-md-4">
                                <select name="cond_campo[]" class="form-select selector-campo" onchange="toggleTipoInput(this)">
                                    <?php foreach ($nombres_campos as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= $campo_id == $k ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 div-numerico" style="<?= $campo_id == 'conciencia' ? 'display:none' : '' ?>">
                                <select name="cond_operador[]" class="form-select">
                                    <option value=">" <?= $op == '>' ? 'selected' : '' ?>>Mayor a (>)</option>
                                    <option value="<" <?= $op == '<' ? 'selected' : '' ?>>Menor a (<)</option>
                                    <option value=">=" <?= $op == '>=' ? 'selected' : '' ?>>Mayor o igual (>=)</option>
                                    <option value="<=" <?= $op == '<=' ? 'selected' : '' ?>>Menor o igual (<=)</option>
                                    <option value="=" <?= $op == '=' ? 'selected' : '' ?>>Igual a (=)</option>
                                </select>
                            </div>
                            <div class="col-md-4 div-numerico" style="<?= $campo_id == 'conciencia' ? 'display:none' : '' ?>">
                                <input type="number" step="0.1" name="cond_valor[]" class="form-control" value="<?= $campo_id != 'conciencia' ? htmlspecialchars($val) : '' ?>">
                            </div>

                            <div class="col-md-7 div-select" style="<?= $campo_id == 'conciencia' ? '' : 'display:none' ?>">
                                <select name="cond_valor_select[]" class="form-select">
                                    <?php foreach ($opciones_conciencia as $opt): ?>
                                        <option value="<?= $opt ?>" <?= ($campo_id == 'conciencia' && $val == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-1 text-end">
                                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.row').remove()">✕</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-dark btn-sm shadow-sm" onclick="agregarFila()">+ Añadir Condición</button>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Síntomas de Alarma (Texto Libre)</label>
                    <textarea name="sintomas_alarma" class="form-control" rows="2"><?= htmlspecialchars($edit['sintomas_alarma'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="reglas_triaje.php" class="btn btn-light px-4">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4 shadow">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-dark text-white">
                    <tr>
                        <th class="ps-4">Prioridad</th>
                        <th>Descripción y Rangos Médicos</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reglas as $r): ?>
                    <tr>
                        <td class="ps-4">
                            <span class="badge rounded-pill <?= $r['prioridad']=='ROJO'?'badge-rojo':($r['prioridad']=='NARANJA'?'badge-naranja':'badge-verde') ?> p-2 px-3">
                                <?= $r['prioridad'] ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($r['descripcion']) ?></div>
                            <div class="small text-muted">
                                <?php 
                                $c_list = json_decode($r['condiciones'], true);
                                foreach($c_list as $cl => $vl) {
                                    $nombre = $nombres_campos[preg_replace('/_\d+$/', '', $cl)] ?? $cl;
                                    echo "• $nombre: $vl ";
                                }
                                ?>
                            </div>
                        </td>
                        <td class="text-center"><a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleTipoInput(selectElement) {
    const row = selectElement.closest('.row');
    const divsNumericos = row.querySelectorAll('.div-numerico');
    const divSelect = row.querySelector('.div-select');

    if (selectElement.value === 'conciencia') {
        divsNumericos.forEach(d => d.style.display = 'none');
        divSelect.style.display = 'block';
    } else {
        divsNumericos.forEach(d => d.style.display = 'block');
        divSelect.style.display = 'none';
    }
}

function agregarFila() {
    const container = document.getElementById('contenedor-condiciones');
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-center cond-bar';
    div.innerHTML = `
        <div class="col-md-4">
            <select name="cond_campo[]" class="form-select selector-campo" onchange="toggleTipoInput(this)">
                <?php foreach ($nombres_campos as $k => $v): ?>
                    <option value="<?= $k ?>"><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 div-numerico">
            <select name="cond_operador[]" class="form-select">
                <option value=">">Mayor a (>)</option>
                <option value="<">Menor a (<)</option>
                <option value="=" selected>Igual a (=)</option>
            </select>
        </div>
        <div class="col-md-4 div-numerico">
            <input type="number" step="0.1" name="cond_valor[]" class="form-control">
        </div>
        <div class="col-md-7 div-select" style="display:none">
            <select name="cond_valor_select[]" class="form-select">
                <?php foreach ($opciones_conciencia as $opt): ?>
                    <option value="<?= $opt ?>"><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.row').remove()">✕</button>
        </div>
    `;
    container.appendChild(div);
}
</script>
</body>
</html>