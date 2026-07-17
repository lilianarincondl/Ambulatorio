<?php
session_start();

// 1. CONEXIÓN
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Fallo BD: " . $conn->connect_error); }
$conn->set_charset("utf8");

// 2. RECUPERAR DATOS TEMPORALES
$temp = [
    'id_paciente' => '',
    'id_medico' => '',
    'fecha' => '',
    'hora' => '',
    'motivo' => ''
];

if (isset($_SESSION['datos_temporales'])) {
    $temp = $_SESSION['datos_temporales'];
    unset($_SESSION['datos_temporales']); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Cita | Ambulatorio</title>
    
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    
    <style>
        body { background-color: #eef2f6; font-family: 'Segoe UI', system-ui, sans-serif; }
        
        /* NAVBAR */
        .navbar { background: linear-gradient(90deg, #aa0b0b 0%, #003366 100%); padding: 10px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .navbar-brand { color: #fff !important; font-weight: 600; font-size: 1.1rem; display: flex; align-items: center; gap: 15px; }
        .logo { height: 50px; width: 50px; background: white; border-radius: 50%; padding: 2px; object-fit: cover; }
        
        /* BOTÓN VOLVER */
        .btn-volver { color: white; border: 1px solid rgba(255,255,255,0.5); padding: 5px 15px; border-radius: 20px; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .btn-volver:hover { background: rgba(255,255,255,0.2); color: white; text-decoration: none; }

        /* TARJETA */
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-top: 30px; margin-bottom: 30px; overflow: hidden; }
        .card-header { background-color: #003366; color: white; padding: 20px; font-weight: 600; font-size: 1.1rem; }
        
        /* BOTONES */
        .btn-guardar { background-color: #28a745; border: none; padding: 10px 30px; border-radius: 50px; font-weight: bold; color: white; transition: 0.3s; }
        .btn-guardar:hover { background-color: #218838; transform: translateY(-2px); }
        .btn-cancelar { background-color: #6c757d; border: none; padding: 10px 30px; border-radius: 50px; color: white; text-decoration: none; transition: 0.3s; }
        .btn-cancelar:hover { background-color: #5a6268; color: white; text-decoration: none; }
        
        label { font-weight: 500; color: #555; margin-bottom: 5px; }

        /* Estilo para el buscador simple */
        .input-filtro { border-bottom: none; border-radius: 5px 5px 0 0; background-color: #f8f9fa; font-size: 0.9em; }
        .select-filtrable { border-radius: 0 0 5px 5px; border-top: 1px solid #ced4da; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="citas.php">
                <img src="../icons/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'"/>
                <div>
                    <div>Ambulatorio</div>
                    <div style="font-size: 0.8em; font-weight: 300;">Libertador Urbano I</div>
                </div>
            </a>
            <div class="ms-auto">
                <a class="btn-volver" href="citas.php">← Volver a la Lista</a>
            </div>
        </div>
    </nav>

    <div style="height: 80px;"></div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                
                <?php if(isset($_SESSION['registro_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-4 shadow-sm" role="alert" style="border-radius: 15px;">
                        <strong>⚠️ Atención:</strong> <?php echo $_SESSION['registro_error']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['registro_error']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        Agendar Nueva Cita
                    </div>
                    
                    <div class="card-body p-4">
                        <form action="guardar_cita.php" method="POST">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Paciente:</label>
                                    <input type="text" id="filtro_paciente" class="form-control input-filtro" placeholder="🔍 Buscar nombre, apellido o cédula..." autocomplete="off">
                                    <select name="id_paciente" id="select_paciente" class="form-control select-filtrable" required>
                                        <option value="">-- Seleccione un Paciente --</option>
                                        <?php
                                        // MODIFICACIÓN AQUÍ: Se añadió 'apellidos' a la consulta SQL
                                        $res_p = $conn->query("SELECT id, nombres, apellidos, cedula FROM pacientes ORDER BY nombres ASC");
                                        while($row = $res_p->fetch_assoc()){
                                            $selected = ($row['id'] == $temp['id_paciente']) ? 'selected' : '';
                                            // MODIFICACIÓN AQUÍ: Se unen nombres, apellidos y cédula en el texto de la opción
                                            $nombre_completo = htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']);
                                            echo "<option value='".$row['id']."' $selected>".$nombre_completo." (V-".$row['cedula'].")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Médico Asignado:</label>
                                    <select name="id_medico" class="form-control" required>
                                        <option value="">-- Seleccione un Médico --</option>
                                        <?php
                                        $sql_medicos = "SELECT id, nombre, cargo FROM usuario_medico 
                                                        WHERE cargo NOT LIKE '%Enfermera%' 
                                                        AND cargo NOT LIKE '%Admin%' 
                                                        ORDER BY nombre ASC";
                                        
                                        $res_m = $conn->query($sql_medicos);
                                        
                                        if (!$res_m) {
                                            $sql_medicos = "SELECT id, nombre FROM usuario_medico ORDER BY nombre ASC";
                                            $res_m = $conn->query($sql_medicos);
                                        }

                                        while($row = $res_m->fetch_assoc()){
                                            $selected = ($row['id'] == $temp['id_medico']) ? 'selected' : '';
                                            
                                            $especialidad = isset($row['cargo']) ? " (" . $row['cargo'] . ")" : "";
                                            
                                            echo "<option value='".$row['id']."' $selected>Dr/a. ".$row['nombre'].$especialidad."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label>Fecha:</label>
                                    <input type="date" name="fecha" class="form-control" required 
                                           min="<?php echo date('Y-m-d'); ?>" 
                                           value="<?php echo htmlspecialchars($temp['fecha']); ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Hora:</label>
                                    <input type="time" name="hora" class="form-control" required 
                                           value="<?php echo htmlspecialchars($temp['hora']); ?>">
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label>Motivo de la Consulta:</label>
                                    <textarea name="motivo" class="form-control" rows="3" placeholder="Ej: Dolor abdominal fuerte..." required><?php echo htmlspecialchars($temp['motivo']); ?></textarea>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="citas.php" class="btn btn-cancelar mr-2">Cancelar</a>
                                <button type="submit" class="btn btn-guardar">Guardar Cita</button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="../bootstrap/js/jquery.min.js"></script>
    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var filtroInput = document.getElementById('filtro_paciente');
            var selectPaciente = document.getElementById('select_paciente');
            var opcionesOriginales = Array.from(selectPaciente.options);

            filtroInput.addEventListener('keyup', function() {
                var texto = this.value.toLowerCase();
                selectPaciente.innerHTML = '';
                var encontrados = 0;
                opcionesOriginales.forEach(function(opcion) {
                    var textoOpcion = opcion.text.toLowerCase();
                    if (opcion.value === "" || textoOpcion.includes(texto)) {
                        selectPaciente.appendChild(opcion);
                        if(opcion.value !== "") encontrados++;
                    }
                });
                if(encontrados === 0 && texto !== "") {
                    var aviso = document.createElement("option");
                    aviso.text = "⚠️ No se encontraron resultados";
                    aviso.disabled = true;
                    selectPaciente.appendChild(aviso);
                }
            });
        });
    </script>

</body>
</html>
<?php $conn->close(); ?>