<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Ambulatorio</title>

    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef2f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
        }

        .container-login {
            background-color: #fff;
            border-radius: 20px; /* Bordes redondeados suaves */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 900px;
            max-width: 95%;
            display: flex;
            min-height: 550px;
        }

        /* --- IZQUIERDA: FORMULARIO --- */
        .form-container {
            width: 50%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* --- AQUÍ ESTÁ EL ARREGLO --- */
        /* Cambié el nombre de 'input-group' a 'campo-entrada' para evitar conflictos */
        .campo-entrada {
            margin-bottom: 20px;
            width: 100%;
        }

        .campo-entrada label {
            display: block; /* Asegura que el texto esté ARRIBA */
            font-size: 14px;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative; /* Para poder poner el icono flotando adentro */
            width: 100%;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 15px 12px 45px; /* Espacio a la izquierda para el icono */
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            color: #333;
        }

        .input-wrapper input:focus {
            background-color: #fff;
            border-color: #aa0b0b;
            box-shadow: 0 0 0 3px rgba(170, 11, 11, 0.1);
        }

        /* Estilo de los iconos SVG */
        .input-wrapper svg {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%); /* Centrar verticalmente exacto */
            color: #888;
            pointer-events: none;
        }

        /* Botón */
        .btn-rojo {
            width: 100%;
            background-color: #a81c1c;
            color: #fff;
            padding: 14px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .btn-rojo:hover { background-color: #8a0000; }

        /* --- DERECHA: PANEL ROJO/AZUL --- */
        .overlay-container {
            width: 50%;
            background: linear-gradient(to bottom, #b30000 0%, #0d213e 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 40px;
            border-top-left-radius: 100px; 
            border-bottom-left-radius: 100px; 
        }

        .logo-circle {
            background: white;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .logo-circle img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .container-login { flex-direction: column; height: auto; }
            .form-container, .overlay-container { width: 100%; padding: 30px; }
            .overlay-container { order: -1; border-radius: 0 0 40px 40px; }
        }
    </style>
</head>
<body>

    <div class="container-login">
        
        <div class="form-container">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: #b30000; font-weight: 700;">Bienvenido</h2>
                <p style="color: #666; font-size: 14px;">Ingresa tus credenciales para acceder</p>
            </div>

            <form action="auth.php" method="post" style="width: 100%;">
                
                <div class="campo-entrada">
                    <label for="correo">Correo Electrónico</label>
                    <div class="input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555zM0 4.697v7.104l5.803-3.558L0 4.697zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757zm3.436-.586L16 11.801V4.697l-5.803 3.547z"/>
                        </svg>
                        <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                    </div>
                </div>

                <div class="campo-entrada">
                    <label for="pass">Contraseña</label>
                    <div class="input-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                        </svg>
                        <input type="password" id="pass" name="pass" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-rojo">INICIAR SESIÓN</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="logo-circle">
                <img src="icons/logo.png" alt="Logo"> 
            </div>
            <h2 style="font-weight: 700; margin-bottom: 10px;">Sistema Integral Médico</h2>
            <p style="font-size: 14px; opacity: 0.9; line-height: 1.5; max-width: 80%;">
                Gestión eficiente para el cuidado de la salud y control de triaje.
            </p>
        </div>

    </div>

</body>
</html>