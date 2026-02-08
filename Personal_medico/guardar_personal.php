<?php
// guardar_personal.php - Procesa el registro de nuevo personal médico
session_start();

// Conexión directa a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// 1. RECIBIR DATOS DEL FORMULARIO
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$cedula = isset($_POST['cedula']) ? intval($_POST['cedula']) : 0;
$cargo  = isset($_POST['cargo'])  ? trim($_POST['cargo'])  : ''; // ¡CAMPO NUEVO!

$password  = isset($_POST['password']) ? $_POST['password'] : '';
$confirmar = isset($_POST['confirmar']) ? $_POST['confirmar'] : '';

// 2. VALIDACIÓN BÁSICA
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
    $stmt->bind_param("i", $cedula);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $mensaje = 'La cédula ya está registrada. Debe ser única.';
    }
    $stmt->close();
}

// Si hay error, regresamos al formulario
if ($mensaje !== '') {
    $_SESSION['registro_error'] = $mensaje;
    header("Location: registrar_personal.php");
    exit();
}

// 3. PREPARAR CONTRASEÑA
// Usamos password_hash para seguridad (estándar moderno)
$pass_hash = password_hash($password, PASSWORD_DEFAULT);

/* NOTA: Si prefieres guardar la contraseña tal cual (ej: "12345") para verla en la BD,
   comenta la línea de arriba y usa esta:
   $pass_hash = $password; 
*/

// 4. INSERTAR EN LA BASE DE DATOS (Incluyendo 'cargo')
// Agregamos 'activo = 1' por defecto
$sql = "INSERT INTO usuario_medico (nombre, cedula, correo, cargo, pass, activo) VALUES (?, ?, ?, ?, ?, 1)";
$stmt = $conn->prepare($sql);

// "sisss" = string (nombre), int (cedula), string (correo), string (cargo), string (pass)
$stmt->bind_param("sisss", $nombre, $cedula, $correo, $cargo, $pass_hash);

if ($stmt->execute()) {
    // Éxito: Redirige al listado
    header("Location: personal.php?exito=1");
    exit();
} else {
    // Fallo SQL
    $_SESSION['registro_error'] = "Error de Base de Datos: " . $stmt->error;
    header("Location: registrar_personal.php");
    exit();
}

$stmt->close();
$conn->close();
?>