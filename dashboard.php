<?php
session_start();
// Verificación de sesión (opcional)
if (!isset($_SESSION['nombre_usu'])) {
    // header("Location: index.php");
}
$nombre_usuario = isset($_SESSION['nombre_usu']) ? $_SESSION['nombre_usu'] : 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ambulatorio Libertador I | Inicio</title>
  
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    /* --- ESTILOS GENERALES --- */
    body {
      background-color: #eef2f6; 
      font-family: 'Segoe UI', system-ui, sans-serif;
      min-height: 100vh;
    }

    /* --- NAVBAR --- */
    .navbar {
      background: linear-gradient(90deg, #aa0b0b 0%, #003366 100%);
      padding: 10px 0;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .navbar-brand {
      color: #fff !important;
      font-weight: 600;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logo {
      height: 50px;
      width: 50px;
      background: white;
      border-radius: 50%;
      padding: 2px;
      object-fit: cover; 
    }

    .btn-salir {
        color: white;
        border: 1px solid rgba(255,255,255,0.5);
        padding: 5px 15px;
        border-radius: 20px;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 14px;
    }
    
    .btn-salir:hover {
        background: rgba(255,255,255,0.1);
        border-color: white;
        color: white;
    }

    /* --- HERO SECTION --- */
    .hero-section {
      background: white;
      padding: 40px 20px;
      border-radius: 0 0 30px 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      text-align: center;
      margin-bottom: 40px;
      margin-top: 70px;
    }

    .hero-section h1 {
      color: #003366;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .hero-section p {
      color: #666;
      font-size: 0.95rem;
    }

    /* --- TARJETAS --- */
    .dashboard-container {
      padding-bottom: 50px;
    }

    .card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .card-menu {
      background: white;
      border: none;
      border-radius: 20px;
      padding: 30px 20px;
      text-align: center;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      height: 100%; 
      cursor: pointer;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .card-menu:hover {
      transform: translateY(-10px); 
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .icon-wrapper {
      width: 80px;
      height: 80px;
      background: #f8f9fa;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      transition: background 0.3s;
      font-size: 32px; 
      color: #003366;
    }

    .card-menu:hover .icon-wrapper {
      background: #e3e9f0; 
      color: #aa0b0b;
    }

    .card-menu img {
      width: 50px;
      height: 50px;
      object-fit: contain;
    }

    .card-menu h5 {
      color: #333;
      font-weight: 600;
      margin: 0;
      font-size: 1.1rem;
    }

    /* Ajustes Responsive */
    @media (max-width: 768px) {
      .hero-section { padding: 30px 15px; }
      .navbar-brand { font-size: 0.9rem; }
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        <img src="icons/logo.png" alt="Logo" class="logo" />
        <div>
            <div>Ambulatorio</div>
            <div style="font-size: 0.8em; font-weight: 300;">Libertador Urbano I</div>
        </div>
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="btn-salir" href="cerrar.php">Cerrar Sesión</a>
              </li>
          </ul>
      </div>
    </div>
  </nav>

  <div class="hero-section">
      <div class="container">
        <h1>Hola, <?php echo htmlspecialchars($nombre_usuario); ?></h1>
        <p>Panel de Control General</p>
      </div>
  </div>

  <div class="container dashboard-container">
    <!-- Se mantiene el justificado al centro -->
    <div class="row g-4 justify-content-center">
      
      <!-- Módulo 1 -->
      <div class="col-12 col-sm-6 col-md-6 col-lg-5">
        <a href="Personal_medico/personal.php" class="card-link">
          <div class="card-menu">
            <div class="icon-wrapper">
              <img src="icons/afiliado.png" alt="Personal" onerror="this.style.display='none'; this.parentElement.innerHTML='👨‍⚕️';"/>
            </div>
            <h5>Personal Médico</h5>
          </div>
        </a>
      </div>

      <!-- Módulo 2 -->
      <div class="col-12 col-sm-6 col-md-6 col-lg-5">
        <a href="Pacientes/pacientes.php" class="card-link">
          <div class="card-menu">
            <div class="icon-wrapper">
              <img src="icons/nuevo_ingreso.png" alt="Pacientes" onerror="this.style.display='none'; this.parentElement.innerHTML='🤒';"/>
            </div>
            <h5>Pacientes</h5>
          </div>
        </a>
      </div>

      <!-- Módulo 3 -->
      <div class="col-12 col-sm-6 col-md-6 col-lg-5">
        <a href="vacunas/jornada.php" class="card-link">
          <div class="card-menu">
            <div class="icon-wrapper">
              <img src="icons/unidad_medica.png" alt="Vacunas" onerror="this.style.display='none'; this.parentElement.innerHTML='💉';"/>
            </div>
            <h5>Jornadas de Vacunaciones</h5>
          </div>
        </a>
      </div>

      <!-- Módulo 4 -->
      <div class="col-12 col-sm-6 col-md-6 col-lg-5">
        <a href="Citas/citas.php" class="card-link">
          <div class="card-menu">
            <div class="icon-wrapper">
                <img src="icons/citas.png" alt="Triaje" onerror="this.style.display='none'; this.parentElement.innerHTML='📋';"/>
            </div>
            <h5>Citas Médicas</h5>
          </div>
        </a>
      </div>

    </div>
  </div>

  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>