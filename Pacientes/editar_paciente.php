<?php
// --- Lógica para cargar datos del paciente y mostrar en el formulario de edición ---
session_start();

// Gestión de mensajes de error
$mensaje = '';
if (isset($_SESSION['registro_error'])) {
  $mensaje = $_SESSION['registro_error'];
  unset($_SESSION['registro_error']);
}

// Conexión a Base de Datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Validación del ID
$id = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
$id = intval($id);

if ($id <= 0) {
    header("Location: pacientes.php?error=id_invalido");
    exit();
}

// Inicialización de variables
$cedula = $apellidos = $nombres = $ocupacion = $sexo = $fecha_nacimiento = $lugar_nacimiento = $estado = $pais = $direccion = $telefono = $peso = $Altura = $observaciones = '';

// Consulta segura
$stmt = $conn->prepare("SELECT cedula, apellidos, nombres, ocupacion, sexo, fecha_nacimiento, lugar_nacimiento, estado, pais, direccion, telefono, peso, Altura, observaciones FROM pacientes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: pacientes.php?error=no_encontrado");
    exit();
}

$stmt->bind_result($cedula, $apellidos, $nombres, $ocupacion, $sexo, $fecha_nacimiento, $lugar_nacimiento, $estado, $pais, $direccion, $telefono, $peso, $Altura, $observaciones);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar Historia Clínica | Ambulatorio</title>
  
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

    /* --- TARJETA FORMULARIO --- */
    .card-form {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      padding: 40px;
      max-width: 1000px; /* Ancho amplio para formulario complejo */
      margin: 40px auto;
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
    }
    
    .form-header h2 {
        color: #003366;
        font-weight: 700;
        margin-bottom: 5px;
    }

    /* --- SECCIONES DEL FORMULARIO --- */
    .section-label {
        color: #aa0b0b;
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #aa0b0b;
        padding-left: 10px;
    }

    /* --- INPUTS --- */
    .form-label {
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }

    .form-control:focus, .form-select:focus {
        background-color: #fff;
        border-color: #003366;
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
    }

    /* --- BOTONES --- */
    .btn-guardar {
        background: #003366; /* Azul para editar */
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        transition: 0.3s;
    }
    .btn-guardar:hover { background: #002244; color: white; }

    .btn-cancelar {
        background: #e9ecef;
        color: #555;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: 0.3s;
    }
    .btn-cancelar:hover { background: #dee2e6; color: #333; }

  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" />
        <span>Edición de Historia Clínica</span>
      </a>
      <div class="ms-auto">
        <a href="pacientes.php" class="text-white text-decoration-none" title="Cancelar" style="font-size: 1.2rem;">✕</a>
      </div>
    </div>
  </nav>

  <div style="height: 80px;"></div>

  <div class="container">
    <div class="card-form">
        
        <div class="form-header">
            <h2>Actualizar Datos del Paciente</h2>
            <p class="text-muted m-0">Modifique la información clínica necesaria</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-danger text-center shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form action="actualizar.php" method="post">
            <input type="hidden" name="id" value="<?php echo base64_encode($id); ?>">

            <div class="row g-3">
                
                <div class="col-12"><div class="section-label">Datos Personales</div></div>

                <div class="col-md-4">
                    <label class="form-label">Cédula de Identidad</label>
                    <input class="form-control" type="text" name="cedula" required value="<?php echo htmlspecialchars($cedula); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombres</label>
                    <input class="form-control" type="text" name="nombres" required value="<?php echo htmlspecialchars($nombres); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Apellidos</label>
                    <input class="form-control" type="text" name="apellidos" required value="<?php echo htmlspecialchars($apellidos); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input class="form-control" type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($fecha_nacimiento); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sexo</label>
                    <select class="form-select" name="sexo">
                        <option disabled>Seleccione</option>
                        <option value="F" <?php if($sexo=="F" || $sexo=="Femenino") echo 'selected'; ?>>Femenino</option>
                        <option value="M" <?php if($sexo=="M" || $sexo=="Masculino") echo 'selected'; ?>>Masculino</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ocupación</label>
                    <input class="form-control" type="text" name="ocupacion" value="<?php echo htmlspecialchars($ocupacion); ?>">
                </div>

                <div class="col-12"><div class="section-label">Ubicación y Contacto</div></div>

                <div class="col-md-4">
                    <label class="form-label">Teléfono</label>
                    <input class="form-control" type="tel" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="estado">
                        <option value="" <?php if(empty($estado)) echo 'selected'; ?>>Seleccione un estado</option>
                        <option value="Zulia" <?php if($estado == 'Zulia') echo 'selected'; ?>>Zulia</option>
                        <option value="Merida" <?php if($estado == 'Mérida' || $estado == 'Merida') echo 'selected'; ?>>Mérida</option>
                        <option value="Tachira" <?php if($estado == 'Táchira' || $estado == 'Tachira') echo 'selected'; ?>>Táchira</option>
                        <option value="Trujillo" <?php if($estado == 'Trujillo') echo 'selected'; ?>>Trujillo</option>
                        <option value="Falcon" <?php if($estado == 'Falcón' || $estado == 'Falcon') echo 'selected'; ?>>Falcón</option>
                        <option value="Lara" <?php if($estado == 'Lara') echo 'selected'; ?>>Lara</option>
                        </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">País</label>
                    <select class="form-select" name="pais">
                        <option value="" <?php if(empty($pais)) echo 'selected'; ?>>Seleccione</option>
                        <option value="Venezuela" <?php if($pais == 'Venezuela') echo 'selected'; ?>>Venezuela</option>
                        <option value="Extranjero" <?php if($pais == 'Extranjero') echo 'selected'; ?>>Extranjero</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Dirección / Lugar de Nacimiento</label>
                    <div class="input-group">
                        <input class="form-control" type="text" name="direccion" placeholder="Dirección actual" value="<?php echo htmlspecialchars($direccion); ?>">
                        <input class="form-control" type="text" name="lugar_nacimiento" placeholder="Lugar de Nacimiento" value="<?php echo htmlspecialchars($lugar_nacimiento); ?>">
                    </div>
                </div>

                <div class="col-12"><div class="section-label">Datos Clínicos</div></div>

                <div class="col-md-6">
                    <label class="form-label">Peso (Kg)</label>
                    <input class="form-control" type="number" step="0.01" name="peso" value="<?php echo htmlspecialchars($peso); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Altura (M)</label>
                    <input class="form-control" type="number" step="0.01" name="Altura" value="<?php echo htmlspecialchars($Altura); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Observaciones / Diagnóstico</label>
                    <textarea class="form-control" rows="5" name="observaciones"><?php echo htmlspecialchars($observaciones); ?></textarea>
                </div>

            </div>

            <div class="row mt-5 pt-3 border-top">
                <div class="col-6">
                    <a href="pacientes.php" class="btn-cancelar">Cancelar</a>
                </div>
                <div class="col-6">
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </div>
            </div>

        </form>
    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>