<?php

require_once __DIR__ . '/Data/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // conexion a MongoDB
        $db = Conexion::conectar();

        // Sel las citas
        $coleccion = $db->citas;

        // obtener los datos del formulario
        $idEspecialista = filter_input(
            INPUT_POST,
            'id_especialista',
            FILTER_VALIDATE_INT
        );

        $modalidad = $_POST['modalidad'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $motivo = trim($_POST['motivo'] ?? '');

        // validar  datos
        $especialistas = [
            1 => 'Dra. María Fernández - Psicología Clínica',
            2 => 'Lic. Roberto Sánchez - Terapia Cognitivo-Conductual'
        ];

        if (
            !$idEspecialista ||
            !isset($especialistas[$idEspecialista]) ||
            !in_array($modalidad, ['presencial', 'online'], true) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ||
            !in_array($hora, ['10:00', '11:00', '12:00', '16:00', '17:00'], true)
        ) {
            throw new Exception("Los datos de la cita no son válidos.");
        }

        $fechaValidada = DateTime::createFromFormat('!Y-m-d', $fecha);

        if (!$fechaValidada || $fechaValidada->format('Y-m-d') !== $fecha) {
            throw new Exception("La fecha seleccionada no es válida.");
        }

        // crear el documento de la cita
        $nuevaCita = [
            'id_especialista' => $idEspecialista,
            'especialista' => $especialistas[$idEspecialista],
            'modalidad' => $modalidad,
            'fecha' => $fecha,
            'hora' => $hora,
            'motivo' => $motivo,
            'estado' => 'pendiente',
            'fecha_registro' => new MongoDB\BSON\UTCDateTime()
        ];

        // guardar en MongoDB
        $resultado = $coleccion->insertOne($nuevaCita);

        // confirmar el registro
        echo "¡Cita registrada correctamente!";
        echo "<br>ID de la cita: " . $resultado->getInsertedId();

        echo '<br><a href="citas.php">Regresar al formulario</a>';

    } catch (Throwable $e) {

        http_response_code(500);

        echo "Error al registrar la cita: " .
            htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }

} else {
    header("Location: citas.php");
    exit();
}
?>
