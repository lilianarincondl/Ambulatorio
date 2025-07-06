<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registros Médicos</title>

  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.bootstrap5.css">

  <style>
  body {
    background-color: #e9edf4;
    padding-top: 70px;
  }

  .navbar {
    background-color:  #aa0b0b!important;
  }

  .navbar .navbar-brand,
  .navbar .nav-link {
    color: white !important;
    font-weight: bold;
  }

  .navbar .nav-link:hover {
    color: #c5cae9 !important;
  }

  h1 {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: #1A237E;
  }

  .card {
    border: none;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
    margin-top: 20px;
    border-radius: 12px;
  }

  .card-header {
    background-color: #e6f0ff;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
  }

  .btn-success {
    background-color: #198754;
    border: none;
  }

  .btn-success:hover {
    background-color: #157347;
  }

  .btn-danger {
    background-color: #990000;
    border: none;
  }

  .btn-danger:hover {
    background-color: #cc0000;
  }

  .btn-warning {
    color: #000;
    font-weight: bold;
  }

  .table thead {
    background-color:  #aa0b0b;
    color: white;
  }

  .modal-header.bg-danger {
    background-color: #990000 !important;
  }
</style>

</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top shadow">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="icons/logo.png" alt="Logo Ambulatorio" style="height: 40px; margin-right: 10px;" />
        Ambulatorio Libertador I
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Inicio</a></li>
        </ul>
      </div>
    </div>
  </nav>
 
  <div class="container text-center">
    <h1>Registros Médicos</h1>
  </div>


  <div class="container">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-md-6 mb-2">
            <a href="reporte_pdf.php" target="_blank">
              <button class="btn btn-danger w-100">Descargar PDF</button>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body table-responsive">
        <table id="tabla_producto" class="table table-sm table-hover text-center">
          <thead>
            <tr>
              <th>CEDULA</th>
              <th>NOMBRES</th>
              <th>APELLIDOS</th>
              <th>ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $num = 1;
              foreach ($filas as $fila) {
            ?>
            <tr>
              <td><?= $num++; ?></td>
              <td><?= $fila['cedula']; ?></td>
              <td><?= $fila['nombres']; ?></td>
              <td><?= $fila['apellidos']; ?></td>
              <td>
                <a href="editar_registros.php?id=<?= base64_encode($fila['id']); ?>">
                  <button type="button" class="btn btn-warning btn-sm">Editar</button>
                </a>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modal_eliminar_<?= $fila['id']; ?>">
                  Eliminar
                </button>

                <!-- MODAL -->
                <div class="modal fade" id="modal_eliminar_<?= $fila['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-danger text-white">
                        <h1 class="modal-title fs-5">Eliminar Producto</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <h5>¿Estás seguro de eliminar el producto: <strong><?= $fila['nombre']; ?></strong>?</h5>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <a href="borrar.php?id=<?= base64_encode($fila['id']); ?>">
                          <button type="button" class="btn btn-danger">Eliminar</button>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>

              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- SCRIPTS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.2.1/js/dataTables.bootstrap5.js"></script>

  <script>
    new DataTable('#tabla_producto', {
      language: {
        url: '//cdn.datatables.net/plug-ins/2.2.1/i18n/es-ES.json'
      }
    });
  </script>
</body>
</html>
