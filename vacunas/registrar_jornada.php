<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Registro de Vacunación</title>
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="registro_vacunas.css">
</head>
<body>

            <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg fixed-top shadow">
        <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../icons/logo.png" alt="Logo Ambulatorio" style="height: 40px; margin-right: 10px;" />
           Ambulatorio Urbano I Libertador
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="../dashboard.php">Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="jornada.php">Atras</a></li>
            </ul>
        </div>
        </div>
    </nav>
    <!-- Espacio para que el contenido no quede debajo de la navbar fija -->
    <div style="height: 70px;"></div>

    <div class="container">
        <div class="header">
            <h1>DIRECTOR GENERAL DE EPIDEMIOLOGÍA</h1>
            <h1>DIRECTOR DE INMUNIZACIONES</h1>
            <h1>SISTEMA DE INFORMACIÓN DEL PROGRAMA AMPLIADO DE INMUNIZACIONES</h1>
            <h2>REGISTRO DIARIO DE VACUNACIÓN</h2>
        </div>
        
        <form id="vaccination-form" method="POST" action="guardar.php">
            <div class="form-section">
                <div class="form-row">
                    <div class="form-field">
                        <span>1. FECHA:</span>
                        <input type="date" name="fecha" required>
                    </div>
                    <div class="form-field">
                        <span>2. ÁSIG:</span>
                        <input type="text" name="asig" maxlength="10" required>
                    </div>
                    <div class="form-field">
                        <span>3. Código:</span>
                        <input type="text" name="codigo" required>
                    </div>
                    <div class="form-field">
                        <span>4. Establecimiento:</span>
                        <input type="text" name="establecimiento" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <span>5. Responsables:</span>
                        <input type="text" name="responsables" required>
                    </div>
                    <div class="form-field">
                        <span>6. Hora Inicio:</span>
                        <input type="time" name="hora_inicio" required>
                    </div>
                    <div class="form-field">
                        <span>7. Hora Fin:</span>
                        <input type="time" name="hora_fin" required>
                    </div>
                </div>
                
                <div class="estrategia-container">
                    <div class="form-field">
                        <span>8. Estrategia de vacunación:</span>
                    </div>
                    <div class="estrategia-buttons">
                        <button type="button" class="estrategia-btn" data-estrategia="rutina">Rutina</button>
                        <button type="button" class="estrategia-btn" data-estrategia="intramural">Intramural</button>
                        <button type="button" class="estrategia-btn" data-estrategia="jornada">Jornada</button>
                        <button type="button" class="estrategia-btn" data-estrategia="extramural">Extramural</button>
                    </div>
                    <div class="estrategia-donde">
                        <span>Donde:</span>
                        <input type="text" name="estrategia_donde" placeholder="Escriba el lugar">
                    </div>
                    <input type="hidden" name="estrategia" value="">
                </div>
            </div>
            
            <div class="table-container">
                <table id="vaccination-table">
                    <thead>
                        <tr class="encabezado-principal">
                            <th class="serial-col">Nº</th>
                            <th class="nombre-col">9. NOMBRE</th>
                            <th class="apellido-col">10. APELLIDO</th>
                            <th class="fecha-col">11. FECHA NACIMIENTO</th>
                            <th class="nacionalidad-col">12. NACIONALIDAD</th>
                            <th class="documento-col">13. N° CÉDULA</th>
                            <th class="hijo-col">14. ORDEN HIJO</th>
                            <th class="direccion-col">15. DIRECCIÓN</th>
                            <th class="etnia-col">16. PUEBLO/ETNIA</th>
                            <th class="edad-col">17. EDAD</th>
                            <th class="sexo-col">18. SEXO</th>
                            <th class="grupo-col">19. GRUPOS ESPECIALES</th>
                            
                            <!-- Encabezado de vacunas -->
                            <th colspan="15" class="vacunas-header">20. VACUNAS</th>
                            
                            <th class="obs-col">21. OBSERVACIONES</th>
                            <th class="acciones-col">ACCIONES</th>
                        </tr>
                        <tr class="encabezado-secundario">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <!-- Columnas de vacunas individuales -->
                            <th class="vacuna-col sticky-vacuna">BCG</th>
                            <th class="vacuna-col sticky-vacuna">Hepatitis B</th>
                            <th class="vacuna-col sticky-vacuna">Rotavirus</th>
                            <th class="vacuna-col sticky-vacuna">Pentavalente</th>
                            <th class="vacuna-col sticky-vacuna">Polio Inyectable</th>
                            <th class="vacuna-col sticky-vacuna">Polio Oral</th>
                            <th class="vacuna-col sticky-vacuna">Neumococo Conjugada</th>
                            <th class="vacuna-col sticky-vacuna">Influenza Estacional</th>
                            <th class="vacuna-col sticky-vacuna">Fiebre Amarilla</th>
                            <th class="vacuna-col sticky-vacuna">SARAMPIÓN/ RUBÉOLA/ PAROTIDITIS</th>
                            <th class="vacuna-col sticky-vacuna">Toxoide Tetánico Diftérico</th>
                            <th class="vacuna-col sticky-vacuna">Neumococo Polisacárida</th>
                            <th class="vacuna-col sticky-vacuna">Meningocócica B-C</th>
                            <th class="vacuna-col sticky-vacuna">Anti-Rábica Humana PRE</th>
                            <th class="vacuna-col sticky-vacuna">Anti-Rábica Humana POST</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="serial">1</td>
                            <td><input type="text" name="nombre[]" required></td>
                            <td><input type="text" name="apellido[]" required></td>
                            <td><input type="date" name="fecha_nacimiento[]" required></td>
                            <td>
                                <select name="nacionalidad[]">
                                    <option value="">Seleccionar</option>
                                    <option value="Ecuatoriana">Ecuatoriana</option>
                                    <option value="Colombiana">Colombiana</option>
                                    <option value="Peruana">Peruana</option>
                                    <option value="Venezolana">Venezolana</option>
                                    <option value="Otra">Otra</option>
                                </select>
                            </td>
                            <td><input type="text" name="documento[]" required></td>
                            <td><input type="number" name="orden_hijo[]" min="1" max="20"></td>
                            <td><input type="text" name="direccion[]"></td>
                            <td><input type="text" name="etnia[]"></td>
                            <td><input type="number" name="edad[]" min="0" max="120"></td>
                            <td>
                                <select name="sexo[]">
                                    <option value="M">M</option>
                                    <option value="F">F</option>
                                </select>
                            </td>
                            <td><input type="text" name="grupo_especial[]"></td>
                            
                            <!-- Columnas para cada vacuna -->
                            <!-- BCG -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
                                </div>
                                <input type="hidden" name="dosis_bcg[]" value="">
                            </td>
                            <!-- HEPATITIS B -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                </div>
                                <input type="hidden" name="dosis_hepb[]" value="">
                            </td>
                            <!-- ROTAVIRUS -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                </div>
                                <input type="hidden" name="dosis_rotavirus[]" value="">
                            </td>
                            <!-- PENTAVALENTE -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                    <button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button>
                                </div>
                                <input type="hidden" name="dosis_pentavalente[]" value="">
                            </td>
                            <!-- POLIO INYECTABLE -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                </div>
                                <input type="hidden" name="dosis_polio_iny[]" value="">
                            </td>
                            <!-- POLIO ORAL -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                    <button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                </div>
                                <input type="hidden" name="dosis_polio_oral[]" value="">
                            </td>
                            <!-- NEUMOCOCO CONJUGADA -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                </div>
                                <input type="hidden" name="dosis_neumo_conj[]" value="">
                            </td>
                            <!-- INFLUENZA ESTACIONAL -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d12" data-dosis="DE">DE</button>
                                </div>
                                <input type="hidden" name="dosis_influenza[]" value="">
                            </td>
                            <!-- FIEBRE AMARILLA -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
                                </div>
                                <input type="hidden" name="dosis_fiebre_ama[]" value="">
                            </td>
                            <!-- SARAMPIÓN RUBÉOLA PAROTIDITIS (SRP) -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                </div>
                                <input type="hidden" name="dosis_srp[]" value="">
                            </td>
                            <!-- TOXOIDE TETÁNICO DIFTÉRICO -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d4" data-dosis="4D">4D</button>
                                    <button type="button" class="dosis-btn d5" data-dosis="5D">5D</button>
                                    <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
                                </div>
                                <input type="hidden" name="dosis_toxoide[]" value="">
                            </td>
                            <!-- NEUMOCOCO POLISACÁRIDA -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                </div>
                                <input type="hidden" name="dosis_neumo_poli[]" value="">
                            </td>
                            <!-- MENINGOCÓCICA B-C -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                </div>
                                <input type="hidden" name="dosis_meningo[]" value="">
                            </td>
                            <!-- ANTI-RÁBICA HUMANA PRE -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
                                </div>
                                <input type="hidden" name="dosis_rabia_pre[]" value="">
                            </td>
                            <!-- ANTI-RÁBICA HUMANA POST -->
                            <td>
                                <div class="dosis-container">
                                    <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
                                    <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
                                    <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
                                    <button type="button" class="dosis-btn d4" data-dosis="4D">4D</button>
                                    <button type="button" class="dosis-btn d5" data-dosis="5D">5D</button>
                                    <button type="button" class="dosis-btn d6" data-dosis="6D">6D</button>
                                    <button type="button" class="dosis-btn d7" data-dosis="7D">7D</button>
                                </div>
                                <input type="hidden" name="dosis_rabia_post[]" value="">
                            </td>
                            <td><input type="text" name="observaciones[]"></td>
                            <td>
                                <button type="button" class="delete-row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="action-buttons" style="margin-bottom: 10px;">
                <button type="button" id="add-row" class="action-btn add-btn">
                    <i class="fas fa-plus-circle"></i> Agregar Fila
                </button>
            </div>

            <!-- Tabla de Números de Lote y Fechas de Vencimiento en el orden de la foto -->
            <h3 style="margin-top: 30px;">22. NÚMEROS DE LOTE Y FECHAS DE VENCIMIENTO</h3>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="tabla-lotes" border="1" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>BIOLÓGICO</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                                <th>BIOLÓGICO</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                                <th>BIOLÓGICO</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                                <th>BIOLÓGICO (OTROS)</th>
                                <th>Nº DE LOTE</th>
                                <th>FECHA VENCIMIENTO</th>
                                <th>Nº DOSIS PERDIDAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- Columna 1 -->
                                <td>BCG</td>
                                <td><input type="text" name="lote_bcg"></td>
                                <td><input type="date" name="venc_bcg"></td>
                                <td><input type="number" name="perdidas_bcg" min="0"></td>
                                <!-- Columna 2 -->
                                <td>POLIO INYECTABLE</td>
                                <td><input type="text" name="lote_polio_iny"></td>
                                <td><input type="date" name="venc_polio_iny"></td>
                                <td><input type="number" name="perdidas_polio_iny" min="0"></td>
                                <!-- Columna 3 -->
                                <td>SARAMPIÓN/RUBÉOLA/PAROTIDITIS</td>
                                <td><input type="text" name="lote_srp"></td>
                                <td><input type="date" name="venc_srp"></td>
                                <td><input type="number" name="perdidas_srp" min="0"></td>
                                <!-- OTROS -->
                                <td><input type="text" name="lote_otro_nombre[]"></td>
                                <td><input type="text" name="lote_otro_lote[]"></td>
                                <td><input type="date" name="lote_otro_venc[]"></td>
                                <td><input type="number" name="lote_otro_perdidas[]" min="0"></td>
                            </tr>
                            <tr>
                                <td>HEPATITIS B</td>
                                <td><input type="text" name="lote_hepb"></td>
                                <td><input type="date" name="venc_hepb"></td>
                                <td><input type="number" name="perdidas_hepb" min="0"></td>
                                <td>POLIO ORAL</td>
                                <td><input type="text" name="lote_polio_oral"></td>
                                <td><input type="date" name="venc_polio_oral"></td>
                                <td><input type="number" name="perdidas_polio_oral" min="0"></td>
                                <td>TOXOIDE TETÁNICO DIFTÉRICO</td>
                                <td><input type="text" name="lote_toxoide"></td>
                                <td><input type="date" name="venc_toxoide"></td>
                                <td><input type="number" name="perdidas_toxoide" min="0"></td>
                                <td><input type="text" name="lote_otro_nombre[]"></td>
                                <td><input type="text" name="lote_otro_lote[]"></td>
                                <td><input type="date" name="lote_otro_venc[]"></td>
                                <td><input type="number" name="lote_otro_perdidas[]" min="0"></td>
                            </tr>
                            <tr>
                                <td>HEPATITIS B (PEDIÁTRICO)</td>
                                <td><input type="text" name="lote_hepb_ped"></td>
                                <td><input type="date" name="venc_hepb_ped"></td>
                                <td><input type="number" name="perdidas_hepb_ped" min="0"></td>
                                <td>NEUMOCOCO CONJUGADA</td>
                                <td><input type="text" name="lote_neumo_conj"></td>
                                <td><input type="date" name="venc_neumo_conj"></td>
                                <td><input type="number" name="perdidas_neumo_conj" min="0"></td>
                                <td>NEUMOCOCO POLISACÁRIDA</td>
                                <td><input type="text" name="lote_neumo_poli"></td>
                                <td><input type="date" name="venc_neumo_poli"></td>
                                <td><input type="number" name="perdidas_neumo_poli" min="0"></td>
                                <td><input type="text" name="lote_otro_nombre[]"></td>
                                <td><input type="text" name="lote_otro_lote[]"></td>
                                <td><input type="date" name="lote_otro_venc[]"></td>
                                <td><input type="number" name="lote_otro_perdidas[]" min="0"></td>
                            </tr>
                            <tr>
                                <td>ROTAVIRUS</td>
                                <td><input type="text" name="lote_rotavirus"></td>
                                <td><input type="date" name="venc_rotavirus"></td>
                                <td><input type="number" name="perdidas_rotavirus" min="0"></td>
                                <td>INFLUENZA ESTACIONAL</td>
                                <td><input type="text" name="lote_influenza"></td>
                                <td><input type="date" name="venc_influenza"></td>
                                <td><input type="number" name="perdidas_influenza" min="0"></td>
                                <td>MENINGOCÓCICA B-C</td>
                                <td><input type="text" name="lote_meningo"></td>
                                <td><input type="date" name="venc_meningo"></td>
                                <td><input type="number" name="perdidas_meningo" min="0"></td>
                                <td><input type="text" name="lote_otro_nombre[]"></td>
                                <td><input type="text" name="lote_otro_lote[]"></td>
                                <td><input type="date" name="lote_otro_venc[]"></td>
                                <td><input type="number" name="lote_otro_perdidas[]" min="0"></td>
                            </tr>
                            <tr>
                                <td>PENTAVALENTE</td>
                                <td><input type="text" name="lote_pentavalente"></td>
                                <td><input type="date" name="venc_pentavalente"></td>
                                <td><input type="number" name="perdidas_pentavalente" min="0"></td>
                                <td>FIEBRE AMARILLA</td>
                                <td><input type="text" name="lote_fiebre_ama"></td>
                                <td><input type="date" name="venc_fiebre_ama"></td>
                                <td><input type="number" name="perdidas_fiebre_ama" min="0"></td>
                                <td>RABIA HUMANA</td>
                                <td><input type="text" name="lote_rabia"></td>
                                <td><input type="date" name="venc_rabia"></td>
                                <td><input type="number" name="perdidas_rabia" min="0"></td>
                                <td><input type="text" name="lote_otro_nombre[]"></td>
                                <td><input type="text" name="lote_otro_lote[]"></td>
                                <td><input type="date" name="lote_otro_venc[]"></td>
                                <td><input type="number" name="lote_otro_perdidas[]" min="0"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="action-btn save-btn">
                    <i class="fas fa-save"></i> Guardar Registro
                </button>
            </div>
        </form>
        
        <div class="footer">
            Sistema de Información del Programa Ampliado de Inmunizaciones &copy; 2023
        </div>
    </div>
    
    <div class="notification" id="notification">
        <i class="fas fa-check-circle"></i> <span id="notification-text">Registro guardado exitosamente!</span>
    </div>
    
    <script>
        // Contador para filas
        let rowCount = 1;
        
        // Función para agregar fila
        document.getElementById('add-row').addEventListener('click', function() {
            rowCount++;
            const table = document.getElementById('vaccination-table').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            
            newRow.innerHTML = `
    <td class="serial">${rowCount}</td>
    <td><input type="text" name="nombre[]" required></td>
    <td><input type="text" name="apellido[]" required></td>
    <td><input type="date" name="fecha_nacimiento[]" required></td>
    <td>
        <select name="nacionalidad[]">
            <option value="">Seleccionar</option>
            <option value="Ecuatoriana">Ecuatoriana</option>
            <option value="Colombiana">Colombiana</option>
            <option value="Peruana">Peruana</option>
            <option value="Venezolana">Venezolana</option>
            <option value="Otra">Otra</option>
        </select>
    </td>
    <td><input type="text" name="documento[]" required></td>
    <td><input type="number" name="orden_hijo[]" min="1" max="20"></td>
    <td><input type="text" name="direccion[]"></td>
    <td><input type="text" name="etnia[]"></td>
    <td><input type="number" name="edad[]" min="0" max="120"></td>
    <td>
        <select name="sexo[]">
            <option value="M">M</option>
            <option value="F">F</option>
        </select>
    </td>
    <td><input type="text" name="grupo_especial[]"></td>
    <!-- BCG -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
        </div>
        <input type="hidden" name="dosis_bcg[]" value="">
    </td>
    <!-- Hepatitis B -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
            <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
        </div>
        <input type="hidden" name="dosis_hepb[]" value="">
    </td>
    <!-- Rotavirus -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
        </div>
        <input type="hidden" name="dosis_rotavirus[]" value="">
    </td>
    <!-- Pentavalente -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
            <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
            <button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button>
        </div>
        <input type="hidden" name="dosis_pentavalente[]" value="">
    </td>
    <!-- Polio Inyectable -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
        </div>
        <input type="hidden" name="dosis_polio_iny[]" value="">
    </td>
    <!-- Polio Oral -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
            <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
            <button type="button" class="dosis-btn d9" data-dosis="2REF">2REF</button>
            <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
        </div>
        <input type="hidden" name="dosis_polio_oral[]" value="">
    </td>
    <!-- Neumococo Conjugada -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
        </div>
        <input type="hidden" name="dosis_neumo_conj[]" value="">
    </td>
    <!-- Influenza Estacional -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d12" data-dosis="DE">DE</button>
        </div>
        <input type="hidden" name="dosis_influenza[]" value="">
    </td>
    <!-- FIEBRE AMARILLA -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d11" data-dosis="DU">DU</button>
        </div>
        <input type="hidden" name="dosis_fiebre_ama[]" value="">
    </td>
    <!-- SARAMPIÓN RUBÉOLA PAROTIDITIS (SRP) -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
        </div>
        <input type="hidden" name="dosis_srp[]" value="">
    </td>
    <!-- TOXOIDE TETÁNICO DIFTÉRICO -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
            <button type="button" class="dosis-btn d4" data-dosis="4D">4D</button>
            <button type="button" class="dosis-btn d5" data-dosis="5D">5D</button>
            <button type="button" class="dosis-btn d10" data-dosis="DA">DA</button>
        </div>
        <input type="hidden" name="dosis_toxoide[]" value="">
    </td>
    <!-- NEUMOCOCO POLISACÁRIDA -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
        </div>
        <input type="hidden" name="dosis_neumo_poli[]" value="">
    </td>
    <!-- MENINGOCÓCICA B-C -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
        </div>
        <input type="hidden" name="dosis_meningo[]" value="">
    </td>
    <!-- ANTI-RÁBICA HUMANA PRE -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d8" data-dosis="1REF">1REF</button>
        </div>
        <input type="hidden" name="dosis_rabia_pre[]" value="">
    </td>
    <!-- ANTI-RÁBICA HUMANA POST -->
    <td>
        <div class="dosis-container">
            <button type="button" class="dosis-btn d1" data-dosis="1D">1D</button>
            <button type="button" class="dosis-btn d2" data-dosis="2D">2D</button>
            <button type="button" class="dosis-btn d3" data-dosis="3D">3D</button>
            <button type="button" class="dosis-btn d4" data-dosis="4D">4D</button>
            <button type="button" class="dosis-btn d5" data-dosis="5D">5D</button>
            <button type="button" class="dosis-btn d6" data-dosis="6D">6D</button>
            <button type="button" class="dosis-btn d7" data-dosis="7D">7D</button>
        </div>
        <input type="hidden" name="dosis_rabia_post[]" value="">
    </td>
    <td><input type="text" name="observaciones[]"></td>
    <td>
        <button type="button" class="delete-row"><i class="fas fa-trash"></i></button>
    </td>
`;
            
            // Agregar eventos a los botones de dosis
            addDosisButtonEvents(newRow);
            
            // Agregar evento al botón de eliminar
            newRow.querySelector('.delete-row').addEventListener('click', function() {
                const inputs = newRow.querySelectorAll('input, select');
                let isEmpty = true;
                
                // Verificar si todos los campos están vacíos
                inputs.forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        isEmpty = false;
                    }
                });
                
                if (isEmpty || confirm('¿Está seguro que desea eliminar esta fila? Tiene datos ingresados.')) {
                    newRow.remove();
                    updateRowNumbers();
                    rowCount--;
                }
            });
        });
        
        // Función para actualizar números de fila
        function updateRowNumbers() {
            const rows = document.querySelectorAll('#vaccination-table tbody tr');
            rows.forEach((row, index) => {
                row.querySelector('.serial').textContent = index + 1;
            });
        }
        
        // Función para agregar eventos a botones de dosis
        function addDosisButtonEvents(row) {
            const dosisButtons = row.querySelectorAll('.dosis-btn');
            
            dosisButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const dosisValue = this.getAttribute('data-dosis');
                    const hiddenInput = this.closest('td').querySelector('input[type="hidden"]');
                    
                    // Toggle para las dosis
                    this.classList.toggle('active');
                    
                    // Actualizar valor del campo oculto
                    const selectedDosis = [];
                    this.closest('td').querySelectorAll('.dosis-btn.active').forEach(btn => {
                        selectedDosis.push(btn.getAttribute('data-dosis'));
                    });
                    
                    hiddenInput.value = selectedDosis.join(',');
                });
            });
        }
        
        // Agregar eventos a botones de dosis en la fila inicial
        document.querySelectorAll('.dosis-btn').forEach(button => {
            button.addEventListener('click', function() {
                const dosisValue = this.getAttribute('data-dosis');
                const hiddenInput = this.closest('td').querySelector('input[type="hidden"]');
                
                // Toggle para la dosis
                this.classList.toggle('active');
                
                // Actualizar valor del campo oculto
                const selectedDosis = [];
                this.closest('td').querySelectorAll('.dosis-btn.active').forEach(btn => {
                    selectedDosis.push(btn.getAttribute('data-dosis'));
                });
                
                hiddenInput.value = selectedDosis.join(',');
            });
        });
        
        // Agregar eventos a botones de estrategia
        document.querySelectorAll('.estrategia-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remover clase activa de todos los botones
                document.querySelectorAll('.estrategia-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Agregar clase activa al botón seleccionado
                this.classList.add('active');
                
                // Actualizar valor del campo oculto
                document.querySelector('input[name="estrategia"]').value = this.getAttribute('data-estrategia');
           
            });
        });
        
        // Agregar eventos a botones de eliminar
        document.querySelectorAll('.delete-row').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const inputs = row.querySelectorAll('input, select');
                let isEmpty = true;
                
                // Verificar si todos los campos están vacíos
                inputs.forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        isEmpty = false;
                    }
                });
                
                if (isEmpty || confirm('¿Está seguro que desea eliminar esta fila? Tiene datos ingresados.')) {
                    row.remove();
                    updateRowNumbers();
                    rowCount--;
                }
            });
        });
        
        // Manejar el envío del formulario
        document.getElementById('vaccination-form').addEventListener('submit', function(e) {
            // e.preventDefault(); // Solo si quieres validar antes de enviar
            // ...validaciones...
            if (!hasDosis) {
                showNotification('Debe seleccionar al menos una dosis en alguna fila', 'error');
                e.preventDefault();
                return;
            }
            if (!document.querySelector('input[name=\"estrategia\"]').value) {
                showNotification('Debe seleccionar una estrategia de vacunación', 'error');
                e.preventDefault();
                return;
            }
            // Si pasa las validaciones, se envía el formulario realmente
            // showNotification('Registro guardado exitosamente!', 'success'); // Esto ya no es necesario aquí
            // Enviar el formulario
            // this.submit(); // <-- DESCOMENTA ESTA LÍNEA
        });
        
        // Función para mostrar notificaciones
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notification-text');
            
            notificationText.textContent = message;
            
            // Cambiar color según el tipo
            if (type === 'error') {
                notification.classList.add('error');
            } else {
                notification.classList.remove('error');
            }
            
            notification.classList.add('show');
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>

<!-- Este archivo debe ser renombrado a registrar_jornada.php y actualizar los enlaces internos en el sistema. -->