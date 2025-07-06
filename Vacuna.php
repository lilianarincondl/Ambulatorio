<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro de Vacunas</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #e9edf4;
      padding-top: 70px;
    }

    .navbar {
      background-color: #0d6efd !important;
    }

    .navbar .navbar-brand,
    .navbar .nav-link {
      color: white !important;
      font-weight: bold;
    }

    h1 {
      color: #1A237E;
      margin-bottom: 20px;
    }

    .form-section {
      background-color: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
      margin-bottom: 30px;
    }

    label {
      font-weight: bold;
    }

    input[type="submit"] {
      background-color: #1A237E;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      margin-top: 15px;
    }

    input[type="submit"]:hover {
      background-color: #0f1a5c;
    }

    .vaccines-group {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg fixed-top shadow">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="icons/logo.png" alt="Logo Ambulatorio" style="height: 40px; margin-right: 10px;">
        Ambulatorio Libertador
      </a>
      <div class="collapse navbar-collapse justify-content-end">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Inicio</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- FORMULARIO -->
  <div class="container">
    <h1 class="text-center">Registro de Vacunas</h1>
    <div class="form-section">
      <form action="guardar_vacunas.php" method="post">
        <h3>Vacunas y Dosis</h3>

        <div class="vaccines-group">
          <label>BCG:</label><br>
          <input type="checkbox" name="vacunas[bcg][]" value="DU"> DU
        </div>

        <div class="vaccines-group">
          <label>Hepatitis B:</label><br>
          <input type="checkbox" name="vacunas[hepatitis_b][]" value="Du"> Du
          <input type="checkbox" name="vacunas[hepatitis_b][]" value="Da"> Da
          <input type="checkbox" name="vacunas[hepatitis_b][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[hepatitis_b][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[hepatitis_b][]" value="3D"> 3D
        </div>

        <div class="vaccines-group">
          <label>Rotavirus:</label><br>
          <input type="checkbox" name="vacunas[rotavirus][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[rotavirus][]" value="2D"> 2D
        </div>

        <div class="vaccines-group">
          <label>Pentavalente:</label><br>
          <input type="checkbox" name="vacunas[pentavalente][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[pentavalente][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[pentavalente][]" value="3D"> 3D
          <input type="checkbox" name="vacunas[pentavalente][]" value="1REF"> 1REF
          <input type="checkbox" name="vacunas[pentavalente][]" value="2REF"> 2REF
        </div>

        <div class="vaccines-group">
          <label>Polio inyectable:</label><br>
          <input type="checkbox" name="vacunas[polio_inyectable][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[polio_inyectable][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[polio_inyectable][]" value="3D"> 3D
        </div>

        <div class="vaccines-group">
          <label>Polio oral:</label><br>
          <input type="checkbox" name="vacunas[polio_oral][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[polio_oral][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[polio_oral][]" value="3D"> 3D
          <input type="checkbox" name="vacunas[polio_oral][]" value="1REF"> 1REF
          <input type="checkbox" name="vacunas[polio_oral][]" value="2REF"> 2REF
        </div>

        <div class="vaccines-group">
          <label>Neumococo conjugada:</label><br>
          <input type="checkbox" name="vacunas[neumococo_conjugada][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[neumococo_conjugada][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[neumococo_conjugada][]" value="1REF"> 1REF
        </div>

        <div class="vaccines-group">
          <label>Influenza estacional:</label><br>
          <input type="checkbox" name="vacunas[influenza_estacional][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[influenza_estacional][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[influenza_estacional][]" value="1DE"> 1DE
        </div>

        <div class="vaccines-group">
          <label>Fiebre amarilla:</label><br>
          <input type="checkbox" name="vacunas[fiebre_amarilla][]" value="DU"> DU
        </div>

        <div class="vaccines-group">
          <label>Sarampión, rubéola, parotiditis:</label><br>
          <input type="checkbox" name="vacunas[sarampion_rubeola_parotiditis][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[sarampion_rubeola_parotiditis][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[sarampion_rubeola_parotiditis][]" value="1DA"> 1DA
        </div>

        <div class="vaccines-group">
          <label>Toxoide tetánico diftérico:</label><br>
          <input type="checkbox" name="vacunas[toxoide_tetanico_difterico][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[toxoide_tetanico_difterico][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[toxoide_tetanico_difterico][]" value="3D"> 3D
          <input type="checkbox" name="vacunas[toxoide_tetanico_difterico][]" value="4D"> 4D
          <input type="checkbox" name="vacunas[toxoide_tetanico_difterico][]" value="5D"> 5D
          <input type="checkbox" name="vacunas[toxoide_tetanico_difterico][]" value="DA"> DA
        </div>

        <div class="vaccines-group">
          <label>Neumococo polisacárida:</label><br>
          <input type="checkbox" name="vacunas[neumococo_polisacarida][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[neumococo_polisacarida][]" value="1REF"> 1REF
        </div>

        <div class="vaccines-group">
          <label>Meningocócica:</label><br>
          <input type="checkbox" name="vacunas[meningococica][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[meningococica][]" value="2D"> 2D
        </div>

        <div class="vaccines-group">
          <label>Antirrábica:</label><br>
          <strong>Pre:</strong><br>
          <input type="checkbox" name="vacunas[antirrabica_pre][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[antirrabica_pre][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[antirrabica_pre][]" value="1REF"> 1REF<br>
          
          <strong>Pos:</strong><br>
          <input type="checkbox" name="vacunas[antirrabica_pos][]" value="1D"> 1D
          <input type="checkbox" name="vacunas[antirrabica_pos][]" value="2D"> 2D
          <input type="checkbox" name="vacunas[antirrabica_pos][]" value="3D"> 3D
          <input type="checkbox" name="vacunas[antirrabica_pos][]" value="4D"> 4D
          <input type="checkbox" name="vacunas[antirrabica_pos][]" value="5D"> 5D
          <input type="checkbox" name="vacunas[antirrabica_pos][]" value='6'> D6
          <input type="checkbox" name="vacunas[antirrabica_pos][]" value='7'> 7D
        </div>

        <!-- Botón Guardar -->
        <input type="submit" value="Guardar" class="btn btn-primary">
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
