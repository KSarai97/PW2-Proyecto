
<?php

require_once __DIR__ . '/Data/Conexion.php';

header('Content-Type: application/json; charset=utf-8');

try {

    // Conectar con MongoDB
    $db = Conexion::conectar();

    // Obtener la colección de citas
    $coleccion = $db->citas;

    // Consultar todas las citas registradas
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

    // Enviar las citas en formato JSON
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
