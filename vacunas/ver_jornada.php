<?php
// ver_jornada.php - Visualización detallada de una jornada de vacunación

// Configuración de la conexión a la base de datos
$host = "localhost";
$dbname = "ambulatorio";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Verificar que se haya enviado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: jornadas.php");
    exit();
}

$id = $_GET['id'];

// Obtener los datos de la jornada
$stmt = $pdo->prepare("SELECT * FROM jornadas WHERE id = ?");
$stmt->execute([$id]);
$jornada = $stmt->fetch();

if (!$jornada) {
    header("Location: jornadas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Jornada</title>
    
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        /* --- ESTILOS GENERALES --- */
        body {
            background: #eef2f6;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        /* --- NAVBAR --- */
        .navbar {
            background: linear-gradient(90deg, #aa0b0b 0%, #003366 100%);
            padding: 10px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 600;
            color: white !important;
            font-size: 1.1rem;
        }

        .btn-volver {
            color: white;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 5px 15px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .btn-volver:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        /* --- TARJETA PRINCIPAL --- */
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        /* --- CABECERA DE LA JORNADA --- */
        .header-jornada {
            background: linear-gradient(135deg, #003366 0%, #1a5276 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .header-jornada h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }

        .header-jornada .fecha-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 30px;
            display: inline-block;
            font-weight: 500;
            font-size: 1rem;
        }

                    /* --- BOTONES MEJORADOS --- */
            .btn-pdf-custom {
                color: white;
                padding: 10px 20px;
                border-radius: 10px;
                font-weight: 500;
                border: none;
                transition: 0.3s;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                white-space: nowrap; /* Evita que el texto se rompa en varias líneas */
            }

            .btn-pdf-custom:hover {
                color: white;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }

            .btn-pdf-ver {
                background: #1976D2;
            }
            .btn-pdf-ver:hover {
                background: #0d47a1;
            }

            .btn-pdf-descargar {
                background: #283593;
            }
            .btn-pdf-descargar:hover {
                background: #1a237e;
            }

            .btn-editar-custom {
                background: #ffc107;
                color: #1a1a2e;
                padding: 10px 20px;
                border-radius: 10px;
                font-weight: 500;
                border: none;
                transition: 0.3s;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                white-space: nowrap;
            }

            .btn-editar-custom:hover {
                background: #e0a800;
                color: #1a1a2e;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
            }

            /* Para pantallas pequeñas (móviles) */
            @media (max-width: 768px) {
                .d-flex {
                    justify-content: center !important;
                }
                .btn-pdf-custom, .btn-editar-custom {
                    padding: 8px 15px;
                    font-size: 13px;
                }
            }
        /* --- TARJETAS DE INFORMACIÓN --- */
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            border: 1px solid #e9ecef;
            transition: 0.3s;
        }

        .info-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .info-card .icon {
            font-size: 2rem;
            color: #003366;
            margin-bottom: 10px;
        }

        .info-card .label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .info-card .value {
            font-size: 1.1rem;
            color: #1a1a2e;
            font-weight: 500;
            margin-top: 5px;
        }

        /* --- BOTONES DE ACCIÓN --- */
        .btn-pdf-custom {
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            border: none;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-pdf-custom:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .btn-pdf-ver {
            background: #1976D2;
        }
        .btn-pdf-ver:hover {
            background: #0d47a1;
        }

        .btn-pdf-descargar {
            background: #283593;
        }
        .btn-pdf-descargar:hover {
            background: #1a237e;
        }

        .btn-editar-custom {
            background: #ffc107;
            color: #1a1a2e;
            padding: 10px 25px;
            border-radius: 10px;
            font-weight: 500;
            border: none;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-editar-custom:hover {
            background: #e0a800;
            color: #1a1a2e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }

        /* --- SEPARADOR --- */
        .section-divider {
            border-top: 2px solid #e9ecef;
            margin: 30px 0;
        }

        /* --- RESPONSIVE --- */
        @media print {
            .navbar, .btn-volver, .btn-pdf-custom, .btn-editar-custom, .no-print {
                display: none !important;
            }
            .main-card {
                box-shadow: none !important;
                padding: 15px !important;
            }
            .header-jornada {
                background: #003366 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body {
                background: white !important;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" onerror="this.style.display='none'"/>
                <span>Detalle de Jornada</span>
            </a>
            <div class="ms-auto">
                <a class="btn-volver" href="jornadas.php">← Volver al Listado</a>
            </div>
        </div>
    </nav>

    <div style="height: 70px;"></div>

    <div class="container">
        <div class="main-card">
            
                        <!-- Cabecera de la Jornada -->
            <div class="header-jornada">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h1>
                            <i class="fas fa-syringe me-2"></i>
                            <?= htmlspecialchars($jornada['establecimiento']) ?>
                        </h1>
                        <div class="fecha-badge mt-2">
                            <i class="far fa-calendar-alt me-2"></i>
                            <?= date('l, d \d\e F \d\e Y', strtotime($jornada['fecha'])) ?>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end mt-3 mt-md-0 no-print">
                        <div class="d-flex justify-content-md-end gap-2 flex-wrap">
                            <!-- Botón Ver PDF -->
                            <a href="pdf_jornada.php?id=<?= $jornada['id'] ?>" target="_blank" class="btn-pdf-custom btn-pdf-ver">
                                <i class="fas fa-eye"></i> Ver PDF
                            </a>
                            
                            <!-- Botón Descargar PDF -->
                            <a href="pdf_jornada.php?id=<?= $jornada['id'] ?>&download=1" class="btn-pdf-custom btn-pdf-descargar">
                                <i class="fas fa-file-pdf"></i> Descargar
                            </a>
                            
                            <!-- Botón Editar -->
                            <a href="editar_jornada.php?id=<?= $jornada['id'] ?>" class="btn-editar-custom">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Detallada -->
            <div class="row g-4">
                <!-- Fecha -->
                <div class="col-md-4">
                    <div class="info-card text-center">
                        <div class="icon"><i class="far fa-calendar-check"></i></div>
                        <div class="label">Fecha de la Jornada</div>
                        <div class="value"><?= date('d/m/Y', strtotime($jornada['fecha'])) ?></div>
                    </div>
                </div>

                <!-- Establecimiento -->
                <div class="col-md-4">
                    <div class="info-card text-center">
                        <div class="icon"><i class="fas fa-hospital"></i></div>
                        <div class="label">Establecimiento / Lugar</div>
                        <div class="value"><?= htmlspecialchars($jornada['establecimiento']) ?></div>
                    </div>
                </div>

                <!-- Responsables -->
                <div class="col-md-4">
                    <div class="info-card text-center">
                        <div class="icon"><i class="fas fa-user-md"></i></div>
                        <div class="label">Responsables</div>
                        <div class="value"><?= htmlspecialchars($jornada['responsables']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Información Adicional (si existe) -->
            <?php if (!empty($jornada['observaciones'])): ?>
                <div class="section-divider"></div>
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-muted mb-3"><i class="fas fa-sticky-note me-2"></i>Observaciones</h5>
                        <div class="info-card" style="background: #fafafa;">
                            <p class="mb-0" style="font-size: 1.05rem;"><?= nl2br(htmlspecialchars($jornada['observaciones'])) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="section-divider"></div>

            <!-- Pie de página -->
            <div class="row">
                <div class="col-12 text-center text-muted" style="font-size: 0.9rem;">
                    <i class="far fa-clock me-1"></i>
                    Registro generado el <?= date('d/m/Y H:i:s') ?>
                </div>
            </div>

        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>