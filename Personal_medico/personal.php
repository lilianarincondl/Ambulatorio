<?php
// 1. LÓGICA PHP AL PRINCIPIO
session_start();

// Conexión
$conn = new mysqli('localhost', 'root', '', 'ambulatorio');
if ($conn->connect_error) { die("Conexión fallida: " . $conn->connect_error); }
$conn->set_charset("utf8");

// Lógica de Borrado
if (isset($_GET['borrar'])) {
    $id_borrar = base64_decode($_GET['borrar']);
    $id_borrar = intval($id_borrar);
    
    // Evitar borrar al admin principal (ID 1)
    if ($id_borrar > 1) { 
        $stmt = $conn->prepare("DELETE FROM usuario_medico WHERE id = ?");
        $stmt->bind_param("i", $id_borrar);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: personal.php");
    exit();
}

// Lógica de Búsqueda (Actualizada para incluir CARGO)
$where = "WHERE id != 1"; // Siempre ocultar al super-admin
if (!empty($_GET['busqueda'])) {
    $busqueda = $conn->real_escape_string($_GET['busqueda']);
    // Agregamos 'OR cargo LIKE...'
    $where .= " AND (nombre LIKE '%$busqueda%' OR cedula LIKE '%$busqueda%' OR correo LIKE '%$busqueda%' OR cargo LIKE '%$busqueda%')";
}

$sql = "SELECT * FROM usuario_medico $where ORDER BY nombre ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Personal Médico | Ambulatorio</title>
  
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    /* --- ESTILO GENERAL --- */
    body { background: #eef2f6; font-family: 'Segoe UI', system-ui, sans-serif; }

    /* --- NAVBAR --- */
    .navbar { background: linear-gradient(90deg, #aa0b0b 0%, #003366 100%); padding: 10px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 600; color: white !important; font-size: 1.1rem; }

    .btn-volver {
        color: white; border: 1px solid rgba(255,255,255,0.5); padding: 5px 15px;
        border-radius: 20px; text-decoration: none; font-size: 14px; transition: 0.3s;
    }
    .btn-volver:hover { background: rgba(255,255,255,0.2); color: white; }

    /* --- TARJETA PRINCIPAL --- */
    .main-card {
      background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      padding: 30px; margin-top: 30px; margin-bottom: 30px;
    }

    /* --- TABLA MODERNA --- */
    .table-container { border-radius: 15px; overflow: hidden; border: 1px solid #eee; }
    .table thead { background-color: #003366; color: white; }
    .table th { font-weight: 500; padding: 15px; border: none; }
    .table td { padding: 15px; vertical-align: middle; color: #555; }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }

    /* --- BOTONES DE ACCIÓN --- */
    .btn-action { width: 35px; height: 35px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; border: none; transition: 0.2s; }
    .btn-edit { background: #e3f2fd; color: #003366; }
    .btn-edit:hover { background: #003366; color: white; }
    .btn-delete { background: #ffebee; color: #aa0b0b; }
    .btn-delete:hover { background: #aa0b0b; color: white; }

    /* Barra de búsqueda */
    .search-input { border-radius: 20px 0 0 20px; border: 1px solid #ced4da; padding-left: 20px; }
    .btn-search { border-radius: 0 20px 20px 0; background: #003366; color: white; border: none; }
    
    /* Etiqueta de Cargo */
    .badge-cargo {
        background-color: #e9ecef; color: #495057; 
        padding: 5px 10px; border-radius: 10px; 
        font-size: 0.9em; font-weight: 600;
    }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" onerror="this.style.display='none'"/>
        <span>Personal Médico</span>
      </a>
      
      <div class="ms-auto">
        <a class="btn-volver" href="../dashboard.php">← Volver al Inicio</a>
      </div>
    </div>
  </nav>

  <div style="height: 70px;"></div>

  <div class="container">
    <div class="main-card">
      
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
          <div>
              <h2 style="color: #003366; font-weight: 700; margin: 0;">Gestión de Personal</h2>
              <p class="text-muted m-0">Administra los doctores, enfermeros y administradores</p>
          </div>
          <a href="registrar_personal.php" class="btn btn-danger" style="background: #aa0b0b; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500;">
            + Nuevo Personal
          </a>
      </div>

      <div class="row mb-4">
          <div class="col-md-6">
              <form class="d-flex" method="get" action="">
                <input class="form-control search-input" type="search" name="busqueda" 
                       placeholder="Buscar por nombre, cargo o cédula..." 
                       value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ?>">
                <button class="btn btn-search px-4" type="submit">Buscar</button>
                
                <?php if(!empty($_GET['busqueda'])): ?>
                    <a href="personal.php" class="btn btn-light ms-2" style="border-radius: 20px; border: 1px solid #ddd;">Limpiar</a>
                <?php endif; ?>
              </form>
          </div>
      </div>

      <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre Completo</th>
                  <th>Cargo / Especialidad</th> <th>Cédula</th>
                  <th>Correo Electrónico</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                  $contador = 1;
                  while ($row = $result->fetch_assoc()) {
                    $id_cifrado = base64_encode($row['id']);
                ?>
                    <tr>
                      <td><?php echo $contador++; ?></td>
                      <td style="font-weight: 500; color: #003366;"><?php echo htmlspecialchars($row['nombre']); ?></td>
                      
                      <td>
                          <span class="badge-cargo"><?php echo htmlspecialchars($row['cargo']); ?></span>
                      </td>
                      
                      <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                      <td><?php echo htmlspecialchars($row['correo']); ?></td>
                      
                      <td class="text-center">
                        <a href="editar_personal.php?id=<?php echo $id_cifrado; ?>" class="btn-action btn-edit me-1" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                        </a>
                        
                        <a href="personal.php?borrar=<?php echo $id_cifrado; ?>" class="btn-action btn-delete" 
                           onclick="return confirm('¿Estás seguro de eliminar a <?php echo $row['nombre']; ?>?');" title="Eliminar">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/></svg>
                        </a>
                      </td>
                    </tr>
                <?php 
                  }
                } else {
                  echo '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron registros de personal.</td></tr>';
                }
                ?>
              </tbody>
            </table>
        </div>
      </div>
      
    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>