<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Personal médico</title>
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
      background: #e3efff;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      margin: 1.5rem 0 1rem;
      border-left: 5px solid  #aa0b0b;;
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
          <li class="nav-item"><a class="nav-link" href="../dashboard.php">Inicio</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- CRUD Personal Médico -->
  <div class="container-fluid py-4">
    <div class="form-container">
      <h1 class="text-center mb-4">Personal Médico</h1>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <form class="d-flex" method="get" action="">
          <input class="form-control me-2" type="search" name="busqueda" placeholder="Buscar por nombre o correo" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ?>">
          <button class="btn btn-outline-primary me-2" type="submit">Buscar</button>
          <a href="personal.php" class="btn btn-secondary">Limpiar</a>
        </form>
        <a href="registrar_personal.php" class="btn btn-success">Agregar personal</a>
      </div>
      <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Cédula</th>
              <th>Correo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Conexión a la base de datos
            $conn = new mysqli('localhost', 'root', '', 'ambulatorio');
            if ($conn->connect_error) {
              die("Error de conexión: " . $conn->connect_error);
            }
            $where = "WHERE id != 1";
            if (!empty($_GET['busqueda'])) {
              $busqueda = $conn->real_escape_string($_GET['busqueda']);
              $where .= " AND (nombre LIKE '%$busqueda%' OR correo LIKE '%$busqueda%')";
            }
            // Limitar a 8 resultados por página visual
            $sql = "SELECT * FROM usuario_medico $where ORDER BY id DESC LIMIT 8";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
              $i = 1;
              while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $i++ . "</td>";
                echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cedula']) . "</td>";
                echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
                echo "<td>";
                $id_cifrado = base64_encode($row['id']);
                echo '<a href="editar_personal.php?id=' . $id_cifrado . '" class="btn btn-sm btn-primary me-1">Editar</a>';
                echo '<a href="personal.php?borrar=' . $id_cifrado . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Seguro que deseas borrar este usuario?\')">Borrar</a>';
                echo "</td>";
                echo "</tr>";
              }
            } else {
              echo '<tr><td colspan="5" class="text-center">No se encontraron resultados.</td></tr>';
            }
            // Borrar usuario
            if (isset($_GET['borrar'])) {
              $id_borrar = base64_decode($_GET['borrar']);
              $id_borrar = intval($id_borrar);
              if ($id_borrar > 1) { // Nunca permitir borrar el admin
                $conn->query("DELETE FROM usuario_medico WHERE id = $id_borrar");
              }
              echo "<script>window.location='personal.php';</script>";
            }
            $conn->close();
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

 
  <script src="../bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
