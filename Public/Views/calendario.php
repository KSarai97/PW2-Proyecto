<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\CitaController;

$controller = new CitaController();
$data = $controller->cargarCalendario($_GET['mes'] ?? null);
$e = fn($val) => htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');

$diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/calendario.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Calendario de Citas</title>
</head>
<body>

    <header class="site-header">
        <nav class="navbar">
            <ul>
                <li><a href="../index.php">Página principal</a></li>
                <li><a href="perfilUs.php">Perfil de usuario</a></li>
                <li><a href="Especialistas.php">Perfil especialista</a></li>
                <li><a href="citas.php">Agendar citas</a></li>
                <li><a href="Foro.php">Foro</a></li>
                <li><a href="../logout.php" title="Cerrar sesión">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="calendar-container">
            <div class="calendar-header">
                <h1>Calendario de <em>Citas</em></h1>
                <div class="month-navigation">
                    <a class="nav-btn" href="calendario.php?mes=<?= urlencode($data['mesAnterior']) ?>" title="Mes anterior" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">&lt;</a>
                    <h2><?= $e($data['tituloMes']) ?></h2>
                    <a class="nav-btn" href="calendario.php?mes=<?= urlencode($data['mesSiguiente']) ?>" title="Mes siguiente" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">&gt;</a>
                </div>
            </div>

            <div class="calendar-grid">
                <?php foreach ($diasSemana as $label): ?>
                    <div class="day-label"><?= $e($label) ?></div>
                <?php endforeach; ?>

                <?php for ($i = 0; $i < $data['diaSemanaInicio']; $i++): ?>
                    <div class="calendar-day empty"></div>
                <?php endfor; ?>

                <?php for ($dia = 1; $dia <= $data['diasEnMes']; $dia++): ?>
                    <?php
                    $fechaCelda = $data['mesActual'] . '-' . str_pad((string) $dia, 2, '0', STR_PAD_LEFT);
                    $citasDia = $data['citasPorDia'][$dia] ?? [];
                    $tieneEvento = !empty($citasDia);
                    $esHoy = ($fechaCelda === $data['hoyFecha']);
                    $clases = 'calendar-day' . ($tieneEvento ? ' has-event' : '') . ($esHoy ? ' today' : '');
                    ?>
                    <div class="<?= $e($clases) ?>">
                        <span><?= $dia ?></span>
                        <?php foreach ($citasDia as $citaDia): ?>
                            <?php $esPendiente = strtolower($citaDia['estado']) === 'pendiente'; ?>
                            <span class="event-pill <?= $esPendiente ? 'pendiente' : '' ?>">
                                <?= $e($citaDia['hora']) ?> · <?= $e($citaDia['especialista_nombre']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="upcoming-appointments">
                <h3>Citas programadas en <?= $e($data['tituloMes']) ?></h3>

                <?php if (empty($data['listadoCitas'])): ?>
                    <p class="no-citas">
                        No hay citas registradas en este mes. <a href="citas.php">Agendar una cita</a>.
                    </p>
                <?php else: ?>
                    <?php foreach ($data['listadoCitas'] as $cita): ?>
                        <?php
                        $confirmada = strtolower($cita['estado']) === 'confirmada';
                        $claseEstado = $confirmada ? 'status-confirmed' : 'status-pending';
                        ?>
                        <div class="appointment-card">
                            <div class="appointment-date">
                                <span class="day"><?= $e($cita['dia_dos_digitos']) ?></span>
                                <span class="month"><?= $e($cita['mes_corto']) ?></span>
                            </div>
                            <div class="appointment-info">
                                <strong><?= $e($cita['especialista_nombre']) ?></strong>
                                <p><?= $e($cita['especialidad']) ?> — <?= $e($cita['hora']) ?> · Modalidad: <?= $e($cita['modalidad']) ?></p>
                                <p>Disponibilidad del especialista: <?= $e($cita['horarios_especialista']) ?></p>
                            </div>
                            <span class="appointment-status <?= $e($claseEstado) ?>">
                                <?= $e($cita['estado']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>

</body>
</html>