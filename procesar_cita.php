
<?php

require_once __DIR__ . '/Data/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // =====================================
        // 1. CONEXIÓN A MONGODB
        // =====================================

        $db = Conexion::conectar();

        // Seleccionar colección de citas
        $coleccion = $db->citas;


        // =====================================
        // 2. OBTENER DATOS DEL FORMULARIO
        // =====================================

        $idEspecialista = filter_input(
            INPUT_POST,
            'id_especialista',
            FILTER_VALIDATE_INT
        );

        $modalidad = $_POST['modalidad'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $motivo = trim($_POST['motivo'] ?? '');

        // Obtener ID si se está modificando una cita
        $idCita = $_POST['id_cita'] ?? '';


        // =====================================
        // 3. VALIDAR DATOS
        // =====================================

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

        // Validar fecha
        $fechaValidada = DateTime::createFromFormat('!Y-m-d', $fecha);

        if (
            !$fechaValidada ||
            $fechaValidada->format('Y-m-d') !== $fecha
        ) {

            throw new Exception("La fecha seleccionada no es válida.");

        }


        // =====================================
        // 4. PREPARAR DATOS DE LA CITA
        // =====================================

        $datosCita = [

            'id_especialista' => $idEspecialista,
            'especialista' => $especialistas[$idEspecialista],
            'modalidad' => $modalidad,
            'fecha' => $fecha,
            'hora' => $hora,
            'motivo' => $motivo

        ];


        // =====================================
        // 5. COMPROBAR SI ES MODIFICACIÓN
        // =====================================

        if (!empty($idCita)) {

            // Validar identificador de MongoDB
            if (!MongoDB\BSON\ObjectId::isValid($idCita)) {

                throw new Exception("El identificador de la cita no es válido.");

            }

            // Convertir ID a ObjectId
            $objectId = new MongoDB\BSON\ObjectId($idCita);


            // =====================================
            // 6. ACTUALIZAR CITA EXISTENTE
            // =====================================

            $resultado = $coleccion->updateOne(

                ['_id' => $objectId],

                ['$set' => $datosCita]

            );


            // Comprobar actualización
            if ($resultado->getMatchedCount() === 1) {

                // Regresar al calendario
                header("Location: calendario.php?modificacion=exitosa");
                exit();

            } else {

                throw new Exception("No se encontró la cita que deseas modificar.");

            }


        } else {


            // =====================================
            // 7. REGISTRAR UNA CITA NUEVA
            // =====================================

            $datosCita['estado'] = 'pendiente';

            $datosCita['fecha_registro'] =
                new MongoDB\BSON\UTCDateTime();


            // Guardar en MongoDB
            $resultado = $coleccion->insertOne($datosCita);


            // Comprobar que se guardó
            if ($resultado->getInsertedCount() === 1) {

                header("Location: calendario.php?registro=exitoso");
                exit();

            } else {

                throw new Exception("No se pudo guardar la cita.");

            }

        }


    } catch (Throwable $e) {

        http_response_code(500);

        echo "Error al procesar la cita: " .
            htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );

    }

} else {

    // Si no viene del formulario, regresar a citas.php
    header("Location: citas.php");
    exit();

}

?>
