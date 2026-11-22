
<?php

require_once __DIR__ . '/Data/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

// Zona horaria de Monterrey
date_default_timezone_set('America/Monterrey');

try {

    // =====================================
    // 1. CONECTAR CON MONGODB
    // =====================================

    $db = Conexion::conectar();
    $coleccion = $db->citas;

    $zonaHoraria = new DateTimeZone('America/Monterrey');

    // Fecha y hora actuales
    $ahora = new DateTimeImmutable('now', $zonaHoraria);


    // =====================================
    // 2. REVISAR ESTADOS DE LAS CITAS
    // =====================================

    $resultadoRevision = $coleccion->find([]);

    foreach ($resultadoRevision as $cita) {

        $fecha = $cita['fecha'] ?? '';
        $hora = $cita['hora'] ?? '';
        $estadoActual = $cita['estado'] ?? 'pendiente';

        // No modificar citas canceladas
        if ($estadoActual === 'cancelada') {
            continue;
        }

        // Validar formato de fecha y hora
        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ||
            !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)
        ) {
            continue;
        }

        // Construir fecha y hora de la cita
        $hora = substr($hora, 0, 5);

        $fechaHoraTexto = $fecha . ' ' . $hora;

        $fechaHoraCita = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $fechaHoraTexto,
            $zonaHoraria
        );

        // Comprobar que la fecha sea válida
        if (
            !$fechaHoraCita ||
            $fechaHoraCita->format('Y-m-d H:i') !== $fechaHoraTexto
        ) {
            continue;
        }


        // =====================================
        // 3. DETERMINAR EL ESTADO CORRECTO
        // =====================================

        if ($fechaHoraCita < $ahora) {

            $nuevoEstado = 'completada';

        } else {

            $nuevoEstado = 'pendiente';

        }


        // =====================================
        // 4. ACTUALIZAR SI ES NECESARIO
        // =====================================

        if ($estadoActual !== $nuevoEstado) {

            $coleccion->updateOne(

                [
                    '_id' => $cita['_id']
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
    // 5. CONSULTAR TODAS LAS CITAS
    // =====================================

    $resultado = $coleccion->find(
        [],
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
    // 6. ENVIAR CITAS EN FORMATO JSON
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
