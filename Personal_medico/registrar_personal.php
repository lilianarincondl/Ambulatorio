<?php
session_start();
// Capturamos mensajes de error si existen
$mensaje = '';
if (isset($_SESSION['registro_error'])) {
    $mensaje = $_SESSION['registro_error'];
    unset($_SESSION['registro_error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrar Médico | Ambulatorio</title>
  
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    /* --- ESTILO GENERAL (Igual al Dashboard) --- */
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

    /* --- TARJETA DEL FORMULARIO --- */
    .card-form {
      background: white;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      padding: 40px;
      max-width: 800px; /* Ancho máximo para que no se estire demasiado */
      margin: 40px auto; /* Centrado automático */
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .form-header h2 {
        color: #003366;
        font-weight: 700;
    }

    /* --- INPUTS PERSONALIZADOS --- */
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

    /* --- BOTONES --- */
    .btn-guardar {
        background: #aa0b0b;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: 0.3s;
        width: 100%;
    }
    .btn-guardar:hover { background: #8a0000; color: white; }

    .btn-cancelar {
        background: #e9ecef;
        color: #555;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-weight: 600;
        transition: 0.3s;
        width: 100%;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    .btn-cancelar:hover { background: #dee2e6; color: #333; }

  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" />
        <span>Registro de Personal</span>
      </a>
    </div>
  </nav>

  <div style="height: 80px;"></div>

  <div class="container">
    <div class="card-form">
        
        <div class="form-header">
            <img src="../icons/afiliado.png" alt="Icono" style="width: 60px; margin-bottom: 15px;">
            <h2>Nuevo Médico</h2>
            <p class="text-muted">Complete la información para registrar un nuevo usuario en el sistema.</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-danger text-center shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div id="alertaJS" class="alert alert-warning text-center d-none shadow-sm border-0" style="border-radius: 10px;"></div>

        <form action="guardar.php" method="post" onsubmit="return validarFormulario()">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej: Dr. Juan Pérez" required>
                    </div>

                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula de Identidad</label>
                        <input type="number" class="form-control" name="cedula" id="cedula" placeholder="Ej: 12345678" required min="1">
                    </div>

                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" id="correo" placeholder="correo@ejemplo.com" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="••••••••" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirmar" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" name="confirmar" id="confirmar" placeholder="Repita la contraseña" required>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded text-muted small">
                        <ul class="mb-0 ps-3">
                            <li>La contraseña debe ser segura.</li>
                            <li>Verifique que el correo sea correcto.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <a href="personal.php" class="btn-cancelar">Cancelar</a>
                </div>
                <div class="col-6">
                    <button type="submit" class="btn-guardar">Guardar Registro</button>
                </div>
            </div>

        </form>
    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>

  <script>
    function validarFormulario() {
      var nombre = document.getElementById('nombre').value.trim();
      var pass = document.getElementById('password').value;
      var conf = document.getElementById('confirmar').value;
      var alerta = document.getElementById('alertaJS');

      // Limpiar estados previos
      alerta.classList.add('d-none');
      document.getElementById('password').classList.remove('is-invalid');
      document.getElementById('confirmar').classList.remove('is-invalid');

      if (!nombre) {
        mostrarAlerta('Por favor, ingrese el nombre completo.');
        return false;
      }

      if (pass !== conf) {
        mostrarAlerta('Las contraseñas no coinciden. Inténtelo de nuevo.');
        document.getElementById('password').classList.add('is-invalid');
        document.getElementById('confirmar').classList.add('is-invalid');
        return false;
      }

      if (pass.length < 4) { // Validación simple de longitud
        mostrarAlerta('La contraseña es muy corta (mínimo 4 caracteres).');
        return false;
      }

      return true;
    }

    function mostrarAlerta(mensaje) {
        var alerta = document.getElementById('alertaJS');
        alerta.textContent = mensaje;
        alerta.classList.remove('d-none');
        // Auto-ocultar después de 4 segundos
        setTimeout(function(){ alerta.classList.add('d-none'); }, 4000);
    }
  </script>

</body>
</html>