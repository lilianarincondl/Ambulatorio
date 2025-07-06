<?php
session_start();

if (!isset($_SESSION['id_usu'])) {
   header("Location: ../inicio.php");
   exit();
}

session_destroy();

echo '<script>alert("Vuelve Pronto"); window.location.href="../ambulatorio/inicio.php";</script>';
?>
