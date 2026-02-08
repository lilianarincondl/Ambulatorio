<?php
// editar_personal.php - Editar datos de personal médico

// Conexión directa a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener ID del usuario a editar (cifrado en base64)
$id = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
$id = intval($id);

// Validación de seguridad: ID inválido o intento de editar al admin (ID 1)
if ($id <= 1) {
    header("Location: personal.php?error=acceso_denegado");
    exit();
}

// Consultar datos actuales
$stmt = $conn->prepare("SELECT nombre, correo, cedula FROM usuario_medico WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: personal.php?error=no_encontrado");
    exit();
}

$stmt->bind_result($nombre, $correo, $cedula);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar Médico | Ambulatorio</title>
  
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    /* --- ESTILOS GENERALES (Coherentes con el resto) --- */
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

    /* --- TARJETA FORMULARIO --- */
    .card-form {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      padding: 40px;
      max-width: 800px;
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

    /* --- INPUTS --- */
    .form-label {
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
    }

    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
    }

    .form-control:focus {
        background-color: #fff;
        border-color: #aa0b0b;
        box-shadow: 0 0 0 3px rgba(170, 11, 11, 0.1);
    }

    /* --- SECCIÓN CONTRASEÑA (Visualmente separada) --- */
    .password-section {
        background-color: #fff9fa; /* Fondo rojizo muy muy suave */
        border: 1px dashed #eecdd2;
        border-radius: 15px;
        padding: 20px;
        margin-top: 10px;
    }

    .password-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #aa0b0b;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* --- BOTONES --- */
    .btn-guardar {
        background: #003366; /* Azul para editar (diferente al rojo de crear) */
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
        <span>Edición de Personal</span>
      </a>
    </div>
  </nav>

  <div style="height: 80px;"></div>

  <div class="container">
    <div class="card-form">
        
        <div class="form-header">
            <h2>Editar Datos</h2>
            <p class="text-muted m-0">Actualiza la información del médico seleccionado</p>
        </div>

        <div id="alertaJS" class="alert alert-warning text-center d-none shadow-sm border-0" style="border-radius: 10px;"></div>

        <form action="actualizar.php" method="POST" autocomplete="off" onsubmit="return validarFormularioEditar()">
            
            <input type="hidden" name="id" value="<?php echo base64_encode($id); ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula de Identidad</label>
                        <input type="number" class="form-control" id="cedula" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>" required min="1">
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="password-section">
                        <div class="password-title">
                            <i class="fa-solid fa-lock me-1"></i> Cambio de Contraseña
                        </div>
                        <p class="text-muted small mb-3">
                            Deje estos campos <b>vacíos</b> si no desea cambiar la contraseña actual.
                        </p>

                        <div class="mb-3">
                            <label for="clave" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="clave" name="clave" autocomplete="new-password" placeholder="Solo si desea cambiarla">
                        </div>

                        <div class="mb-0">
                            <label for="confirmar_clave" class="form-label">Confirmar Nueva</label>
                            <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" autocomplete="new-password" placeholder="Repita la nueva clave">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 pt-2 border-top">
                <div class="col-6 mt-3">
                    <a href="personal.php" class="btn-cancelar">Cancelar</a>
                </div>
                <div class="col-6 mt-3">
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </div>
            </div>

        </form>
    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    function validarFormularioEditar() {
      var nombre = document.getElementById('nombre').value.trim();
      var correo = document.getElementById('correo').value.trim();
      var cedula = document.getElementById('cedula').value.trim();
      
      var clave = document.getElementById('clave').value;
      var confirmar = document.getElementById('confirmar_clave').value;
      
      var alerta = document.getElementById('alertaJS');

      // Limpiar estados
      alerta.classList.add('d-none');
      document.getElementById('clave').classList.remove('is-invalid');
      document.getElementById('confirmar_clave').classList.remove('is-invalid');

      if (!nombre || !correo || !cedula) {
        mostrarAlerta('Los campos de nombre, cédula y correo son obligatorios.');
        return false;
      }

      // Solo validar contraseñas si el usuario escribió algo en el campo "Nueva Contraseña"
      if (clave !== '') {
          if (clave !== confirmar) {
            mostrarAlerta('Las nuevas contraseñas no coinciden.');
            document.getElementById('clave').classList.add('is-invalid');
            document.getElementById('confirmar_clave').classList.add('is-invalid');
            return false;
          }
          if (clave.length < 4) {
             mostrarAlerta('La nueva contraseña es muy corta (mínimo 4 caracteres).');
             return false;
          }
      }

      return true;
    }

    function mostrarAlerta(mensaje) {
        var alerta = document.getElementById('alertaJS');
        alerta.textContent = mensaje;
        alerta.classList.remove('d-none');
        setTimeout(function(){ alerta.classList.add('d-none'); }, 4000);
    }
  </script>

</body>
</html>
<?php $conn->close(); ?>