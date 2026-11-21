
<?php

require_once __DIR__ . '/Data/Conexion.php';

// Especialistas disponibles
$especialistas = [
    1 => 'Dra. María Fernández - Psicología Clínica',
    2 => 'Lic. Roberto Sánchez - Terapia Cognitivo-Conductual'
];

$fechaMinima = date('Y-m-d');

$citaEditar = null;

// Comprobar si se quiere modificar una cita
if (isset($_GET['id'])) {

    try {

        $id = $_GET['id'];

        // Validar identificador de MongoDB
        if (!MongoDB\BSON\ObjectId::isValid($id)) {
            throw new Exception('Identificador de cita no válido.');
        }

        $db = Conexion::conectar();

        $coleccion = $db->citas;

        // Buscar la cita
        $citaEditar = $coleccion->findOne([
            '_id' => new MongoDB\BSON\ObjectId($id)
        ]);

        if (!$citaEditar) {
            throw new Exception('No se encontró la cita.');
        }

    } catch (Throwable $e) {

        http_response_code(404);

        exit(htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        ));

    }

}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/citas.css">
    <link rel="icon" type="image/png" href="img/logo.png">

    <title>Detalles Cita</title>
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
                <li><a href="login.html" title="Cerrar sesión">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">

        <div class="container">

            <div class="form-header">
                <h1>Agendar <em>Cita</em></h1>
                <p>Selecciona a tu especialista y el horario de tu preferencia</p>
            </div>

            <form action="procesar_cita.php" method="POST">

                <fieldset>
                    <legend>Detalles del especialista</legend>

                    <div class="field">
                        <label for="especialista">Seleccionar Especialista:</label>

                        <select id="especialista" name="id_especialista" required>

                           <option value="" disabled
                                    <?= !$citaEditar ? 'selected' : '' ?>>
                                        </option>
                                          <?php foreach ($especialistas as $id => $nombre): ?>
                                         <option value="<?= $id ?>"
                                         <?= $citaEditar &&
                                            $citaEditar['id_especialista'] == $id
                                            ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="field">
                        <label for="modalidad">Modalidad:</label>

                        <select id="modalidad" name="modalidad" required>

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

                <fieldset>
                    <legend>Fecha y Horario</legend>
                    <div class="field-grid">
                        <div class="field">
                            <label for="fecha">Fecha:</label>
                           <input
                                type="date"
                                id="fecha"
                                name="fecha"
                                min="<?= $fechaMinima ?>"
                                value="<?= $citaEditar
                                    ? htmlspecialchars($citaEditar['fecha'], ENT_QUOTES, 'UTF-8')
                                    : '' ?>"
                                required
                            >

                        </div>

                        <div class="field">
                        <label for="hora">Horario disponible:</label>
                           <select id="hora" name="hora" required>

                        <option value="" disabled
                            <?= !$citaEditar ? 'selected' : '' ?>>
                            -- Selecciona hora --
                        </option>

               <?php foreach (['10:00', '11:00', '12:00', '16:00', '17:00'] as $hora): ?>

                    <option value="<?= $hora ?>"
                        <?= $citaEditar &&
                            $citaEditar['hora'] === $hora
                            ? 'selected' : '' ?>>

                        <?= $hora ?>

                    </option>

    <?php endforeach; ?>

</select>

                        </div>

                    </div>

                </fieldset>

                <fieldset>

                    <legend>Motivo o Notas (Opcional)</legend>

                    <div class="field">

                        <label for="motivo">
                            Breve motivo de la consulta:
                        </label>

                        <textarea
                            id="motivo"
                            name="motivo"
                            rows="3"
                            placeholder="Describe brevemente tus inquietudes o síntomas..."
                        ><?= $citaEditar
                            ? htmlspecialchars($citaEditar['motivo'] ?? '', ENT_QUOTES, 'UTF-8')
                            : '' ?></textarea>

                    </div>

                </fieldset>

                <fieldset>

                <legend>Acciones</legend>

            <div class="button-group">

                <?php if ($citaEditar): ?>

                    <input
                        type="hidden"
                        name="id_cita"
                        value="<?= htmlspecialchars(
                            (string) $citaEditar['_id'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <button type="submit" name="actualizar" value="Actualizar">
                        Guardar cambios
                    </button>

                <?php else: ?>

                    <button type="submit" name="registrar" value="Registrar">
                        Agendar cita
                    </button>

                <?php endif; ?>

                <a href="calendario.php" class="button cancel-button">
                    Cancelar
                </a>

            </div>

                    </fieldset>

            </form>

        </div>

    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>

</body>
</html>
