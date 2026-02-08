<?php
session_start();

// CONEXIÓN
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ambulatorio";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Fallo BD: " . $conn->connect_error); }
$conn->set_charset("utf8");

// RECIBIR DATOS
$id          = intval($_POST['id']);
$id_paciente = intval($_POST['id_paciente']);
$motivo      = trim($_POST['motivo']);
$estado      = $_POST['estado'];

// Validar ID
if ($id == 0 || $id_paciente == 0) {
    $_SESSION['registro_error'] = "Error: Datos inválidos.";
    header("Location: editar_cita.php?id=$id");
    exit();
}

// ACTUALIZAR (Solo actualizamos Paciente, Motivo y Estado)
// NO TOCAMOS id_medico, fecha NI hora, así que es imposible que haya choques.
$sql_update = "UPDATE citas SET 
                id_paciente = ?, 
                motivo = ?, 
                estado = ? 
               WHERE id = ?";

$stmt = $conn->prepare($sql_update);
$stmt->bind_param("issi", $id_paciente, $motivo, $estado, $id);

if ($stmt->execute()) {
    header("Location: citas.php?exito=1");
    exit();
} else {
    $_SESSION['registro_error'] = "Error SQL: " . $stmt->error;
    header("Location: editar_cita.php?id=$id");
    exit();
}

$stmt->close();
$conn->close();
?>