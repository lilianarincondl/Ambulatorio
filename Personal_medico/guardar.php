<?php
// guardar.php - Procesa el registro de nuevo personal médico

// Conexión directa a la base de datos (ajusta los datos según tu entorno XAMPP)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibe datos del formulario

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$cedula = isset($_POST['cedula']) ? intval($_POST['cedula']) : 0;

// Validación básica

$mensaje = '';
if (empty($nombre) || empty($cedula) || empty($correo) || empty($password) || empty($_POST['confirmar'])) {
    $mensaje = 'Todos los campos son obligatorios.';
} elseif (!is_numeric($cedula) || $cedula <= 0) {
    $mensaje = 'La cédula debe ser un número válido.';
} elseif ($password !== $_POST['confirmar']) {
    $mensaje = 'Las contraseñas no coinciden.';
} else {
    // Validar cédula única
    $stmt = $conn->prepare("SELECT id FROM usuario_medico WHERE cedula = ?");
    $stmt->bind_param("i", $cedula);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $mensaje = 'La cédula ya está registrada. Debe ser única.';
    }
    $stmt->close();
}
if ($mensaje !== '') {
    session_start();
    $_SESSION['registro_error'] = $mensaje;
    header("Location: registrar_personal.php");
    exit();
}

// Hashea la contraseña
$pass_hash = password_hash($password, PASSWORD_DEFAULT);


// Prepara e inserta el nuevo usuario con el campo correcto 'pass'
$stmt = $conn->prepare("INSERT INTO usuario_medico (nombre, cedula, correo, pass) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $nombre, $cedula, $correo, $pass_hash);

if ($stmt->execute()) {
    // Redirige al listado tras guardar
    header("Location: personal.php?exito=1");
    exit();
} else {
    // Redirige con error si falla
    header("Location: registrar_personal.php?error=bd");
    exit();
}

$stmt->close();
$conn->close();
?>
