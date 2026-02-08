<?php
// actualizar_personal.php - Procesa la edición de personal médico
session_start(); // Iniciamos sesión para pasar errores de forma limpia

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// 1. RECIBIR DATOS
// El ID viene cifrado en base64 desde el campo hidden
$id = isset($_POST['id']) ? base64_decode($_POST['id']) : 0;
$id = intval($id);

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$cargo  = isset($_POST['cargo'])  ? trim($_POST['cargo'])  : ''; // ¡CAMPO NUEVO!

// Contraseñas (opcionales)
$clave = isset($_POST['clave']) ? $_POST['clave'] : '';
$confirmar = isset($_POST['confirmar_clave']) ? $_POST['confirmar_clave'] : '';

// 2. VALIDACIONES
$mensaje = '';

// Proteger al admin principal (ID 1) o IDs inválidos
if ($id <= 1) {
    $_SESSION['registro_error'] = "Operación no permitida sobre este usuario.";
    header("Location: personal.php");
    exit();
}

if (empty($nombre) || empty($cedula) || empty($correo) || empty($cargo)) {
    $mensaje = 'Todos los campos (Nombre, Cédula, Correo y Cargo) son obligatorios.';
} elseif (!empty($clave) && $clave !== $confirmar) {
    $mensaje = 'Las nuevas contraseñas no coinciden.';
} else {
    // Validar cédula única (excepto para el propio usuario que estamos editando)
    $stmt = $conn->prepare("SELECT id FROM usuario_medico WHERE cedula = ? AND id != ?");
    $stmt->bind_param("si", $cedula, $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $mensaje = 'La cédula ya está registrada por otro usuario.';
    }
    $stmt->close();
}

// Si hubo error, devolvemos al formulario
if ($mensaje !== '') {
    $_SESSION['registro_error'] = $mensaje;
    // Redirigimos al formulario de edición con el ID cifrado
    header("Location: editar_personal.php?id=" . base64_encode($id));
    exit();
}

// 3. ACTUALIZAR EN BASE DE DATOS
if (!empty($clave)) {
    // --- OPCIÓN A: SI SE CAMBIA LA CONTRASEÑA ---
    
    // OJO: Si estás usando claves en texto plano (12345), usa esta línea:
    $pass_final = $clave;
    
    // Si usaras encriptación real, usarías esta:
    // $pass_final = password_hash($clave, PASSWORD_DEFAULT);

    // Consulta SQL incluyendo CARGO y PASS
    $stmt = $conn->prepare("UPDATE usuario_medico SET nombre=?, cedula=?, correo=?, cargo=?, pass=? WHERE id=?");
    // Tipos: s=string, i=int/string, s=string, s=string, s=string, i=int
    $stmt->bind_param("sisssi", $nombre, $cedula, $correo, $cargo, $pass_final, $id);

} else {
    // --- OPCIÓN B: NO SE CAMBIA LA CONTRASEÑA ---
    
    // Consulta SQL incluyendo CARGO pero NO pass
    $stmt = $conn->prepare("UPDATE usuario_medico SET nombre=?, cedula=?, correo=?, cargo=? WHERE id=?");
    // Tipos: s=string, i=int/string, s=string, s=string, i=int
    $stmt->bind_param("sissi", $nombre, $cedula, $correo, $cargo, $id);
}

if ($stmt->execute()) {
    // Éxito
    header("Location: personal.php?actualizado=1");
    exit();
} else {
    // Error de BD
    $_SESSION['registro_error'] = "Error en base de datos: " . $stmt->error;
    header("Location: editar_personal.php?id=" . base64_encode($id));
    exit();
}

$stmt->close();
$conn->close();
?>