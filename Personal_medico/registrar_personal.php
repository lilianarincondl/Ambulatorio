<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agregar Personal Médico</title>
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
      <h2>Agregar Personal Médico</h2>
      <?php
        session_start();
        $mensaje = '';
        if (isset($_SESSION['registro_error'])) {
          $mensaje = $_SESSION['registro_error'];
          unset($_SESSION['registro_error']);
        }
      ?>
      <form action="guardar.php" method="post" onsubmit="return validarFormulario()">
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre completo</label>
          <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>
        <div class="mb-3">
          <label for="cedula" class="form-label">Cédula</label>
          <input type="number" class="form-control" name="cedula" id="cedula" required min="1">
        </div>
        <div class="mb-3">
          <label for="correo" class="form-label">Correo electrónico</label>
          <input type="email" class="form-control" name="correo" id="correo" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <div class="mb-3">
          <label for="confirmar" class="form-label">Confirmar contraseña</label>
          <input type="password" class="form-control" name="confirmar" id="confirmar" required>
        </div>
        <div id="alerta" class="alert alert-danger d-none"></div>
        <?php if ($mensaje): ?>
          <div id="bannerError" class="alert alert-danger text-center" style="position:relative;z-index:10;">
            <?php echo htmlspecialchars($mensaje); ?>
          </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-danger">Agregar personal</button>
        <a href="personal.php" class="btn btn-link mt-2">Volver al listado</a>
      </form>
    </div>
  </div>
  <script>
    function validarFormulario() {
      var nombre = document.getElementById('nombre').value.trim();
      var correo = document.getElementById('correo').value.trim();
      var pass = document.getElementById('password').value;
      var conf = document.getElementById('confirmar').value;
      var alerta = document.getElementById('alerta');
      if (!nombre || !correo || !pass || !conf) {
        alerta.textContent = 'Todos los campos son obligatorios.';
        alerta.classList.remove('d-none');
        setTimeout(function(){ alerta.classList.add('d-none'); }, 3500);
        return false;
      }
      if (pass !== conf) {
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
