<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Fallo BD: " . $conn->connect_error); }
$conn->set_charset("utf8");

// Validar ID
if (!isset($_GET['id'])) { header("Location: citas.php"); exit(); }
$id_cita = intval($_GET['id']);

// Buscar datos
$sql = "SELECT c.*, m.nombre as nombre_medico 
        FROM citas c 
        INNER JOIN usuario_medico m ON c.id_medico = m.id 
        WHERE c.id = $id_cita";
$resultado = $conn->query($sql);

if ($resultado->num_rows == 0) { header("Location: citas.php"); exit(); }
$cita = $resultado->fetch_assoc();

// Recuperar error si existe
$error = isset($_SESSION['registro_error']) ? $_SESSION['registro_error'] : '';
unset($_SESSION['registro_error']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cita | Ambulatorio</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <style>
        body { background-color: #eef2f6; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(90deg, #aa0b0b 0%, #003366 100%); }
        .card { border-radius: 20px; border: none; margin-top: 30px; }
        .card-header { background-color: #003366; color: white; border-radius: 20px 20px 0 0 !important; }
        .btn-guardar { background-color: #28a745; color: white; border-radius: 50px; }
        .btn-cancelar { background-color: #6c757d; color: white; border-radius: 50px; }
        
        /* Estilo para campos bloqueados */
        .bloqueado { 
            background-color: #e9ecef !important; 
            cursor: not-allowed; 
            color: #6c757d;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="citas.php">← Volver</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <?php if($error): ?>
                    <div class="alert alert-danger mt-4"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-header">Editar Cita #<?php echo $cita['id']; ?></div>
                    <div class="card-body p-4">
                        
                        <form action="actualizar_cita.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $cita['id']; ?>">
                            
                            <input type="hidden" name="id_medico" value="<?php echo $cita['id_medico']; ?>">
                            <div class="form-group mb-3">
                                <label>Médico (No editable):</label>
                                <input type="text" class="form-control bloqueado" value="Dr/a. <?php echo $cita['nombre_medico']; ?>" readonly>
                                <small class="text-muted">Para cambiar el médico, cree una nueva cita.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Fecha (No editable):</label>
                                    <input type="date" name="fecha" class="form-control bloqueado" 
                                           value="<?php echo $cita['fecha']; ?>" readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Hora (No editable):</label>
                                    <input type="time" name="hora" class="form-control bloqueado" 
                                           value="<?php echo $cita['hora']; ?>" readonly>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-primary">Estado de la Cita:</label>
                                <select name="estado" class="form-control font-weight-bold" style="border: 2px solid #007bff;">
                                    <option value="Pendiente" <?php if($cita['estado']=='Pendiente') echo 'selected'; ?>>🟡 Pendiente</option>
                                    <option value="Atendida"  <?php if($cita['estado']=='Atendida') echo 'selected'; ?>>🟢 Atendida</option>
                                    <option value="Cancelada" <?php if($cita['estado']=='Cancelada') echo 'selected'; ?>>🔴 Cancelada</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label>Paciente:</label>
                                <select name="id_paciente" class="form-control">
                                    <?php
                                    $res_p = $conn->query("SELECT id, nombres, cedula FROM pacientes ORDER BY nombres ASC");
                                    while($row = $res_p->fetch_assoc()){
                                        $sel = ($row['id'] == $cita['id_paciente']) ? 'selected' : '';
                                        echo "<option value='".$row['id']."' $sel>".$row['nombres']." (V-".$row['cedula'].")</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label>Motivo:</label>
                                <textarea name="motivo" class="form-control" rows="2" required><?php echo htmlspecialchars($cita['motivo']); ?></textarea>
                            </div>

                            <div class="text-right">
                                <a href="citas.php" class="btn btn-cancelar mr-2">Cancelar</a>
                                <button type="submit" class="btn btn-guardar">Guardar Cambios</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>