
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
            throw new Exception(
                "No se encontraron los datos del usuario."
            );
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

            throw new Exception(
                "Los datos de la cita no son válidos."
            );

        }

        // =====================================
        // 6. VALIDAR FECHA REAL
        // =====================================

        $fechaValidada = DateTime::createFromFormat(
            '!Y-m-d',
            $fecha
        );

        if (
            !$fechaValidada ||
            $fechaValidada->format('Y-m-d') !== $fecha
        ) {

            throw new Exception(
                "La fecha seleccionada no es válida."
            );

        }

        // =====================================
        // 7. VALIDAR FECHA Y HORA
        // =====================================

        // Zona horaria de México
        $zonaHoraria = new DateTimeZone(
            'America/Mexico_City'
        );

        // Obtener fecha y hora actuales
        $ahora = new DateTimeImmutable(
            'now',
            $zonaHoraria
        );

        // Obtener fecha actual
        $fechaActual = $ahora->format('Y-m-d');

        // No permitir fechas anteriores al día actual
        if ($fecha < $fechaActual) {

            throw new Exception(
                "No puedes seleccionar una fecha pasada."
            );

        }

        // Crear fecha y hora completas de la cita
        $fechaHoraCita = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $fecha . ' ' . $hora,
            $zonaHoraria
        );

        if (!$fechaHoraCita) {

            throw new Exception(
                "No se pudo validar la fecha y hora."
            );

        }

        // No permitir horarios que ya pasaron
        if ($fechaHoraCita <= $ahora) {

            throw new Exception(
                "No puedes agendar o modificar una cita en un horario que ya pasó."
            );

        }

        // =====================================
        // 8. PREPARAR DATOS DE LA CITA
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
        // 9. COMPROBAR SI ES MODIFICACIÓN
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
            // 10. ACTUALIZAR SOLAMENTE SU PROPIA CITA
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

                header(
                    "Location: calendario.php?modificacion=exitosa"
                );

                exit();

            } else {

                throw new Exception(
                    "No puedes modificar esta cita porque no te pertenece o no existe."
                );

            }

        } else {

            // =====================================
            // 11. REGISTRAR UNA CITA NUEVA
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
            // 12. INSERTAR EN MONGODB
            // =====================================

            $resultado = $coleccion->insertOne($datosCita);

            if ($resultado->getInsertedCount() === 1) {

                header(
                    "Location: calendario.php?registro=exitoso"
                );

                exit();

            } else {

                throw new Exception(
                    "No se pudo guardar la cita."
                );

            }

        }

 } catch (Throwable $e) {

    // Identificar el tipo de error
    $tipoError = 'datos';

    if (
        strpos(
            $e->getMessage(),
            'horario que ya pasó'
        ) !== false
    ) {
        $tipoError = 'horario';
    } elseif (
        strpos(
            $e->getMessage(),
            'fecha'
        ) !== false
    ) {
        $tipoError = 'fecha';
    }

    // Conservar los datos enviados por el usuario
    $_SESSION['datosError'] = [
    'fecha' => $_POST['fecha'] ?? '',
    'hora' => $_POST['hora'] ?? '',
    'id_especialista' => $_POST['id_especialista'] ?? '',
    'modalidad' => $_POST['modalidad'] ?? '',
    'motivo' => $_POST['motivo'] ?? ''
];

    // Conservar el identificador si se modifica una cita
    $idCita = $_POST['id_cita'] ?? '';

    $url = 'citas.php?error=' . urlencode($tipoError);

    if (
        !empty($idCita) &&
        preg_match('/^[a-fA-F0-9]{24}$/', $idCita)
    ) {
        $url .= '&id=' . urlencode($idCita);
    }

    header("Location: $url");
    exit();

}


} else {

    header("Location: citas.php");
    exit();

}

?>
