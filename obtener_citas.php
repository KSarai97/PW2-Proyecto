
<?php

require_once __DIR__ . '/Data/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

// Zona horaria de Monterrey, México
date_default_timezone_set('America/Monterrey');

try {

    // =====================================
    // 1. CONECTAR CON MONGODB
    // =====================================

    $db = Conexion::conectar();

    // Obtener colección de citas
    $coleccion = $db->citas;

    // Fecha actual
    $hoy = date('Y-m-d');


    // =====================================
    // 2. ACTUALIZAR CITAS VENCIDAS
    // =====================================

    $coleccion->updateMany(

        [
            'fecha' => ['$lt' => $hoy],
            'estado' => ['$ne' => 'cancelada']
        ],

        [
            '$set' => [
                'estado' => 'completada'
            ]
        ]

    );


    // =====================================
    // 3. REGRESAR A PENDIENTES
    //    LAS CITAS DE HOY O FUTURAS
    // =====================================

    $coleccion->updateMany(

        [
            'fecha' => ['$gte' => $hoy],
            'estado' => 'completada'
        ],

        [
            '$set' => [
                'estado' => 'pendiente'
            ]
        ]

    );


    // =====================================
    // 4. CONSULTAR TODAS LAS CITAS
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
    // 5. ENVIAR CITAS EN FORMATO JSON
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
