
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

// =====================================
// 2. ESPECIALISTAS DISPONIBLES
// =====================================

$especialistas = [
    1 => 'Dra. María Fernández - Psicología Clínica',
    2 => 'Lic. Roberto Sánchez - Terapia Cognitivo-Conductual'
];

$citaEditar = null;

// =====================================
// 3. COMPROBAR SI SE QUIERE EDITAR
// =====================================

if (isset($_GET['id'])) {

    try {

        $id = $_GET['id'];

        if (!preg_match('/^[a-fA-F0-9]{24}$/', $id)) {
            throw new Exception('ID de cita no válido.');
        }

        $db = Conexion::conectar();

        $nombreUsuario = $_SESSION['usuario'];

        $documentoUsuario = $db->usuarios->findOne([
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

        $citaEditar = $db->citas->findOne([
            '_id' => new MongoDB\BSON\ObjectId($id),
            'id_usuario' => $idUsuario
        ]);

        if (!$citaEditar) {
            throw new Exception(
                'No se encontró la cita o no tienes permiso para modificarla.'
            );
        }

    } catch (Throwable $e) {

        http_response_code(403);

        exit(
            htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            )
        );

    }

}

// =====================================
// 4. OBTENER VALORES PARA EL FORMULARIO
// =====================================

$fechaSeleccionada = $citaEditar
    ? $citaEditar['fecha']
    : '';

$horaSeleccionada = $citaEditar
    ? $citaEditar['hora']
    : '';

$horarios = [
    '10:00' => '10:00 AM',
    '11:00' => '11:00 AM',
    '12:00' => '12:00 PM',
    '16:00' => '04:00 PM',
    '17:00' => '05:00 PM'
];

$meses = [
    'enero', 'febrero', 'marzo', 'abril',
    'mayo', 'junio', 'julio', 'agosto',
    'septiembre', 'octubre', 'noviembre', 'diciembre'
];

$fechaTexto = '';

if ($fechaSeleccionada !== '') {

    $fechaObjeto = DateTime::createFromFormat('!Y-m-d', $fechaSeleccionada);

    if ($fechaObjeto) {

        $dias = [
            'domingo', 'lunes', 'martes',
            'miércoles', 'jueves', 'viernes', 'sábado'
        ];

        $fechaTexto = 'Día elegido: ' .
            $dias[(int)$fechaObjeto->format('w')] . ' ' .
            $fechaObjeto->format('j') . ' de ' .
            $meses[(int)$fechaObjeto->format('n') - 1] .
            ' de ' . $fechaObjeto->format('Y');

    }

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/index.css">

    <link rel="stylesheet" href="css/calendario.css">

    <link rel="stylesheet" href="css/citas.css">

    <link rel="icon" type="image/png" href="img/logo.png">

    <title><?= $citaEditar ? 'Modificar cita' : 'Agendar cita' ?></title>

</head>

<body>

    <header class="site-header">

        <nav class="navbar">

            <ul>

                <li><a href="index.php">Página principal</a></li>

                <li><a href="perfilUs.php">Perfil de usuario</a></li>

                <li><a href="perfilEspecialista.php">Perfil especialista</a></li>

                <li><a href="calendario.php">Calendario de citas</a></li>

                <li><a href="foro.html">Foro</a></li>

                <li><a href="logout.php" title="Cerrar sesión">Cerrar sesión</a></li>

            </ul>

        </nav>

    </header>

    <main class="main-content">

        <div class="container">

            <div class="form-header">

                <h1>
                    <?= $citaEditar ? 'Modificar cita' : 'Agendar cita' ?>
                </h1>

                <p>
                    Elige a tu especialista y el día y la hora que mejor te acomoden.
                </p>

            </div>

            <form
                action="procesar_cita.php"
                method="POST"
                id="form-cita"
            >

                <!-- ESPECIALISTA -->

                <fieldset>

                    <legend>Tu especialista</legend>

                    <div class="field">

                        <label for="especialista">Especialista</label>

                        <select
                            id="especialista"
                            name="id_especialista"
                            required
                        >

                            <option value="" disabled
                                <?= !$citaEditar ? 'selected' : '' ?>>
                                Elige un profesional
                            </option>

                            <?php foreach ($especialistas as $id => $nombre): ?>

                                <option
                                    value="<?= $id ?>"
                                    <?= $citaEditar &&
                                        $citaEditar['id_especialista'] == $id
                                        ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars(
                                        $nombre,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="field">

                        <label for="modalidad">Modalidad</label>

                        <select
                            id="modalidad"
                            name="modalidad"
                            required
                        >

                            <option value="presencial"
                                <?= $citaEditar &&
                                    $citaEditar['modalidad'] === 'presencial'
                                    ? 'selected' : '' ?>>

                                Presencial (Consultorio)

                            </option>

                            <option value="online"
                                <?= $citaEditar &&
                                    $citaEditar['modalidad'] === 'online'
                                    ? 'selected' : '' ?>>

                                En línea (Videollamada)

                            </option>

                        </select>

                    </div>

                </fieldset>

                <!-- FECHA Y HORARIO -->

                <fieldset>

                    <legend>Fecha y horario</legend>

                    <div class="picker">

                        <div class="month-navigation">

                            <button
                                type="button"
                                class="nav-btn"
                                id="mes-anterior"
                                aria-label="Mes anterior"
                            >
                                &lt;
                            </button>

                            <h2 id="mes-titulo" aria-live="polite"></h2>

                            <button
                                type="button"
                                class="nav-btn"
                                id="mes-siguiente"
                                aria-label="Mes siguiente"
                            >
                                &gt;
                            </button>

                        </div>

                        <div class="calendar-grid" id="calendario-grid">

                            <div class="day-label">Dom</div>
                            <div class="day-label">Lun</div>
                            <div class="day-label">Mar</div>
                            <div class="day-label">Mié</div>
                            <div class="day-label">Jue</div>
                            <div class="day-label">Vie</div>
                            <div class="day-label">Sáb</div>

                        </div>

                        <input
                            type="hidden"
                            id="fecha"
                            name="fecha"
                            value="<?= htmlspecialchars(
                                $fechaSeleccionada,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                        <p
                            class="picked-date"
                            id="fecha-elegida"
                            aria-live="polite"
                        >
                            <?= $fechaTexto !== ''
                                ? htmlspecialchars($fechaTexto, ENT_QUOTES, 'UTF-8')
                                : 'Aún no eliges un día.' ?>
                        </p>

                    </div>

                    <p class="group-label" id="horas-label">
                        Horario disponible
                    </p>

                    <div
                        class="time-slots"
                        role="group"
                        aria-labelledby="horas-label"
                    >

                        <?php foreach ($horarios as $hora => $etiqueta): ?>

                            <label class="time-option">

                                <input
                                    type="radio"
                                    name="hora"
                                    value="<?= $hora ?>"
                                    <?= $horaSeleccionada === $hora
                                        ? 'checked' : '' ?>
                                    required
                                >

                                <span><?= $etiqueta ?></span>

                            </label>

                        <?php endforeach; ?>

                    </div>

                </fieldset>

                <!-- MOTIVO -->

                <fieldset>

                    <legend>Motivo o notas (opcional)</legend>

                    <div class="field">

                        <label for="motivo">
                            Lo que quieras compartir
                        </label>

                        <textarea
                            id="motivo"
                            name="motivo"
                            rows="3"
                            placeholder="Cuéntanos lo que quieras. Si prefieres, déjalo en blanco y háblalo en la sesión."
                        ><?= $citaEditar
                            ? htmlspecialchars(
                                $citaEditar['motivo'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            )
                            : '' ?></textarea>

                    </div>

                </fieldset>

                <p
                    class="form-error"
                    id="fecha-error"
                    role="alert"
                    hidden
                >
                    Elige un día en el calendario para continuar.
                </p>

                <!-- ACCIONES -->

                <div class="form-actions">

                    <?php if ($citaEditar): ?>

                        <input
                            type="hidden"
                            name="id_cita"
                            value="<?= htmlspecialchars(
                                (string)$citaEditar['_id'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                        <button
                            type="submit"
                            name="actualizar"
                            value="Actualizar"
                        >
                            Guardar cambios
                        </button>

                    <?php else: ?>

                        <button
                            type="submit"
                            name="registrar"
                            value="Registrar"
                        >
                            Confirmar cita
                        </button>

                    <?php endif; ?>

                    <a href="calendario.php" class="button cancel-button">
                        Cancelar
                    </a>

                </div>

            </form>

        </div>

    </main>

    <footer class="site-footer">

        <p>
            &copy; 2026 Brote psique. Todos los derechos reservados.
        </p>

    </footer>

    <script src="js/citas.js"></script>

</body>

</html>
