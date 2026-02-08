<?php
// guardar.php - Procesa el registro de un nuevo paciente

session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibe datos del formulario

$cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
$nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
$ocupacion = isset($_POST['ocupacion']) ? trim($_POST['ocupacion']) : '';
$sexo = isset($_POST['sexo']) ? trim($_POST['sexo']) : '';
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
$lugar_nacimiento = isset($_POST['lugar_nacimiento']) ? trim($_POST['lugar_nacimiento']) : '';
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
$pais = isset($_POST['pais']) ? trim($_POST['pais']) : '';
$direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$peso = isset($_POST['peso']) && $_POST['peso'] !== '' ? floatval($_POST['peso']) : null;
$Altura = isset($_POST['Altura']) && $_POST['Altura'] !== '' ? floatval($_POST['Altura']) : null;
$observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

// Validación básica
$mensaje = '';
if (empty($cedula) || empty($apellidos) || empty($nombres)) {
    $mensaje = 'Cédula, apellidos y nombres son obligatorios.';
} else {
    // Validar cédula única
    $stmt = $conn->prepare("SELECT id FROM pacientes WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $mensaje = 'La cédula ya está registrada. Debe ser única.';
    }
    $stmt->close();
}
if ($mensaje !== '') {
    $_SESSION['registro_error'] = $mensaje;
    header("Location: registro_pacientes.php");
    exit();
}

// Prepara e inserta el nuevo paciente (solo los campos válidos)
$stmt = $conn->prepare("INSERT INTO pacientes (cedula, apellidos, nombres, ocupacion, sexo, fecha_nacimiento, lugar_nacimiento, estado, pais, direccion, telefono, peso, Altura, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssssddss", $cedula, $apellidos, $nombres, $ocupacion, $sexo, $fecha_nacimiento, $lugar_nacimiento, $estado, $pais, $direccion, $telefono, $peso, $Altura, $observaciones);

if ($stmt->execute()) {
    header("Location: pacientes.php?exito=1");
    exit();
} else {
    $_SESSION['registro_error'] = 'Error al guardar el paciente.';
    header("Location: registro_pacientes.php");
    exit();
}
$stmt->close();
$conn->close();
