<?php
session_start();

if (!isset($_SESSION['id_usu'])) {
   header("Location: ../inicio.html");
   exit();
}

session_destroy();

echo '<script>alert("Vuelve Pronto"); window.location.href="../inicio.html";</script>';
?>
