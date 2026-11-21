
<?php

require_once __DIR__ . '/Data/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: calendario.php");
    exit();
}

try {

    // Obtener ID de la cita
    $idCita = $_POST['id_cita'] ?? '';

    // Validar identificador
    if (!preg_match('/^[a-fA-F0-9]{24}$/', $idCita)) {
        throw new Exception("El identificador de la cita no es válido.");
    }

    // Conectar con MongoDB
    $db = Conexion::conectar();

    $coleccion = $db->citas;

    $objectId = new MongoDB\BSON\ObjectId($idCita);

    // Cancelar solamente citas que no estén canceladas
    $resultado = $coleccion->updateOne(
        [
            '_id' => $objectId,
            'estado' => ['$ne' => 'cancelada']
        ],
        [
            '$set' => [
                'estado' => 'cancelada'
            ]
        ]
    );

    if ($resultado->getModifiedCount() === 1) {

        header("Location: calendario.php?cancelacion=exitosa");
        exit();

    } else {

        throw new Exception("No se encontró la cita o ya estaba cancelada.");

    }

} catch (Throwable $e) {

    http_response_code(500);

    echo "Error al cancelar la cita: " .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');

}

?>
