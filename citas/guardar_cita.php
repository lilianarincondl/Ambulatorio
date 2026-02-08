<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Conexión fallida: " . $conn->connect_error); }
$conn->set_charset("utf8");

// Recibir datos
$id_paciente = isset($_POST['id_paciente']) ? intval($_POST['id_paciente']) : 0;
$id_medico   = isset($_POST['id_medico']) ? intval($_POST['id_medico']) : 0;
$fecha       = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$hora        = isset($_POST['hora']) ? $_POST['hora'] : '';
$motivo      = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
$estado      = 'Pendiente';

// --- NUEVO: Función para guardar datos y volver ---
function volverConError($mensaje) {
    global $id_paciente, $id_medico, $fecha, $hora, $motivo;
    
    $_SESSION['registro_error'] = $mensaje;
    // Guardamos lo que el usuario escribió para no borrarlo
    $_SESSION['datos_temporales'] = [
        'id_paciente' => $id_paciente,
        'id_medico' => $id_medico,
        'fecha' => $fecha,
        'hora' => $hora,
        'motivo' => $motivo
    ];
    header("Location: nueva_cita.php");
    exit();
}
// ------------------------------------------------

// Validaciones
if ($id_paciente <= 0 || $id_medico <= 0 || empty($fecha) || empty($hora)) {
    volverConError('Todos los campos son obligatorios.');
}

// Validación de disponibilidad del médico
$sql_check = "SELECT id FROM citas WHERE id_medico = ? AND fecha = ? AND hora = ? AND estado != 'Cancelada'";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iss", $id_medico, $fecha, $hora);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    volverConError('El médico seleccionado ya tiene una cita en ese horario exacto.');
}
$stmt_check->close();

// Insertar
$sql_insert = "INSERT INTO citas (id_paciente, id_medico, fecha, hora, motivo, estado) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql_insert);
$stmt->bind_param("iissss", $id_paciente, $id_medico, $fecha, $hora, $motivo, $estado);

if ($stmt->execute()) {
    // Si se guarda bien, borramos los datos temporales por si acaso
    if(isset($_SESSION['datos_temporales'])) unset($_SESSION['datos_temporales']);
    
    header("Location: citas.php?exito=1");
    exit();
} else {
    volverConError('Error en la base de datos: ' . $stmt->error);
}

$stmt->close();
$conn->close();
?>