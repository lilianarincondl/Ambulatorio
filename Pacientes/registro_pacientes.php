<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Historia Clínica Integral</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
      background: #ffffff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      max-width: 1000px;
      margin: 5rem auto 2rem;
    }

    h1, h2 {
      color: #1e3d59;
    }

    .form-label {
      font-weight: 600;
      color: #34495e;
    }

    .headline {
      background: #fff0f0;
      border: 2px solid #d32f2f;
      padding: 2.5rem 2rem;
      border-radius: 1rem;
      box-shadow: 0 8px 24px rgba(211,47,47,0.12);
      margin: 5rem auto 2rem;
    }

    .form-section {
      display: none;
      animation: fadeIn 0.3s ease-in-out;
    }

    .form-section.active {
      display: block;
    }

    .navigation-buttons {
      text-align: center;
      margin-top: 2rem;
    }

    .btn {
      padding: 0.5rem 1.5rem;
      font-weight: 500;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .navbar-toggler {
      background-color: #ffffff;
    }

    @media (max-width: 768px) {
      .navbar-nav {
        text-align: center;
      }
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
          <li class="nav-item"><a class="nav-link" href="/ambulatorio/dashboard.php">Inicio</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Contenedor del formulario -->
  <div class="container-fluid py-4">
    <div class="form-container">
      <h1 class="text-center mb-4">Registro de Historial Clínico Integral de Paciente</h1>
      <form action="registro.php" method="post" class="form-clinic">

        <!-- Módulo 1: Datos del paciente -->
        <div class="module active form-section" id="module1">
          <div class="headline"><h2 class="h5">Datos del paciente</h2></div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">C.I.</label><input class="form-control" type="number" name="ci" required></div>
            <div class="col-md-6"><label class="form-label">Apellidos</label><input class="form-control" type="text" name="apellidos" required></div>
            <div class="col-md-6"><label class="form-label">Nombres</label><input class="form-control" type="text" name="nombres" required></div>
            <div class="col-md-6"><label class="form-label">Ocupación</label><input class="form-control" type="text" name="ocupacion"></div>
            <div class="col-md-6">
              <label class="form-label">Sexo</label>
              <select class="form-select" name="sexo" required>
                <option disabled selected>Seleccione</option>
                <option value="Femenino">Femenino</option>
                <option value="Masculino">Masculino</option>
              </select>
            </div>
            <div class="col-md-4"><label class="form-label">Fecha de nacimiento</label><input class="form-control" type="date" name="fecha_nacimiento" required></div>
            <div class="col-md-8"><label class="form-label">Lugar de nacimiento</label><input class="form-control" type="text" name="lugar_nacimiento" required></div>
            <div class="col-md-6"><label class="form-label">Estado</label><input class="form-control" type="text" name="estado"></div>
            <div class="col-md-6"><label class="form-label">País</label><input class="form-control" type="text" name="pais"></div>
            <div class="col-md-8"><label class="form-label">Dirección</label><input class="form-control" type="text" name="direccion"></div>
            <div class="col-md-4"><label class="form-label">Teléfono</label><input class="form-control" type="tel" name="telefono" placeholder="+58 0414-1234567"></div>
          </div>
        </div>

        <!-- Módulo 2: Examen Físico -->
        <div class="module form-section" id="module2">
          <div class="headline"><h2 class="h5">Examen Físico</h2></div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Consulta No.:</label><input class="form-control" type="text" name="consulta_no"></div>
            <div class="col-md-4"></div>
            <div class="col-md-4"><label class="form-label">Peso</label><input class="form-control" type="number" step="0.1" name="peso"></div>
            <div class="col-md-4"><label class="form-label">Talla</label><input class="form-control" type="number" step="0.1" name="talla"></div>
            <div class="col-md-4"><label class="form-label">Tensión Arterial</label><input class="form-control" type="number" step="0.1" name="tension_arterial"></div>
          </div>
        </div>

        <!-- Módulo 5: Observaciones -->
        <div class="module form-section" id="module5">
          <div class="headline"><h2 class="h5">Observaciones</h2></div>
          <div class="row">
            <div class="col-12"><label class="form-label">Observaciones</label><textarea class="form-control" rows="5" name="observaciones"></textarea></div>
            <div class="col-12 mt-3"><button class="btn btn-success" type="submit">Guardar Historia Clínica</button></div>
          </div>
        </div>
      </form>

      <!-- Botones de navegación -->
      <div class="navigation-buttons">
        <button type="button" class="back d-none btn btn-outline-primary" onclick="prevModule()">← Atrás</button>
        <button type="button" class="next btn btn-primary" onclick="nextModule()">Siguiente →</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    let currentModule = 0;
    const backButton = document.querySelector('.back');
    const nextButton = document.querySelector('.next');
    const modules = document.querySelectorAll('.module');

    function initModules() {
      modules.forEach((module, index) => {
        module.classList.toggle('active', index === 0);
      });
      updateButtons();
    }

    function updateButtons() {
      backButton.classList.toggle('d-none', currentModule === 0);
      nextButton.classList.toggle('d-none', currentModule === modules.length - 1);
    }

    function showModule(index) {
      modules.forEach((module, i) => {
        module.classList.toggle('active', i === index);
      });
      currentModule = index;
      updateButtons();
      modules[index].scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function nextModule() {
      if (currentModule < modules.length - 1) {
        showModule(currentModule + 1);
      }
    }

    function prevModule() {
      if (currentModule > 0) {
        showModule(currentModule - 1);
      }
    }

    document.addEventListener('DOMContentLoaded', initModules);
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
