<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\CitaController;

$controller = new CitaController();
$data = $controller->cargarFormularioCita();
$e = fn($val) => htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/citas.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Agendar Cita</title>
</head>
<body>

    <header class="site-header">
        <nav class="navbar">
            <ul>
                <li><a href="../index.php">Página principal</a></li>
                <li><a href="perfilUs.php">Perfil de usuario</a></li>
                <li><a href="Especialistas.php">Perfil especialista</a></li>
                <li><a href="calendario.php">Calendario de citas</a></li>
                <li><a href="Foro.php">Foro</a></li>
                <li><a href="../logout.php" title="Cerrar sesión">Cerrar sesión</a></li>
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
                            <option value="" disabled <?= $data['especialistaSeleccionado'] === '' ? 'selected' : '' ?>>-- Elige un profesional --</option>
                            <?php foreach ($data['especialistas'] as $esp): ?>
                                <option value="<?= $e($esp['id']) ?>"
                                    <?= $data['especialistaSeleccionado'] === $esp['id'] ? 'selected' : '' ?>>
                                    <?= $e($esp['nombre_completo']) ?> - <?= $e($esp['especialidad']) ?> (<?= $e($esp['horarios']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="modalidad">Modalidad:</label>
                        <select id="modalidad" name="modalidad" required>
                            <option value="Presencial">Presencial (Consultorio)</option>
                            <option value="En línea">En línea (Videollamada)</option>
                        </select>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Fecha y Horario</legend>

                    <div class="field-grid">
                        <div class="field">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" required>
                        </div>

                        <div class="field">
                            <label for="hora">Horario disponible:</label>
                            <select id="hora" name="hora" required>
                                <option value="" disabled selected>-- Selecciona hora --</option>
                                <option value="10:00 AM">10:00 AM</option>
                                <option value="11:00 AM">11:00 AM</option>
                                <option value="12:00 PM">12:00 PM</option>
                                <option value="04:00 PM">04:00 PM</option>
                                <option value="05:00 PM">05:00 PM</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Motivo o Notas (Opcional)</legend>
                    <div class="field">
                        <label for="motivo">Breve motivo de la consulta:</label>
                        <textarea id="motivo" name="motivo" rows="3" placeholder="Describe brevemente tus inquietudes o síntomas..."></textarea>
                    </div>
                </fieldset>

                <div class="form-actions">
                    <button type="submit">Confirmar Cita</button>
                </div>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>

</body>
</html>