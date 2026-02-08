<?php
session_start();
// Capturar mensajes de error si vienen de guardar.php
$mensaje = '';
$tipo_mensaje = '';

if (isset($_SESSION['registro_error'])) {
  $mensaje = $_SESSION['registro_error'];
  $tipo_mensaje = 'danger'; // Rojo para errores
  unset($_SESSION['registro_error']);
}

// (Opcional) Si quisieras mostrar mensajes de éxito aquí también
if (isset($_GET['msg']) && $_GET['msg'] == 'guardado') {
    $mensaje = "Paciente registrado exitosamente.";
    $tipo_mensaje = 'success'; // Verde para éxito
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nueva Historia Clínica | Ambulatorio</title>
  
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    /* --- ESTILOS GENERALES --- */
    :root {
        --primary-red: #aa0b0b;
        --primary-blue: #003366;
        --bg-light: #eef2f6;
    }

    body {
      background: var(--bg-light);
      font-family: 'Segoe UI', system-ui, sans-serif;
      padding-bottom: 40px;
    }

    /* --- NAVBAR --- */
    .navbar {
      background: linear-gradient(90deg, var(--primary-red) 0%, var(--primary-blue) 100%);
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
      max-width: 1100px; /* Ancho amplio para que quepan bien las columnas */
      margin: 40px auto;
    }

    .form-header {
        text-align: center;
        margin-bottom: 35px;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
    }
    
    .form-header h2 {
        color: var(--primary-blue);
        font-weight: 800;
        margin-bottom: 5px;
        font-size: 1.8rem;
    }

    /* --- SECCIONES --- */
    .section-label {
        color: var(--primary-blue);
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 30px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
    }
    
    .section-label::before {
        content: '';
        display: inline-block;
        width: 5px;
        height: 20px;
        background-color: var(--primary-red);
        margin-right: 10px;
        border-radius: 5px;
    }

    /* --- INPUTS --- */
    .form-label {
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        border-radius: 10px;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        background-color: #f8f9fa;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
        background-color: #fff;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
    }

    /* --- BOTONES --- */
    .btn-guardar {
        background: var(--primary-red);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        width: 100%;
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(170, 11, 11, 0.2);
    }
    .btn-guardar:hover { background: #8a0000; color: white; transform: translateY(-2px); }

    .btn-cancelar {
        background: #e9ecef;
        color: #555;
        border: none;
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
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
        <span>Nueva Historia Clínica</span>
      </a>
      <div class="ms-auto">
        <a href="pacientes.php" class="text-white text-decoration-none fw-bold" style="font-size: 1.5rem; opacity: 0.8;">&times;</a>
      </div>
    </div>
  </nav>

  <div style="height: 80px;"></div>

  <div class="container px-3">
    <div class="card-form">
        
        <div class="form-header">
            <h2>Registro de Paciente</h2>
            <p class="text-muted m-0">Complete la información para abrir una nueva historia médica integral.</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?> text-center shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <?= htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div id="alertaJS" class="alert alert-warning text-center d-none shadow-sm border-0 mb-4" style="border-radius: 10px; font-weight: 500;"></div>

        <form action="guardar.php" method="post" onsubmit="return validarFormulario()">
            
            <div class="row g-4">
                
                <div class="col-12">
                    <div class="section-label">Datos de Identificación</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cédula de Identidad <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" name="cedula" id="cedula" required placeholder="Solo números">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="nombres" id="nombres" required placeholder="Ej: Juan Carlos">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="apellidos" id="apellidos" required placeholder="Ej: Pérez Pérez">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha Nacimiento</label>
                    <input class="form-control" type="date" name="fecha_nacimiento">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sexo <span class="text-danger">*</span></label>
                    <select class="form-select" name="sexo" required>
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="F">Femenino</option>
                        <option value="M">Masculino</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ocupación</label>
                    <input class="form-control" type="text" name="ocupacion" placeholder="Ej: Estudiante">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Teléfono de Contacto</label>
                    <input class="form-control" type="tel" name="telefono" placeholder="Ej: 0414-1234567">
                </div>

                <div class="col-12">
                    <div class="section-label">Ubicación Geográfica</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Estado de Residencia</label>
                    <select class="form-select" name="estado">
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="Mérida">Mérida</option>
                        <option value="Táchira">Táchira</option>
                        <option value="Trujillo">Trujillo</option>
                        <option value="Zulia">Zulia</option>
                        <option value="Barinas">Barinas</option>
                        <option value="Caracas">Caracas</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">País</label>
                    <select class="form-select" name="pais">
                        <option value="Venezuela" selected>Venezuela</option>
                        <option value="Colombia">Colombia</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Lugar de Nacimiento</label>
                    <input class="form-control" type="text" name="lugar_nacimiento" placeholder="Ciudad/Pueblo">
                </div>
                <div class="col-12">
                    <label class="form-label">Dirección de Habitación Exacta</label>
                    <input class="form-control" type="text" name="direccion" placeholder="Ej: Av. Principal, Sector El Llano, Casa N° 123">
                </div>

                <div class="col-12">
                    <div class="section-label">Datos Clínicos Iniciales</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Peso (Kg)</label>
                    <input class="form-control" type="number" step="0.01" name="peso" placeholder="Ej: 70.5">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Altura (Metros)</label>
                    <input class="form-control" type="number" step="0.01" name="Altura" placeholder="Ej: 1.75">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Observaciones / Motivo de Registro</label>
                    <textarea class="form-control" rows="3" name="observaciones" placeholder="Notas adicionales relevantes para la apertura de la historia..."></textarea>
                </div>

            </div>

            <div class="row mt-5 pt-4 border-top g-3">
                <div class="col-md-6 order-md-2">
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save me-2"></i> Guardar Historia Clínica
                    </button>
                </div>
                <div class="col-md-6 order-md-1">
                    <a href="pacientes.php" class="btn-cancelar">
                        Cancelar
                    </a>
                </div>
            </div>

        </form>
    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    function validarFormulario() {
      var cedula = document.getElementById('cedula').value.trim();
      var nombres = document.getElementById('nombres').value.trim();
      var apellidos = document.getElementById('apellidos').value.trim();
      var sexo = document.querySelector('select[name="sexo"]').value;
      var alerta = document.getElementById('alertaJS');

      alerta.classList.add('d-none'); // Ocultar alerta previa

      // Validación básica de campos obligatorios
      if (!cedula || !nombres || !apellidos || !sexo) {
        alerta.textContent = 'Por favor, complete los campos obligatorios marcados con asterisco (*).';
        alerta.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' }); // Subir para ver el error
        return false;
      }

      // Validación de longitud de cédula
      if (cedula.length < 5 || cedula.length > 10) {
        alerta.textContent = 'La cédula debe tener entre 5 y 10 dígitos.';
        alerta.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
      }

      return true; // Formulario válido, proceder al envío
    }
  </script>

</body>
</html>