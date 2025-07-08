<?php
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

// 1. Guardar la jornada principal
$stmt = $pdo->prepare("INSERT INTO jornadas (fecha, asig, codigo, establecimiento, responsables, hora_inicio, hora_fin, estrategia, estrategia_donde)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $_POST['fecha'],
    $_POST['asig'],
    $_POST['codigo'],
    $_POST['establecimiento'],
    $_POST['responsables'],
    $_POST['hora_inicio'],
    $_POST['hora_fin'],
    $_POST['estrategia'],
    $_POST['estrategia_donde']
]);
$jornada_id = $pdo->lastInsertId();

// 2. Guardar los pacientes y sus dosis
$filas = count($_POST['nombre']);
for ($i = 0; $i < $filas; $i++) {
    $stmt = $pdo->prepare("INSERT INTO jornada_pacientes 
        (jornada_id, nombre, apellido, fecha_nacimiento, nacionalidad, documento, orden_hijo, direccion, etnia, edad, sexo, grupo_especial, observaciones,
        dosis_bcg, dosis_hepb, dosis_rotavirus, dosis_pentavalente, dosis_polio_iny, dosis_polio_oral, dosis_neumo_conj, dosis_influenza, dosis_fiebre_ama,
        dosis_srp, dosis_toxoide, dosis_neumo_poli, dosis_meningo, dosis_rabia_pre, dosis_rabia_post)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $jornada_id,
        $_POST['nombre'][$i] ?? '',
        $_POST['apellido'][$i] ?? '',
        $_POST['fecha_nacimiento'][$i] ?? null,
        $_POST['nacionalidad'][$i] ?? '',
        $_POST['documento'][$i] ?? '',
        $_POST['orden_hijo'][$i] !== '' ? $_POST['orden_hijo'][$i] : null,
        $_POST['direccion'][$i] ?? '',
        $_POST['etnia'][$i] ?? '',
        $_POST['edad'][$i] !== '' ? $_POST['edad'][$i] : null,
        $_POST['sexo'][$i] ?? '',
        $_POST['grupo_especial'][$i] ?? '',
        $_POST['observaciones'][$i] ?? '',
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
        $_POST['dosis_rabia_post'][$i] ?? ''
    ]);
}

// 3. Guardar los lotes normales
$lotes = [
    // [biologico, lote, vencimiento, perdidas]
    ["BCG", $_POST['lote_bcg'] ?? '', $_POST['venc_bcg'] ?? null, $_POST['perdidas_bcg'] ?? null],
    ["POLIO INYECTABLE", $_POST['lote_polio_iny'] ?? '', $_POST['venc_polio_iny'] ?? null, $_POST['perdidas_polio_iny'] ?? null],
    ["SARAMPIÓN/RUBÉOLA/PAROTIDITIS", $_POST['lote_srp'] ?? '', $_POST['venc_srp'] ?? null, $_POST['perdidas_srp'] ?? null],
    ["HEPATITIS B", $_POST['lote_hepb'] ?? '', $_POST['venc_hepb'] ?? null, $_POST['perdidas_hepb'] ?? null],
    ["POLIO ORAL", $_POST['lote_polio_oral'] ?? '', $_POST['venc_polio_oral'] ?? null, $_POST['perdidas_polio_oral'] ?? null],
    ["TOXOIDE TETÁNICO DIFTÉRICO", $_POST['lote_toxoide'] ?? '', $_POST['venc_toxoide'] ?? null, $_POST['perdidas_toxoide'] ?? null],
    ["HEPATITIS B (PEDIÁTRICO)", $_POST['lote_hepb_ped'] ?? '', $_POST['venc_hepb_ped'] ?? null, $_POST['perdidas_hepb_ped'] ?? null],
    ["NEUMOCOCO CONJUGADA", $_POST['lote_neumo_conj'] ?? '', $_POST['venc_neumo_conj'] ?? null, $_POST['perdidas_neumo_conj'] ?? null],
    ["NEUMOCOCO POLISACÁRIDA", $_POST['lote_neumo_poli'] ?? '', $_POST['venc_neumo_poli'] ?? null, $_POST['perdidas_neumo_poli'] ?? null],
    ["ROTAVIRUS", $_POST['lote_rotavirus'] ?? '', $_POST['venc_rotavirus'] ?? null, $_POST['perdidas_rotavirus'] ?? null],
    ["INFLUENZA ESTACIONAL", $_POST['lote_influenza'] ?? '', $_POST['venc_influenza'] ?? null, $_POST['perdidas_influenza'] ?? null],
    ["MENINGOCÓCICA B-C", $_POST['lote_meningo'] ?? '', $_POST['venc_meningo'] ?? null, $_POST['perdidas_meningo'] ?? null],
    ["PENTAVALENTE", $_POST['lote_pentavalente'] ?? '', $_POST['venc_pentavalente'] ?? null, $_POST['perdidas_pentavalente'] ?? null],
    ["FIEBRE AMARILLA", $_POST['lote_fiebre_ama'] ?? '', $_POST['venc_fiebre_ama'] ?? null, $_POST['perdidas_fiebre_ama'] ?? null],
    ["RABIA HUMANA", $_POST['lote_rabia'] ?? '', $_POST['venc_rabia'] ?? null, $_POST['perdidas_rabia'] ?? null]
];

foreach ($lotes as $lote) {
    $stmt = $pdo->prepare("INSERT INTO jornada_lotes (jornada_id, biologico, lote, vencimiento, perdidas, tipo)
        VALUES (?, ?, ?, ?, ?, 'normal')");
    $stmt->execute([$jornada_id, $lote[0], $lote[1] ?? '', $lote[2] ?? null, $lote[3] ?? null]);
}

// 4. Guardar los lotes "otros"
if (!empty($_POST['lote_otro_nombre'])) {
    foreach ($_POST['lote_otro_nombre'] as $k => $nombre) {
        if ($nombre || $_POST['lote_otro_lote'][$k] || $_POST['lote_otro_venc'][$k] || $_POST['lote_otro_perdidas'][$k]) {
            $stmt = $pdo->prepare("INSERT INTO jornada_lotes (jornada_id, biologico, lote, vencimiento, perdidas, tipo)
                VALUES (?, ?, ?, ?, ?, 'otro')");
            $stmt->execute([
                $jornada_id,
                $nombre ?? '',
                $_POST['lote_otro_lote'][$k] ?? '',
                $_POST['lote_otro_venc'][$k] ?? null,
                $_POST['lote_otro_perdidas'][$k] ?? null
            ]);
        }
    }
}

// Redirigir al formulario de edición de la jornada recién creada (ruta absoluta)
header("Location: /Ambulatorio/vacunas/editar_jornada.php?id=$jornada_id&mensaje=Registro+guardado+correctamente");
exit;
?>