<?php

// Configuración de la conexión a la base de datos
$host = "localhost";
$dbname = "ambulatorio";
$user = "root";
$pass = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }

    // Obtener el ID de la jornada
    $jornada_id = $_POST['jornada_id'] ?? null;
    if (!$jornada_id) {
        die('ID de jornada no válido.');
    }

    // 1. Actualizar datos de la jornada
    $sql = "UPDATE jornadas SET fecha=?, asig=?, codigo=?, establecimiento=?, responsables=?, hora_inicio=?, hora_fin=?, estrategia=?, estrategia_donde=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['fecha'] ?? null,
        $_POST['asig'] ?? null,
        $_POST['codigo'] ?? null,
        $_POST['establecimiento'] ?? null,
        $_POST['responsables'] ?? null,
        $_POST['hora_inicio'] ?? null,
        $_POST['hora_fin'] ?? null,
        $_POST['estrategia'] ?? null,
        $_POST['estrategia_donde'] ?? null,
        $jornada_id
    ]);

    // 2. Eliminar pacientes y volver a insertar
    $pdo->prepare('DELETE FROM jornada_pacientes WHERE jornada_id=?')->execute([$jornada_id]);
    if (!empty($_POST['nombre'])) {
        $n = count($_POST['nombre']);
        $sql = "INSERT INTO jornada_pacientes (jornada_id, nombre, apellido, fecha_nacimiento, nacionalidad, documento, orden_hijo, direccion, etnia, edad, sexo, grupo_especial, dosis_bcg, dosis_hepb, dosis_rotavirus, dosis_pentavalente, dosis_polio_iny, dosis_polio_oral, dosis_neumo_conj, dosis_influenza, dosis_fiebre_ama, dosis_srp, dosis_toxoide, dosis_neumo_poli, dosis_meningo, dosis_rabia_pre, dosis_rabia_post, observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        for ($i = 0; $i < $n; $i++) {
            $orden_hijo = isset($_POST['orden_hijo'][$i]) && $_POST['orden_hijo'][$i] !== '' ? $_POST['orden_hijo'][$i] : null;
            $stmt->execute([
                $jornada_id,
                $_POST['nombre'][$i] ?? '',
                $_POST['apellido'][$i] ?? '',
                $_POST['fecha_nacimiento'][$i] ?? null,
                $_POST['nacionalidad'][$i] ?? '',
                $_POST['documento'][$i] ?? '',
                $orden_hijo,
                $_POST['direccion'][$i] ?? '',
                $_POST['etnia'][$i] ?? '',
                $_POST['edad'][$i] ?? null,
                $_POST['sexo'][$i] ?? '',
                $_POST['grupo_especial'][$i] ?? '',
                $_POST['dosis_bcg'][$i] ?? '',
                $_POST['dosis_hepb'][$i] ?? '',
                $_POST['dosis_rotavirus'][$i] ?? '',
                $_POST['dosis_pentavalente'][$i] ?? '',
                $_POST['dosis_polio_iny'][$i] ?? '',
                $_POST['dosis_polio_oral'][$i] ?? '',
                $_POST['dosis_neumo_conj'][$i] ?? '',
                $_POST['dosis_influenza'][$i] ?? '',
                $_POST['dosis_fiebre_ama'][$i] ?? '',
                $_POST['dosis_srp'][$i] ?? '',
                $_POST['dosis_toxoide'][$i] ?? '',
                $_POST['dosis_neumo_poli'][$i] ?? '',
                $_POST['dosis_meningo'][$i] ?? '',
                $_POST['dosis_rabia_pre'][$i] ?? '',
                $_POST['dosis_rabia_post'][$i] ?? '',
                $_POST['observaciones'][$i] ?? ''
            ]);
        }
    }

    // 3. Eliminar lotes y volver a insertar
    $pdo->prepare('DELETE FROM jornada_lotes WHERE jornada_id=?')->execute([$jornada_id]);
    $lotes = [
        // nombre, lote, vencimiento, perdidas
        ['BCG', $_POST['lote_bcg'] ?? '', $_POST['venc_bcg'] ?? '', $_POST['perdidas_bcg'] ?? ''],
        ['POLIO INYECTABLE', $_POST['lote_polio_iny'] ?? '', $_POST['venc_polio_iny'] ?? '', $_POST['perdidas_polio_iny'] ?? ''],
        ['SARAMPIÓN/RUBÉOLA/PAROTIDITIS', $_POST['lote_srp'] ?? '', $_POST['venc_srp'] ?? '', $_POST['perdidas_srp'] ?? ''],
        ['HEPATITIS B', $_POST['lote_hepb'] ?? '', $_POST['venc_hepb'] ?? '', $_POST['perdidas_hepb'] ?? ''],
        ['POLIO ORAL', $_POST['lote_polio_oral'] ?? '', $_POST['venc_polio_oral'] ?? '', $_POST['perdidas_polio_oral'] ?? ''],
        ['TOXOIDE TETÁNICO DIFTÉRICO', $_POST['lote_toxoide'] ?? '', $_POST['venc_toxoide'] ?? '', $_POST['perdidas_toxoide'] ?? ''],
        ['HEPATITIS B (PEDIÁTRICO)', $_POST['lote_hepb_ped'] ?? '', $_POST['venc_hepb_ped'] ?? '', $_POST['perdidas_hepb_ped'] ?? ''],
        ['NEUMOCOCO CONJUGADA', $_POST['lote_neumo_conj'] ?? '', $_POST['venc_neumo_conj'] ?? '', $_POST['perdidas_neumo_conj'] ?? ''],
        ['NEUMOCOCO POLISACÁRIDA', $_POST['lote_neumo_poli'] ?? '', $_POST['venc_neumo_poli'] ?? '', $_POST['perdidas_neumo_poli'] ?? ''],
        ['ROTAVIRUS', $_POST['lote_rotavirus'] ?? '', $_POST['venc_rotavirus'] ?? '', $_POST['perdidas_rotavirus'] ?? ''],
        ['INFLUENZA ESTACIONAL', $_POST['lote_influenza'] ?? '', $_POST['venc_influenza'] ?? '', $_POST['perdidas_influenza'] ?? ''],
        ['MENINGOCÓCICA B-C', $_POST['lote_meningo'] ?? '', $_POST['venc_meningo'] ?? '', $_POST['perdidas_meningo'] ?? ''],
        ['PENTAVALENTE', $_POST['lote_pentavalente'] ?? '', $_POST['venc_pentavalente'] ?? '', $_POST['perdidas_pentavalente'] ?? ''],
        ['FIEBRE AMARILLA', $_POST['lote_fiebre_ama'] ?? '', $_POST['venc_fiebre_ama'] ?? '', $_POST['perdidas_fiebre_ama'] ?? ''],
        ['RABIA HUMANA', $_POST['lote_rabia'] ?? '', $_POST['venc_rabia'] ?? '', $_POST['perdidas_rabia'] ?? ''],
    ];
    // Lotes "otros"
    if (!empty($_POST['lote_otro_nombre'])) {
        $otros_nombres = $_POST['lote_otro_nombre'];
        $otros_lotes = $_POST['lote_otro_lote'];
        $otros_venc = $_POST['lote_otro_venc'];
        $otros_perdidas = $_POST['lote_otro_perdidas'];
        for ($i = 0; $i < count($otros_nombres); $i++) {
            if ($otros_nombres[$i] || $otros_lotes[$i] || $otros_venc[$i] || $otros_perdidas[$i]) {
                $lotes[] = [
                    $otros_nombres[$i],
                    $otros_lotes[$i],
                    $otros_venc[$i],
                    $otros_perdidas[$i]
                ];
            }
        }
    }
    $sql = "INSERT INTO jornada_lotes (jornada_id, biologico, lote, vencimiento, perdidas) VALUES (?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    foreach ($lotes as $lote) {
        $stmt->execute([
            $jornada_id,
            $lote[0],
            $lote[1],
            $lote[2],
            $lote[3]
        ]);
    }

    // Redirigir al mismo registro editado (ruta absoluta)
    header("Location: /Ambulatorio/vacunas/editar_jornada.php?id=$jornada_id&mensaje=Registro+actualizado+correctamente");
    exit;
} else {
    echo 'Acceso no permitido.';
}
