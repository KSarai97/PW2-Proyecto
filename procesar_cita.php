
<?php

session_start();

require_once __DIR__ . '/Data/Conexion.php';

// =====================================
// 1. COMPROBAR SESIÓN
// =====================================

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // =====================================
        // 2. CONEXIÓN A MONGODB
        // =====================================

        $db = Conexion::conectar();

        $coleccion = $db->citas;

        // =====================================
        // 3. IDENTIFICAR USUARIO LOGUEADO
        // =====================================

        $nombreUsuario = $_SESSION['usuario'];

        $documentoUsuario = $db->usuarios->findOne([
            'usuarios.usuario' => $nombreUsuario
        ]);

        if (!$documentoUsuario) {
            throw new Exception("Usuario no encontrado.");
        }

        $usuarioEncontrado = null;

        foreach ($documentoUsuario['usuarios'] as $usuario) {

            if ($usuario['usuario'] === $nombreUsuario) {
                $usuarioEncontrado = $usuario;
                break;
            }

        }

        if (!$usuarioEncontrado) {
            throw new Exception("No se encontraron los datos del usuario.");
        }

        // Obtener ID del usuario
        $idUsuario = $usuarioEncontrado['id'];

        // =====================================
        // 4. OBTENER DATOS DEL FORMULARIO
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

        // ID de cita si se está modificando
        $idCita = $_POST['id_cita'] ?? '';

        // =====================================
        // 5. VALIDAR DATOS
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
            !in_array(
                $hora,
                ['10:00', '11:00', '12:00', '16:00', '17:00'],
                true
            )
        ) {

            throw new Exception("Los datos de la cita no son válidos.");

        }

        // Validar fecha real
        $fechaValidada = DateTime::createFromFormat('!Y-m-d', $fecha);

        if (
            !$fechaValidada ||
            $fechaValidada->format('Y-m-d') !== $fecha
        ) {

            throw new Exception("La fecha seleccionada no es válida.");

        }

        // No permitir fechas anteriores al día actual
        if ($fecha < date('Y-m-d')) {
            throw new Exception("No puedes seleccionar una fecha pasada.");
        }

        // =====================================
        // 6. PREPARAR DATOS DE LA CITA
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
        // 7. COMPROBAR SI ES MODIFICACIÓN
        // =====================================

        if (!empty($idCita)) {

            // Validar ID de MongoDB
            if (!preg_match('/^[a-fA-F0-9]{24}$/', $idCita)) {

                throw new Exception(
                    "El identificador de la cita no es válido."
                );

            }

            $objectId = new MongoDB\BSON\ObjectId($idCita);

            // =====================================
            // 8. ACTUALIZAR SOLAMENTE SU PROPIA CITA
            // =====================================

            $resultado = $coleccion->updateOne(

                [
                    '_id' => $objectId,
                    'id_usuario' => $idUsuario
                ],

                [
                    '$set' => $datosCita
                ]

            );

            // Comprobar si existe y pertenece al usuario
            if ($resultado->getMatchedCount() === 1) {

                header("Location: calendario.php?modificacion=exitosa");
                exit();

            } else {

                throw new Exception(
                    "No puedes modificar esta cita porque no te pertenece o no existe."
                );

            }

        } else {

            // =====================================
            // 9. REGISTRAR UNA CITA NUEVA
            // =====================================

            // Guardar propietario de la cita
            $datosCita['id_usuario'] = $idUsuario;

            $datosCita['usuario'] = $nombreUsuario;

            // Estado inicial
            $datosCita['estado'] = 'pendiente';

            // Fecha de registro
            $datosCita['fecha_registro'] =
                new MongoDB\BSON\UTCDateTime();

            // =====================================
            // 10. INSERTAR EN MONGODB
            // =====================================

            $resultado = $coleccion->insertOne($datosCita);

            if ($resultado->getInsertedCount() === 1) {

                header("Location: calendario.php?registro=exitoso");
                exit();

            } else {

                throw new Exception("No se pudo guardar la cita.");

            }

        }

    } catch (Throwable $e) {

        http_response_code(400);

        echo "Error al procesar la cita: " .

            htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );

    }

} else {

    header("Location: citas.php");
    exit();

}

?>
