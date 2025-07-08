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
if ($id <= 0 || $id == 1) {
    header("Location: personal.php?error=id_invalido");
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
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar Personal Médico</title>
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <style>
    body {
      background: #f1f5f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
      background-color: #aa0b0b;
      padding: 0.6rem 1.2rem;
    }
    .navbar-brand,
    .nav-link {
      color: #fff !important;
      font-weight: 500;
    }
    .nav-link:hover {
      color: #cce5ff !important;
    }
    .form-container {
      background: #fff0f0;
      border: 2px solid #d32f2f;
      padding: 2.5rem 2rem;
      border-radius: 1rem;
      box-shadow: 0 8px 24px rgba(211,47,47,0.12);
      max-width: 420px;
      margin: 5rem auto 2rem;
    }
    .form-section h2 {
      color: #b71c1c;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    .form-label {
      font-weight: 600;
      color: #b71c1c;
    }
    .btn {
      width: 100%;
      font-weight: 500;
    }
    .logo {
      display: block;
      margin: 0 auto 1rem;
      height: 50px;
    }
    .alert {
      margin-bottom: 1rem;
    }
  </style>
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
          <li class="nav-item"><a class="nav-link" href="personal.php">Atrás</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <div class="form-container mt-5">
    <div class="form-section">
      <img src="../icons/logo.png" alt="Logo" class="logo">
      <h2>Editar Personal Médico</h2>
      <?php
        $mensaje = '';
        if (isset($_GET['error'])) {
          if ($_GET['error'] === 'bd') {
            $mensaje = 'Error al actualizar en la base de datos.';
          } else {
            $mensaje = $_GET['error'];
          }
        }
      ?>
      <form action="actualizar.php" method="POST" autocomplete="off" onsubmit="return validarFormularioEditar()">
        <input type="hidden" name="id" value="<?php echo base64_encode($id); ?>">
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre completo</label>
          <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
        </div>
        <div class="mb-3">
          <label for="cedula" class="form-label">Cédula</label>
          <input type="number" class="form-control" id="cedula" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>" required min="1">
        </div>
        <div class="mb-3">
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>
        </div>
        <div class="mb-3">
          <label for="clave" class="form-label">Nueva contraseña (dejar en blanco para no cambiar)</label>
          <input type="password" class="form-control" id="clave" name="clave" autocomplete="new-password">
        </div>
        <div class="mb-3">
          <label for="confirmar_clave" class="form-label">Confirmar nueva contraseña</label>
          <input type="password" class="form-control" id="confirmar_clave" name="confirmar_clave" autocomplete="new-password">
        </div>
        <div id="alerta" class="alert alert-danger d-none"></div>
        <?php if ($mensaje): ?>
          <div id="bannerError" class="alert alert-danger text-center" style="position:relative;z-index:10;">
            <?php echo htmlspecialchars($mensaje); ?>
          </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-danger">Guardar cambios</button>
        <a href="personal.php" class="btn btn-link mt-2">Cancelar</a>
      </form>
    </div>
  </div>
  <script>
    function validarFormularioEditar() {
      var nombre = document.getElementById('nombre').value.trim();
      var correo = document.getElementById('correo').value.trim();
      var clave = document.getElementById('clave').value;
      var confirmar = document.getElementById('confirmar_clave').value;
      var cedula = document.getElementById('cedula').value.trim();
      var alerta = document.getElementById('alerta');
      if (!nombre || !correo || !cedula) {
        alerta.textContent = 'Todos los campos son obligatorios.';
        alerta.classList.remove('d-none');
        setTimeout(function(){ alerta.classList.add('d-none'); }, 3500);
        return false;
      }
      if (clave !== '' && clave !== confirmar) {
        alerta.textContent = 'Las contraseñas no coinciden.';
        alerta.classList.remove('d-none');
        setTimeout(function(){ alerta.classList.add('d-none'); }, 3500);
        return false;
      }
      alerta.classList.add('d-none');
      return true;
    }
    // Banner de error de backend
    window.onload = function() {
      var banner = document.getElementById('bannerError');
      if (banner) {
        setTimeout(function(){ banner.style.display = 'none'; }, 3500);
      }
    }
  </script>
  <script src="../bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
