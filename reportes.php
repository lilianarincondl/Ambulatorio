<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reporte de Vacunación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            background-color: #0d6efd;
            padding: 0.2rem 0.5rem; 
        }

        .navbar-brand,
        .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #cce5ff !important;
        }

        .hero {
            margin-top: 100px; /* Aumenta este valor para bajar más el encabezado */
            text-align: center;
        }

        .form-container {
            background: #ffffff;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: 5rem auto 2rem;
        }

        h1 {
            color: #1e3d59;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #34495e;
        }

        input[type="submit"] {
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg fixed-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="icons/logo.png" alt="Logo Ambulatorio" style="height: 40px; margin-right: 10px;" />
                Ambulatorio Libertador
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Inicio</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Encabezado principal -->
    <div class="hero">
        <h1>Reporte de Vacunación</h1>
    </div>

    <!-- Contenedor del formulario -->
    <div class="form-container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="mb-3">
                <label for="tipo_reporte" class="form-label">Tipo de reporte:</label>
                <select name="tipo_reporte" id="tipo_reporte" class="form-select" required>
                    <option value="diario">Diario</option>
                    <option value="semanal">Semanal</option>
                    <option value="mensual">Mensual</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha:</label>
                <input type="date" name="fecha" id="fecha" class="form-control" required>
            </div>

            <div class="text-center">
                <input type="submit" name="submit" value="Generar Reporte" class="btn btn-primary">
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
