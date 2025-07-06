<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <div class="form-section">
      <img src="icons/logo.png" alt="" class="logo">
      <h2>Iniciar Sesión</h2>

      <form action="auth.php" method="post">
         
        <label for="email">Email</label>
        <input type="email" name="correo" required> 
        
        <label for="password">Contraseña</label>
        <input type="password" name="pass" required>
        
        <button type="submit">Iniciar</button>
      </form>
    </div>
    <div class="info-section">
    </div>
  </div>
</body>
</html>
