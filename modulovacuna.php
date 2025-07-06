<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo Registro Diario de Vacunación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .module {
            display: none;
        }
        .module.active {
            display: block;
        }
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .headline {
            background-color: #aa0b0b;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .vaccine-group {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .vaccine-title {
            color:  #aa0b0b;
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .row.g-2.align-items-center, .row.g-3.align-items-center {
                gap: 10px;
            }
            .col-auto, .col-md-3 {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="form-container">
            <h1 class="text-center mb-4">Registro Diario de Vacunación</h1>
            
            <form action="" method="post" class="form-vaccine">
                <!-- Módulo 1: Datos del paciente -->
                <div class="module active form-section" id="module1">
                    <div class="headline">
                        <h2 class="h4 mb-0">Datos del paciente</h2>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombres</label>
                            <input class="form-control" type="text" name="name" id="name" required>
                            <div class="invalid-feedback">
                                Por favor ingrese los nombres del paciente.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Apellidos</label>
                            <input class="form-control" type="text" name="lastname" id="lastname" required>
                            <div class="invalid-feedback">
                                Por favor ingrese los apellidos del paciente.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="birthday" class="form-label">Fecha de Nacimiento</label>
                            <input class="form-control" type="date" name="birthday" id="birthday" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cedula" class="form-label">Cédula</label>
                            <input class="form-control" type="number" name="cedula" id="cedula" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="age" class="form-label">Edad</label>
                            <input class="form-control" type="number" name="age" id="age" min="0" max="120" required>
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Sexo</label>
                            <select class="form-select" name="gender" id="gender" required>
                                <option value="" selected disabled>Seleccione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="grupoEspecial" class="form-label">Grupos Especiales</label>
                            <input class="form-control" type="text" name="grupoEspecial" id="grupoEspecial">
                        </div>
                    </div>
                </div>

                <!-- Módulo 2: Biológicos -->
                <div class="module form-section" id="module2">
                    <div class="headline">
                        <h2 class="h4 mb-0">Biológicos aplicados</h2>
                    </div>
                    
                    <!-- BCG -->
                    <div class="vaccine-group">
                        <h4 class="vaccine-title">BCG</h4>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="BCS" id="BCS" value="1">
                            <label class="form-check-label" for="BCS">DU</label>
                        </div>
                    </div>
                    
                    <!-- Hepatitis B -->
                    <div class="vaccine-group">
                        <h4 class="vaccine-title">Hepatitis B</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="DU" id="DU" value="1">
                                    <label class="form-check-label" for="DU">DU</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="DA" id="DA" value="1">
                                    <label class="form-check-label" for="DA">DA</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="1D" id="1D" value="1">
                                    <label class="form-check-label" for="1D">1D</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="2D" id="2D" value="1">
                                    <label class="form-check-label" for="2D">2D</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="3D" id="3D" value="1">
                                    <label class="form-check-label" for="3D">3D</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rotavirus -->
                    <div class="vaccine-group">
                        <h4 class="vaccine-title">Rotavirus</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="1DRotavirus" id="1DRotavirus" value="1">
                                    <label class="form-check-label" for="1DRotavirus">1D</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="2DRotavirus" id="2DRotavirus" value="1">
                                    <label class="form-check-label" for="2DRotavirus">2D</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulo 3: Observaciones y envío
                <div class="module form-section" id="module3">
                    <div class="headline">
                        <h2 class="h4 mb-0">Finalizar registro</h2>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="observaciones" rows="3"></textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <button class="btn btn-success" type="submit">Guardar Registro de Vacunación</button>
                        </div>
                    </div>
                </div> -->
            </form>
            
            <div class="navigation-buttons">
                <button type="button" class="back d-none btn btn-primary" onclick="prevModule()">
                    <i class="bi bi-arrow-left"></i> Atrás
                </button>
                <button type="button" class="next btn btn-primary" onclick="nextModule()">
                    Siguiente <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentModule = 0;
        const backButton = document.querySelector('.back');
        const nextButton = document.querySelector('.next');
        const modules = document.querySelectorAll('.module');

        function initModules() {
            modules.forEach((module, index) => {
                if (index === 0) {
                    module.classList.add('active');
                } else {
                    module.classList.remove('active');
                }
            });
            updateButtons();
        }

        function updateButtons() {
            if (currentModule === 0) {
                backButton.classList.add('d-none');
            } else {
                backButton.classList.remove('d-none');
            }

            if (currentModule === modules.length - 1) {
                nextButton.classList.add('d-none');
            } else {
                nextButton.classList.remove('d-none');
            }
        }

        function showModule(index) {
            currentModule = index;
            modules.forEach((module, i) => {
                if (i === index) {
                    module.classList.add('active');
                } else {
                    module.classList.remove('active');
                }
            });
            updateButtons();
            modules[index].scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function nextModule() {
            if (currentModule < modules.length - 1) {
                showModule(currentModule + 1);
            }
        }

        function prevModule() {
            if (currentModule > 0) {
                showModule(currentModule - 1);
            }
        }

        document.addEventListener('DOMContentLoaded', initModules);
    </script>
</body>
</html>