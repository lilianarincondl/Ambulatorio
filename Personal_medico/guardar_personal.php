<?php
// guardar_personal.php - Procesa el registro de nuevo personal médico
session_start();

// Conexión
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// 1. RECIBIR DATOS
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$cedula = isset($_POST['cedula']) ? intval($_POST['cedula']) : 0;
$cargo  = isset($_POST['cargo'])  ? trim($_POST['cargo'])  : ''; 

$password  = isset($_POST['password']) ? $_POST['password'] : '';
$confirmar = isset($_POST['confirmar']) ? $_POST['confirmar'] : '';

// 2. VALIDACIÓN
$mensaje = '';

if (empty($nombre) || empty($cedula) || empty($correo) || empty($password) || empty($confirmar) || empty($cargo)) {
    $mensaje = 'Todos los campos son obligatorios (incluyendo el Cargo).';
} elseif (!is_numeric($cedula) || $cedula <= 0) {
    $mensaje = 'La cédula debe ser un número válido.';
} elseif ($password !== $confirmar) {
    $mensaje = 'Las contraseñas no coinciden.';
} else {
    // Validar cédula única
    $stmt = $conn->prepare("SELECT id FROM usuario_medico WHERE cedula = ?");
    if ($stmt) {
        $stmt->bind_param("i", $cedula);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $mensaje = 'La cédula ya está registrada.';
        }
        $stmt->close();
    }
}

if ($mensaje !== '') {
    $_SESSION['registro_error'] = $mensaje;
    header("Location: registrar_personal.php");
    exit();
}

// 3. INSERTAR (CON DETECCIÓN DE ERRORES)
$pass_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuario_medico (nombre, cedula, correo, cargo, pass, activo) VALUES (?, ?, ?, ?, ?, 1)";
$stmt = $conn->prepare($sql);

// --- AQUÍ ESTÁ LA PROTECCIÓN ---
if (!$stmt) {
    // Si falla aquí, es porque la tabla no tiene las columnas correctas
    die("<h3 style='color:red; font-family:sans-serif;'>Error Crítico de Base de Datos:</h3>" . 
        "<p>La base de datos rechazó la consulta. Probablemente falta la columna 'cargo'.</p>" .
        "<p><strong>Detalle técnico:</strong> " . $conn->error . "</p>" . 
        "<hr><p>Ejecuta este SQL en phpMyAdmin:</p><code>ALTER TABLE usuario_medico ADD COLUMN cargo VARCHAR(50) AFTER correo;</code>");
}

$stmt->bind_param("sisss", $nombre, $cedula, $correo, $cargo, $pass_hash);

if ($stmt->execute()) {
    header("Location: personal.php?exito=1");
    exit();
} else {
    $_SESSION['registro_error'] = "Error al guardar: " . $stmt->error;
    header("Location: registrar_personal.php");
    exit();
}

$stmt->close();
$conn->close();
?>