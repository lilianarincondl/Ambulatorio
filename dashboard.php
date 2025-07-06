<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ambulatorio Libertador I</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Segoe UI', sans-serif;
    }

    .navbar {
      background-color:  #aa0b0b;
      padding: 0.2rem 0.5rem; 
    }

    .navbar-brand,
    .nav-link {
      color: #fff !important;
      font-weight: 500;
    }

    .nav-link:hover {
      color: #cce5ff !important;
    }

    .logo {
      height: 40px; /* logo */
      margin-right: 10px;
    }

    .hero {
      background-color:  #aa0b0b;
      color: white;
      text-align: center;
      padding: 30px 20px;
    }

    .hero h1 {
      font-weight: bold;
    }

    .hero p {
      font-style: italic;
      margin-top: 10px;
    }

    .dashboard {
      padding: 40px 20px;
    }

    .icon-card {
      border-radius: 15px;
      background-color: #eeeeee;
      text-align: center;
      padding: 20px;
      transition: transform 0.2s ease;
      cursor: pointer;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* sombra  */
    }

    .icon-card:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); /* sombra - mouse */
    }

    .icon-card img {
      width: 60px;
      margin-bottom: 15px;
    }

    .icon-card h5 {
      font-weight: bold;
    }

    @media (max-width: 768px) {
      .navbar-nav {
        text-align: center;
      }
    }
  </style>
</head>
<body>

<?php
  session_start(); // Iniciar la sesión
  ?>

  <!-- Barra de Navegación -->
  <nav class="navbar navbar-expand-lg fixed-top shadow">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="icons/logo.png" alt="Logo Ambulatorio" class="logo" />
        Ambulatorio Libertador
      </a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Encabezado principal -->
  <div class="hero mt-5">
     <h1> Bienvenido(a): <?php echo $_SESSION['nombre_usu'];?></h1>
  </div>

  <!-- Dashboard de opciones -->
  <div class="container dashboard">
    <div class="row g-4">
      <div class="col-6 col-md-4">
        <a href="registros_medicos.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/afiliado.png" alt="Afiliado" />
            <h5>Registros Médicos</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4">
        <a href="registro_pacientes.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/nuevo_ingreso.png" alt="Nuevo Ingreso" />
            <h5>Ingresar Nuevo Registro</h5>
          </div>
        </a>
      </div>

      <div class="col-6 col-md-4">
        <a href="ver_jornadas.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/control_medico.png" alt="Control Médico" />
            <h5>55</h5>
          </div>
      </div>


      <div class="col-6 col-md-4">
        <a href="citas_médicas.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/citas.png" alt="Citas" />
            <h5>Citas</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4">
        <a href="registrar_jornada.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/unidad_medica.png" alt="Vacunas" />
            <h5>Vacunas</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4">
        <a href="usuarios.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/usuarios.png" alt="Usuarios" />
            <h5>Usuarios</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4">
        <a href="configuracion.html" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/configuracion.png" alt="Configuración" />
            <h5>Configuración</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4">
        <a href="cerrar.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/salir.png" alt="Cerrar Sesión" />
            <h5>Cerrar Sesión</h5>
          </div>
        </a>
      </div>
      <div class="col-6 col-md-4">
        <a href="reportes.php" class="text-decoration-none text-dark">
          <div class="icon-card">
            <img src="icons/reportes.png" alt="Reportes" />
            <h5>Reportes & Estadísticas</h5>
          </div>
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
