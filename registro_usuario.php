<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrarse</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <div class="form-section">
      <img src="......" alt="" class="logo">
      <h2>Registro</h2>
      <form action="register.php" method="post">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" required>

        <label for="email">Email</label>
        <input type="email" name="email" required>

        <label for="password">Contraseña</label>
        <input type="password" name="password" required>

        <button type="submit">Registrarse</button>
      </form>
      <p>¿Ya tienes una cuenta? <a href="inicio.html">Inicia sesión</a></p>
    </div>
    <div class="info-section">
      <h2>Ambulatorio Urbano<br>Libertador I.</h2>
      <div class="bg-logo"></div>
    </div>
  </div>
</body>
</html>
