<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\UsuarioController;

$controller = new UsuarioController();
$especialistas = $controller->obtenerEspecialistasVista();

$e = fn($val) => htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/perfilEsp.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Especialistas</title>
</head>
<body>

    <header class="site-header">
        <nav class="navbar">
            <ul>
                <li><a href="../index.php">Página principal</a></li>
                <li><a href="perfilUs.php">Mi perfil</a></li>
                <li><a href="citas.html">Agendar citas</a></li>
                <li><a href="calendario.html">Calendario de citas</a></li>
                <li><a href="foro.php">Foro</a></li>
                <li><a href="logout.php" title="Cerrar sesión">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <?php if (empty($especialistas)): ?>
                <div class="profile-box">
                    <div class="profile-header">
                        <h1>Especialistas</h1>
                    </div>
                    <p>No hay especialistas registrados por el momento.</p>
                </div>
            <?php else: ?>
                <?php foreach ($especialistas as $esp): ?>
                    <div class="profile-box">
                        <div class="profile-header">
                            <h1>Perfil de <em>Especialista</em></h1>
                        </div>

                        <div class="profile-image">
                            <img src="<?= $e($esp['avatar']) ?>" alt="Foto de perfil de <?= $e($esp['nombre_completo']) ?>">
                            <figcaption>Especialista verificado</figcaption>
                        </div>

                        <div class="user-info">
                            <h2>Información profesional</h2>
                            <dl>
                                <dt>Nombre completo</dt>
                                <dd><?= $e($esp['nombre_completo']) ?></dd>

                                <dt>Teléfono</dt>
                                <dd><?= $e($esp['telefono']) ?></dd>

                                <dt>Correo electrónico</dt>
                                <dd><a href="mailto:<?= $e($esp['correo']) ?>"><?= $e($esp['correo']) ?></a></dd>

                                <dt>Especialidad</dt>
                                <dd><?= $e($esp['especialidad']) ?></dd>

                                <dt>Horarios</dt>
                                <dd><?= $e($esp['horarios']) ?></dd>

                                <dt>Modalidad</dt>
                                <dd><?= $e($esp['modalidad']) ?></dd>

                                <dt>Rol</dt>
                                <dd>Especialista</dd>
                            </dl>
                        </div>

                        <nav aria-label="Acciones del perfil">
                            <ul class="button-group">
                                <li>
                                    <a href="citas.html?especialista_id=<?= urlencode($esp['id']) ?>"
                                       class="btn-agendar-cita"
                                       title="Agendar cita con <?= $e($esp['nombre_completo']) ?>">
                                        Agendar cita
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>

</body>
</html>