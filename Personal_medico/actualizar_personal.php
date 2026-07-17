<?php
// actualizar_personal.php - Procesa la edición
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Conexión fallida: " . $conn->connect_error); }
$conn->set_charset("utf8");

// 1. RECIBIR DATOS
// Decodificamos el ID que viene oculto en el formulario
$id = isset($_POST['id']) ? base64_decode($_POST['id']) : 0;
$id = intval($id);

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$cargo  = isset($_POST['cargo'])  ? trim($_POST['cargo'])  : ''; 

// Campos de contraseña (pueden venir vacíos)
$clave = isset($_POST['clave']) ? $_POST['clave'] : '';
$confirmar = isset($_POST['confirmar_clave']) ? $_POST['confirmar_clave'] : '';

// 2. VALIDACIONES BÁSICAS
$mensaje = '';

if ($id <= 1) { // Proteger al admin principal
    $_SESSION['registro_error'] = "No tienes permiso para editar este usuario.";
    header("Location: personal.php");
    exit();
}

if (empty($nombre) || empty($cedula) || empty($correo) || empty($cargo)) {
    $mensaje = 'Nombre, Cédula, Correo y Cargo son obligatorios.';
} elseif (!empty($clave)) {
    // Solo validamos contraseñas si el usuario escribió algo en el campo
    if ($clave !== $confirmar) {
        $mensaje = 'Las nuevas contraseñas no coinciden.';
    } elseif (strlen($clave) < 4) {
        $mensaje = 'La contraseña es muy corta.';
    }
}

// Verificar duplicados de cédula (excluyendo al usuario actual)
if (empty($mensaje)) {
    $sql_check = "SELECT id FROM usuario_medico WHERE cedula = ? AND id != ?";
    $stmt = $conn->prepare($sql_check);
    if ($stmt) {
        $stmt->bind_param("si", $cedula, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $mensaje = 'Esa cédula ya está registrada por otro usuario.';
        }
        $stmt->close();
    }
}

// Si hubo error, devolver al formulario
if ($mensaje !== '') {
    $_SESSION['registro_error'] = $mensaje;
    header("Location: editar_personal.php?id=" . base64_encode($id));
    exit();
}

// 3. ACTUALIZAR EN BASE DE DATOS
// Aquí está la lógica: ¿Cambió la contraseña o no?

if (!empty($clave)) {
    // --- CASO A: SI ESCRIBIÓ NUEVA CONTRASEÑA ---
    // La encriptamos antes de guardar
    $pass_hash = password_hash($clave, PASSWORD_DEFAULT);
    
    $sql = "UPDATE usuario_medico SET nombre=?, cedula=?, correo=?, cargo=?, pass=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) { die("Error SQL (Caso A): " . $conn->error); }
    
    // "sisssi" -> string, int, string, string, string, int
    $stmt->bind_param("sisssi", $nombre, $cedula, $correo, $cargo, $pass_hash, $id);

} else {
    // --- CASO B: DEJÓ EL CAMPO VACÍO (MANTENER LA VIEJA) ---
    // No incluimos 'pass' en el UPDATE
    
    $sql = "UPDATE usuario_medico SET nombre=?, cedula=?, correo=?, cargo=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) { die("Error SQL (Caso B): " . $conn->error); }
    
    // "sissi" -> string, int, string, string, int
    $stmt->bind_param("sissi", $nombre, $cedula, $correo, $cargo, $id);
}

// EJECUTAR
if ($stmt->execute()) {
    header("Location: personal.php?actualizado=1");
    exit();
} else {
    die("Error al actualizar en BD: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>