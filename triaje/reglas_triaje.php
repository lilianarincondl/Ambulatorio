<?php
// reglas_triaje.php - Configuración de Reglas Médicas (Diseño Moderno)
session_start();

// Validación de sesión
if (!isset($_SESSION['id_usu']) || empty($_SESSION['id_usu'])) {
    header("Location: ../inicio.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "ambulatorio");
$conn->set_charset("utf8mb4");
$mensaje = '';

// Diccionario de campos
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

$opciones_conciencia = ['ALERTA', 'SOMNOLIENTO', 'RESPONDE_A_VOZ', 'RESPONDE_A_DOLOR', 'INCONSCIENTE'];

// Guardar cambios
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
    // Mensaje bonito
    $mensaje = '<div class="alert alert-success shadow-sm border-0 mb-4" style="border-radius:10px;">Regla actualizada correctamente.</div>';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Reglas | Ambulatorio</title>
    
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

    <style>
        /* --- ESTILOS GENERALES --- */
        body {
            background: #eef2f6;
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding-bottom: 40px;
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

        /* --- FORMULARIO DE EDICIÓN --- */
        .edit-section {
            background: #fdfdfd;
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #aa0b0b; /* Acento rojo */
        }

        .form-label {
            font-weight: 700;
            color: #555;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 10px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #003366;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }

        /* --- FILAS DE CONDICIONES --- */
        .cond-bar {
            background: #fff;
            border: 1px dashed #ccc;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 10px;
            transition: 0.2s;
        }
        .cond-bar:hover { background: #f8f9fa; border-color: #aa0b0b; }

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
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            color: #555;
        }

        .table-hover tbody tr:hover { background-color: #f8f9fa; }

        /* --- BADGES DE PRIORIDAD --- */
        .badge-prio {
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        .bg-rojo { background-color: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .bg-naranja { background-color: #fff3e0; color: #ef6c00; border: 1px solid #ffcc80; }
        .bg-verde { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }

        /* --- BOTONES --- */
        .btn-add-cond {
            background: #003366;
            color: white;
            border-radius: 50px;
            font-size: 0.85rem;
            padding: 8px 20px;
        }
        .btn-add-cond:hover { background: #002244; color: white; }

        .btn-save {
            background: #aa0b0b;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-save:hover { background: #8a0000; color: white; }

    </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" />
        <span>Configuración de Reglas</span>
      </a>
      <div class="ms-auto">
        <a class="btn-volver" href="triaje.php">← Volver al Triaje</a>
      </div>
    </div>
  </nav>

  <div style="height: 70px;"></div>

  <div class="container">
    <div class="main-card">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="color: #003366; font-weight: 700; margin: 0;">Reglas de Triaje</h2>
                <p class="text-muted m-0">Define los parámetros automáticos de priorización</p>
            </div>
        </div>

        <?= $mensaje ?>

        <?php if ($edit): ?>
        <div class="edit-section shadow-sm" id="seccion-editar">
            <h4 class="mb-4" style="color: #aa0b0b; font-weight: 700;">
                <i class="fas fa-edit"></i> Editando Regla: <?= htmlspecialchars($edit['descripcion']) ?>
            </h4>
            
            <form method="POST" id="form-reglas">
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <input type="hidden" name="guardar" value="1">

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Nivel de Prioridad</label>
                        <select name="prioridad" class="form-select">
                            <option value="ROJO" <?= $edit['prioridad']=='ROJO'?'selected':'' ?>>🔴 ROJO (Emergencia)</option>
                            <option value="NARANJA" <?= $edit['prioridad']=='NARANJA'?'selected':'' ?>>🟠 NARANJA (Urgencia)</option>
                            <option value="VERDE" <?= $edit['prioridad']=='VERDE'?'selected':'' ?>>🟢 VERDE (Estándar)</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Descripción del Diagnóstico</label>
                        <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($edit['descripcion']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-primary">Condiciones de Activación</label>
                    <div class="p-3 bg-light rounded border" id="contenedor-condiciones">
                        <?php
                        $json_c = json_decode($edit['condiciones'] ?? '{}', true);
                        foreach ($json_c as $clave => $val_raw):
                            $campo_id = preg_replace('/_\d+$/', '', $clave);
                            preg_match('/^(<=|>=|<|>|=)?\s*(.*)/', $val_raw, $m);
                            $op = $m[1] ?? '=';
                            $val = $m[2] ?? $val_raw;
                        ?>
                        <div class="row g-2 align-items-center cond-bar">
                            <div class="col-md-4">
                                <select name="cond_campo[]" class="form-select" onchange="toggleTipoInput(this)">
                                    <?php foreach ($nombres_campos as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= $campo_id == $k ? 'selected' : '' ?>><?= $v ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2 div-numerico" style="<?= $campo_id == 'conciencia' ? 'display:none' : '' ?>">
                                <select name="cond_operador[]" class="form-select text-center fw-bold">
                                    <option value=">" <?= $op == '>' ? 'selected' : '' ?>>></option>
                                    <option value="<" <?= $op == '<' ? 'selected' : '' ?>><</option>
                                    <option value=">=" <?= $op == '>=' ? 'selected' : '' ?>>≥</option>
                                    <option value="<=" <?= $op == '<=' ? 'selected' : '' ?>>≤</option>
                                    <option value="=" <?= $op == '=' ? 'selected' : '' ?>>=</option>
                                </select>
                            </div>
                            
                            <div class="col-md-5 div-numerico" style="<?= $campo_id == 'conciencia' ? 'display:none' : '' ?>">
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
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.row').remove()">x</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2 text-end">
                        <button type="button" class="btn btn-add-cond" onclick="agregarFila()">+ Añadir Condición</button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Texto de Ayuda (Síntomas)</label>
                    <textarea name="sintomas_alarma" class="form-control" rows="2" placeholder="Ej: Dolor opresivo, sudoración..."><?= htmlspecialchars($edit['sintomas_alarma'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="reglas_triaje.php" class="btn btn-light border px-4">Cancelar</a>
                    <button type="submit" class="btn btn-save">Guardar Cambios</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Nivel Prioridad</th>
                        <th>Descripción y Parámetros</th>
                        <th class="text-center" style="width: 100px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reglas as $r): 
                        $badge_class = $r['prioridad']=='ROJO' ? 'bg-rojo' : ($r['prioridad']=='NARANJA' ? 'bg-naranja' : 'bg-verde');
                    ?>
                    <tr>
                        <td class="ps-4">
                            <span class="badge-prio <?= $badge_class ?>">
                                <?= $r['prioridad'] ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($r['descripcion']) ?></div>
                            <div class="small text-muted mt-1">
                                <?php 
                                $c_list = json_decode($r['condiciones'], true);
                                foreach($c_list as $cl => $vl) {
                                    $nombre = $nombres_campos[preg_replace('/_\d+$/', '', $cl)] ?? $cl;
                                    echo "<span class='me-3'>• $nombre: <b>$vl</b></span>";
                                }
                                ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                                Editar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
  </div>

<script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
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
    div.className = 'row g-2 align-items-center cond-bar';
    div.innerHTML = `
        <div class="col-md-4">
            <select name="cond_campo[]" class="form-select" onchange="toggleTipoInput(this)">
                <?php foreach ($nombres_campos as $k => $v): ?>
                    <option value="<?= $k ?>"><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 div-numerico">
            <select name="cond_operador[]" class="form-select text-center fw-bold">
                <option value=">">></option>
                <option value="<"><</option>
                <option value=">=">≥</option>
                <option value="<=">≤</option>
                <option value="=" selected>=</option>
            </select>
        </div>
        <div class="col-md-5 div-numerico">
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
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.row').remove()">x</button>
        </div>
    `;
    container.appendChild(div);
}
</script>

</body>
</html>