<?php
// actualizar.php - Procesa la edición de personal médico

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id = isset($_POST['id']) ? base64_decode($_POST['id']) : 0;
$id = intval($id);

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$clave = isset($_POST['clave']) ? $_POST['clave'] : '';
$confirmar = isset($_POST['confirmar_clave']) ? $_POST['confirmar_clave'] : '';
$cedula = isset($_POST['cedula']) ? intval($_POST['cedula']) : 0;

// Validación de campos

$mensaje = '';
if ($id <= 0 || $id == 1 || empty($nombre) || empty($cedula) || empty($correo)) {
    $mensaje = 'Todos los campos son obligatorios.';
} elseif (!is_numeric($cedula) || $cedula <= 0) {
    $mensaje = 'La cédula debe ser un número válido.';
} elseif (!empty($clave) && $clave !== $confirmar) {
    $mensaje = 'Las contraseñas no coinciden.';
} else {
    // Validar cédula única (excepto para el propio usuario)
    $stmt = $conn->prepare("SELECT id FROM usuario_medico WHERE cedula = ? AND id != ?");
    $stmt->bind_param("ii", $cedula, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $mensaje = 'La cédula ya está registrada. Debe ser única.';
    }
    $stmt->close();
}
if ($mensaje !== '') {
    header("Location: editar_personal.php?id=" . base64_encode($id) . "&error=" . urlencode($mensaje));
    exit();
}

if (!empty($clave)) {
    // Si se cambia la contraseña
    $pass_hash = password_hash($clave, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuario_medico SET nombre=?, cedula=?, correo=?, pass=? WHERE id=?");
    $stmt->bind_param("sissi", $nombre, $cedula, $correo, $pass_hash, $id);
} else {
    // Si no se cambia la contraseña
    $stmt = $conn->prepare("UPDATE usuario_medico SET nombre=?, cedula=?, correo=? WHERE id=?");
    $stmt->bind_param("sisi", $nombre, $cedula, $correo, $id);
}

if ($stmt->execute()) {
    header("Location: personal.php?actualizado=1");
    exit();
} else {
    header("Location: editar_personal.php?id=" . base64_encode($id) . "&error=bd");
    exit();
}

$stmt->close();
$conn->close();
?>
