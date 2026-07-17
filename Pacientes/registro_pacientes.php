<?php
session_start();
$mensaje = '';
$tipo_mensaje = '';

if (isset($_SESSION['registro_error'])) {
  $mensaje = $_SESSION['registro_error'];
  $tipo_mensaje = 'danger';
  unset($_SESSION['registro_error']);
}

// --- LÓGICA PARA AUTO-GENERAR EL NÚMERO DE HISTORIA ---
$conn_db = new mysqli("localhost", "root", "", "ambulatorio");
$conn_db->set_charset("utf8");
if (!$conn_db->connect_error) {
    $resultado = $conn_db->query("SELECT MAX(CAST(numero_historia AS UNSIGNED)) as max_num FROM pacientes");
    $fila = $resultado->fetch_assoc();
    $siguiente_numero = $fila['max_num'] ? intval($fila['max_num']) + 1 : 1;
    $numero_historia_generado = str_pad($siguiente_numero, 4, "0", STR_PAD_LEFT);
    $conn_db->close();
} else {
    $numero_historia_generado = "Error DB"; 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nueva Historia Clínica | Ambulatorio</title>
  
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">

  <style>
    :root {
        --primary-red: #aa0b0b;
        --primary-blue: #003366;
        --bg-light: #eef2f6;
    }
    body { background: var(--bg-light); font-family: 'Segoe UI', system-ui, sans-serif; padding-bottom: 40px; }
    .navbar { background: linear-gradient(90deg, var(--primary-red) 0%, var(--primary-blue) 100%); padding: 10px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .navbar-brand { font-weight: 600; color: white !important; font-size: 1.1rem; }
    .card-form { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; max-width: 1100px; margin: 40px auto; }
    .form-header { text-align: center; margin-bottom: 35px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
    .form-header h2 { color: var(--primary-blue); font-weight: 800; margin-bottom: 5px; font-size: 1.8rem; }
    
    .section-label { color: var(--primary-blue); font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 30px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #e9ecef; display: flex; align-items: center; }
    .section-label::before { content: ''; display: inline-block; width: 5px; height: 20px; background-color: var(--primary-red); margin-right: 10px; border-radius: 5px; }
    
    .form-label { font-weight: 600; color: #555; font-size: 0.9rem; margin-bottom: 6px; }
    .form-control, .form-select { border-radius: 10px; padding: 12px 15px; border: 1px solid #dee2e6; background-color: #f8f9fa; font-size: 0.95rem; transition: all 0.3s; }
    .form-control:focus, .form-select:focus { background-color: #fff; border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1); }
    .form-control[readonly] { background-color: #e9ecef; border-color: #ced4da; color: #aa0b0b; font-weight: bold; font-size: 1.1rem; text-align: center; cursor: not-allowed; }
    
    .btn-guardar { background: var(--primary-red); color: white; border: none; padding: 15px 30px; border-radius: 50px; font-weight: 700; font-size: 1.1rem; width: 100%; transition: 0.3s; box-shadow: 0 4px 15px rgba(170, 11, 11, 0.2); }
    .btn-guardar:hover { background: #8a0000; color: white; transform: translateY(-2px); }
    .btn-cancelar { background: #e9ecef; color: #555; border: none; padding: 15px 30px; border-radius: 50px; font-weight: 700; font-size: 1.1rem; width: 100%; text-decoration: none; display: inline-block; text-align: center; transition: 0.3s; }
    .btn-cancelar:hover { background: #dee2e6; color: #333; }

    /* Estilos para los checkboxes */
    .custom-checkbox-group { background: #f8f9fa; padding: 15px; border-radius: 10px; border: 1px solid #dee2e6; }
    .form-check-label { cursor: pointer; user-select: none; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <img src="../icons/logo.png" alt="Logo" style="height: 40px; background: white; border-radius: 50%; padding: 2px;" onerror="this.style.display='none'"/>
        <span>Nueva Historia Clínica</span>
      </a>
      <div class="ms-auto">
        <a href="pacientes.php" class="text-white text-decoration-none fw-bold" style="font-size: 1.5rem; opacity: 0.8;">&times;</a>
      </div>
    </div>
  </nav>

  <div style="height: 80px;"></div>

  <div class="container px-3">
    <div class="card-form">
        
        <div class="form-header">
            <h2>Registro de Paciente</h2>
            <p class="text-muted m-0">Complete la información para abrir una nueva historia médica integral.</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?> text-center shadow-sm border-0 mb-4" style="border-radius: 10px;">
                <?= htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div id="alertaJS" class="alert alert-warning text-center d-none shadow-sm border-0 mb-4" style="border-radius: 10px; font-weight: 500;"></div>

        <form action="guardar.php" method="post" onsubmit="return validarFormulario()">
            
            <div class="row g-4">
                
                <div class="col-12"><div class="section-label">Datos de Identificación</div></div>

                <div class="col-md-2">
                    <label class="form-label">N° Historia</label>
                    <input class="form-control" type="text" name="numero_historia" id="numero_historia" value="<?php echo htmlspecialchars($numero_historia_generado); ?>" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nac. <span class="text-danger">*</span></label>
                    <select class="form-select" name="nacionalidad" required>
                        <option value="V" selected>V</option>
                        <option value="E">E</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cédula <span class="text-danger">*</span></label>
                    <input class="form-control" type="number" name="cedula" id="cedula" required placeholder="Solo números">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="nombres" id="nombres" required placeholder="Ej: Juan Carlos">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                    <input class="form-control" type="text" name="apellidos" id="apellidos" required placeholder="Ej: Pérez Pérez">
                </div>
                <div class="col-md-3">
                    <label class="form-label">F. Nacimiento</label>
                    <input class="form-control" type="date" name="fecha_nacimiento" id="fecha_nacimiento" onchange="calcularEdad()">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Edad</label>
                    <input class="form-control" type="number" name="edad" id="edad" placeholder="Años" min="0" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sexo <span class="text-danger">*</span></label>
                    <select class="form-select" name="sexo" required>
                        <option value="" selected disabled>...</option>
                        <option value="F">Femenino</option>
                        <option value="M">Masculino</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ocupación</label>
                    <input class="form-control" type="text" name="ocupacion" placeholder="Ej: Estudiante">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono de Contacto</label>
                    <input class="form-control" type="tel" name="telefono" placeholder="Ej: 0414-1234567">
                </div>

                <div class="col-12"><div class="section-label">Ubicación Geográfica</div></div>

                <div class="col-md-4">
                    <label class="form-label">País de Origen</label>
                    <select class="form-select" name="pais" id="pais_origen" onchange="toggleEstado()">
                        <option value="Venezuela" selected>Venezuela</option>
                        <option value="Afganistán">Afganistán</option>
                        <option value="Alemania">Alemania</option>
                        <option value="Argentina">Argentina</option>
                        <option value="Australia">Australia</option>
                        <option value="Bolivia">Bolivia</option>
                        <option value="Brasil">Brasil</option>
                        <option value="Canadá">Canadá</option>
                        <option value="Chile">Chile</option>
                        <option value="China">China</option>
                        <option value="Colombia">Colombia</option>
                        <option value="Corea del Sur">Corea del Sur</option>
                        <option value="Costa Rica">Costa Rica</option>
                        <option value="Cuba">Cuba</option>
                        <option value="Ecuador">Ecuador</option>
                        <option value="El Salvador">El Salvador</option>
                        <option value="España">España</option>
                        <option value="Estados Unidos">Estados Unidos</option>
                        <option value="Francia">Francia</option>
                        <option value="Guatemala">Guatemala</option>
                        <option value="Haití">Haití</option>
                        <option value="Honduras">Honduras</option>
                        <option value="Italia">Italia</option>
                        <option value="Japón">Japón</option>
                        <option value="México">México</option>
                        <option value="Nicaragua">Nicaragua</option>
                        <option value="Panamá">Panamá</option>
                        <option value="Paraguay">Paraguay</option>
                        <option value="Perú">Perú</option>
                        <option value="Portugal">Portugal</option>
                        <option value="Puerto Rico">Puerto Rico</option>
                        <option value="Reino Unido">Reino Unido</option>
                        <option value="República Dominicana">República Dominicana</option>
                        <option value="Rusia">Rusia</option>
                        <option value="Uruguay">Uruguay</option>
                        <option value="Otro">Otro (No listado)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Estado / Provincia</label>
                    
                    <!-- Select para Venezuela -->
                    <select class="form-select" name="estado" id="estado_vzla">
                        <option value="" selected disabled>Seleccione Estado...</option>
                        <option value="Amazonas">Amazonas</option>
                        <option value="Anzoátegui">Anzoátegui</option>
                        <option value="Apure">Apure</option>
                        <option value="Aragua">Aragua</option>
                        <option value="Barinas">Barinas</option>
                        <option value="Bolívar">Bolívar</option>
                        <option value="Carabobo">Carabobo</option>
                        <option value="Cojedes">Cojedes</option>
                        <option value="Delta Amacuro">Delta Amacuro</option>
                        <option value="Distrito Capital">Distrito Capital</option>
                        <option value="Falcón">Falcón</option>
                        <option value="Guárico">Guárico</option>
                        <option value="La Guaira">La Guaira (Vargas)</option>
                        <option value="Lara">Lara</option>
                        <option value="Mérida">Mérida</option>
                        <option value="Miranda">Miranda</option>
                        <option value="Monagas">Monagas</option>
                        <option value="Nueva Esparta">Nueva Esparta</option>
                        <option value="Portuguesa">Portuguesa</option>
                        <option value="Sucre">Sucre</option>
                        <option value="Táchira">Táchira</option>
                        <option value="Trujillo">Trujillo</option>
                        <option value="Yaracuy">Yaracuy</option>
                        <option value="Zulia">Zulia</option>
                    </select>

                    <!-- Input para países extranjeros (Oculto por defecto) -->
                    <input class="form-control d-none" type="text" id="estado_otro" placeholder="Escriba la provincia/estado">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Lugar de Nacimiento</label>
                    <input class="form-control" type="text" name="lugar_nacimiento" placeholder="Ciudad/Pueblo">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Dirección de Habitación Exacta</label>
                    <input class="form-control" type="text" name="direccion" placeholder="Ej: Av. Principal, Sector El Llano, Casa N° 123">
                </div>

                <div class="col-12"><div class="section-label">Antecedentes Familiares / Personales</div></div>
                
                <div class="col-12">
                    <div class="custom-checkbox-group d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input check-antecedente" type="checkbox" name="antecedentes[]" value="Asma" id="ant_asma">
                            <label class="form-check-label" for="ant_asma">Asma</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input check-antecedente" type="checkbox" name="antecedentes[]" value="Cardiopatía" id="ant_cardio">
                            <label class="form-check-label" for="ant_cardio">Cardiopatía</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input check-antecedente" type="checkbox" name="antecedentes[]" value="Hipertensión" id="ant_hiper">
                            <label class="form-check-label" for="ant_hiper">Hipertensión</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input check-antecedente" type="checkbox" name="antecedentes[]" value="Diabetes" id="ant_diab">
                            <label class="form-check-label" for="ant_diab">Diabetes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input check-antecedente" type="checkbox" name="antecedentes[]" value="Cáncer" id="ant_cancer">
                            <label class="form-check-label" for="ant_cancer">Cáncer</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input check-antecedente" type="checkbox" name="antecedentes[]" value="Otros" id="ant_otros">
                            <label class="form-check-label" for="ant_otros">Otros</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input check-antecedente" type="checkbox" name="antecedentes[]" value="Alergias" id="ant_alergias">
                            <label class="form-check-label fw-bold text-danger" for="ant_alergias">Alergias</label>
                        </div>
                        <div class="form-check ms-auto">
                            <input class="form-check-input" type="checkbox" name="antecedentes[]" value="Ninguno" id="ant_ninguno">
                            <label class="form-check-label fw-bold" for="ant_ninguno">Ninguno</label>
                        </div>
                    </div>
                    
                    <!-- Campo oculto para describir las alergias -->
                    <div class="mt-3 d-none" id="div_alergias">
                        <label class="form-label text-danger">¿Cuáles alergias?</label>
                        <input type="text" class="form-control border-danger" name="alergias_desc" id="alergias_desc" placeholder="Especifique a qué es alérgico el paciente...">
                    </div>
                </div>

                <div class="col-12"><div class="section-label">Datos Clínicos Iniciales</div></div>

                <div class="col-md-3">
                    <label class="form-label">Peso (Kg)</label>
                    <input class="form-control" type="number" step="0.01" name="peso" placeholder="Ej: 70.5">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Altura (Metros)</label>
                    <input class="form-control" type="number" step="0.01" name="Altura" placeholder="Ej: 1.75">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Observaciones / Motivo de Registro</label>
                    <textarea class="form-control" rows="3" name="observaciones" placeholder="Notas adicionales relevantes para la apertura de la historia..."></textarea>
                </div>

            </div>

            <div class="row mt-5 pt-4 border-top g-3">
                <div class="col-md-6 order-md-2">
                    <button type="submit" class="btn-guardar"><i class="fas fa-save me-2"></i> Guardar Historia Clínica</button>
                </div>
                <div class="col-md-6 order-md-1">
                    <a href="pacientes.php" class="btn-cancelar">Cancelar</a>
                </div>
            </div>

        </form>
    </div>
  </div>

  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    // Lógica para cambiar Estado/Provincia según País seleccionado
    function toggleEstado() {
        var pais = document.getElementById('pais_origen').value;
        var selectVzla = document.getElementById('estado_vzla');
        var inputOtro = document.getElementById('estado_otro');

        if (pais === 'Venezuela') {
            // Mostrar lista de estados Vzla y asignarle el nombre "estado" para PHP
            selectVzla.classList.remove('d-none');
            selectVzla.setAttribute('name', 'estado');
            
            // Ocultar caja de texto para otros y quitarle el nombre
            inputOtro.classList.add('d-none');
            inputOtro.removeAttribute('name');
        } else {
            // Ocultar lista de estados Vzla y quitarle el nombre
            selectVzla.classList.add('d-none');
            selectVzla.removeAttribute('name');
            
            // Mostrar caja de texto y asignarle el nombre "estado" para PHP
            inputOtro.classList.remove('d-none');
            inputOtro.setAttribute('name', 'estado');
        }
    }

    // Calcular edad automáticamente
    function calcularEdad() {
        var fechaNac = document.getElementById('fecha_nacimiento').value;
        if(fechaNac) {
            var hoy = new Date();
            var cumpleanos = new Date(fechaNac);
            var edad = hoy.getFullYear() - cumpleanos.getFullYear();
            var m = hoy.getMonth() - cumpleanos.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < cumpleanos.getDate())) { edad--; }
            document.getElementById('edad').value = edad;
        }
    }

    // Lógica interactiva de los checkboxes de Antecedentes
    const checkAlergias = document.getElementById('ant_alergias');
    const divAlergias = document.getElementById('div_alergias');
    const inputAlergias = document.getElementById('alergias_desc');
    const checkNinguno = document.getElementById('ant_ninguno');
    const otrosChecks = document.querySelectorAll('.check-antecedente');

    // Mostrar/Ocultar campo de alergias
    checkAlergias.addEventListener('change', function() {
        if(this.checked) {
            divAlergias.classList.remove('d-none');
            inputAlergias.required = true;
        } else {
            divAlergias.classList.add('d-none');
            inputAlergias.required = false;
            inputAlergias.value = '';
        }
    });

    // Si marca "Ninguno", desmarcar todos los demás
    checkNinguno.addEventListener('change', function() {
        if(this.checked) {
            otrosChecks.forEach(chk => chk.checked = false);
            divAlergias.classList.add('d-none');
            inputAlergias.required = false;
            inputAlergias.value = '';
        }
    });

    // Si marca cualquier otro, desmarcar "Ninguno"
    otrosChecks.forEach(chk => {
        chk.addEventListener('change', function() {
            if(this.checked) checkNinguno.checked = false;
        });
    });

    // Validación Final
    function validarFormulario() {
      var cedula = document.getElementById('cedula').value.trim();
      var nombres = document.getElementById('nombres').value.trim();
      var apellidos = document.getElementById('apellidos').value.trim();
      var alerta = document.getElementById('alertaJS');
      alerta.classList.add('d-none'); 

      if (!cedula || !nombres || !apellidos) {
        alerta.textContent = 'Por favor, complete Cédula, Nombres y Apellidos.';
        alerta.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' }); 
        return false;
      }
      if (cedula.length < 5 || cedula.length > 10) {
        alerta.textContent = 'La cédula debe tener entre 5 y 10 dígitos.';
        alerta.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
      }
      return true;
    }
  </script>

</body>
</html>