<?php
session_start();

// Obtener datos del formulario
$correo = $_POST['correo']; // Corregido para que coincida con el nombre en el formulario
$pass = trim($_POST['pass']); // Usar trim() para eliminar espacios en blanco

if (empty($correo) || empty($pass)) { // Requisitos obligatorios de pass y correo
    echo "Debe ingresar su email y contraseña";
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "ambulatorio"); // Conexión a la base de datos

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Preparar la consulta
$query = $mysqli->prepare("SELECT * FROM usuario_medico WHERE correo = ?");
$query->bind_param("s", $correo); // Vincular el parámetro
$query->execute(); // Ejecutar la consulta
$result = $query->get_result(); // Obtener el resultado

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();

    // Depuración: imprimir la contraseña ingresada y el hash almacenado
    // echo "Contraseña ingresada: " . $pass . "<br>";
    // echo "Hash almacenado: " . $row['pass'] . "<br>";

    // Verificar la contraseña
    if (password_verify($pass, $row['pass'])) {
        $_SESSION['id_usu'] = $row['id'];
        $_SESSION['nombre_usu'] = $row['nombre'];
        $_SESSION['correo_usu'] = $row['correo'];
        $_SESSION['activo_usu'] = $row['activo'];

        header("Location: dashboard.php");
        exit();
    } else {
        echo "Contraseña Incorrecta";
        exit();
    }

} else {
    echo "Usuario no encontrado";
    exit();
}

$query->close();
$mysqli->close();
?>
