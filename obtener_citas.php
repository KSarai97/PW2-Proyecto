
<?php

session_start();

require_once __DIR__ . '/Data/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Monterrey');

// Comprobar que exista una sesión activa
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Debes iniciar sesión.'
    ]);
    exit();
}

try {

    // =====================================
    // 1. CONECTAR CON MONGODB
    // =====================================

    $db = Conexion::conectar();

    $coleccionUsuarios = $db->usuarios;
    $coleccionCitas = $db->citas;

    // =====================================
    // 2. IDENTIFICAR AL USUARIO LOGUEADO
    // =====================================

    $nombreUsuario = $_SESSION['usuario'];

    $documentoUsuario = $coleccionUsuarios->findOne([
        'usuarios.usuario' => $nombreUsuario
    ]);

    if (!$documentoUsuario) {
        throw new Exception('Usuario no encontrado.');
    }

    $usuarioEncontrado = null;

    foreach ($documentoUsuario['usuarios'] as $usuario) {

        if ($usuario['usuario'] === $nombreUsuario) {
            $usuarioEncontrado = $usuario;
            break;
        }

    }

    if (!$usuarioEncontrado) {
        throw new Exception('No se encontraron los datos del usuario.');
    }

    $idUsuario = $usuarioEncontrado['id'];

    // =====================================
    // 3. CONSULTAR SOLAMENTE SUS CITAS
    // =====================================

    $filtroUsuario = [
        'id_usuario' => $idUsuario
    ];

    $zonaHoraria = new DateTimeZone('America/Monterrey');

    $ahora = new DateTimeImmutable('now', $zonaHoraria);

    // =====================================
    // 4. ACTUALIZAR ESTADOS DE SUS CITAS
    // =====================================

    $resultadoRevision = $coleccionCitas->find(
        $filtroUsuario
    );

    foreach ($resultadoRevision as $cita) {

        $fecha = $cita['fecha'] ?? '';
        $hora = $cita['hora'] ?? '';
        $estadoActual = $cita['estado'] ?? 'pendiente';

        if ($estadoActual === 'cancelada') {
            continue;
        }

        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ||
            !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)
        ) {
            continue;
        }

        $hora = substr($hora, 0, 5);

        $fechaHoraTexto = $fecha . ' ' . $hora;

        $fechaHoraCita = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $fechaHoraTexto,
            $zonaHoraria
        );

        if (
            !$fechaHoraCita ||
            $fechaHoraCita->format('Y-m-d H:i') !== $fechaHoraTexto
        ) {
            continue;
        }

        $nuevoEstado = ($fechaHoraCita < $ahora)
            ? 'completada'
            : 'pendiente';

        if ($estadoActual !== $nuevoEstado) {

            $coleccionCitas->updateOne(
                [
                    '_id' => $cita['_id'],
                    'id_usuario' => $idUsuario
                ],
                [
                    '$set' => [
                        'estado' => $nuevoEstado
                    ]
                ]
            );

        }

    }

    // =====================================
    // 5. OBTENER SOLAMENTE SUS CITAS
    // =====================================

    $resultado = $coleccionCitas->find(
        $filtroUsuario,
        [
            'sort' => [
                'fecha' => 1,
                'hora' => 1
            ]
        ]
    );

    $citas = [];

    foreach ($resultado as $cita) {

        $citas[] = [
            'id' => (string) $cita['_id'],
            'fecha' => $cita['fecha'] ?? '',
            'hora' => $cita['hora'] ?? '',
            'especialista' => $cita['especialista'] ?? '',
            'modalidad' => $cita['modalidad'] ?? '',
            'motivo' => $cita['motivo'] ?? '',
            'estado' => $cita['estado'] ?? 'pendiente'
        ];

    }

    // =====================================
    // 6. ENVIAR RESPUESTA JSON
    // =====================================

    echo json_encode(
        $citas,
        JSON_UNESCAPED_UNICODE
    );

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'error' => 'No se pudieron obtener las citas.'
    ]);

}

?>
