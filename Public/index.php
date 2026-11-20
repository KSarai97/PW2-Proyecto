<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';

$usuarioAutenticado = isset($_SESSION['correo']);
define('BASE_URL', 'http://localhost:8000/');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="icon" type="image/png" href="img/logo.png">
    <title>Brote psique - Página principal</title>
</head>
<body>
    <header class="site-header">
        <nav class="navbar" aria-label="Navegación principal">
            <ul>
                <?php if ($usuarioAutenticado): ?>
                    <li><a href="/Views/perfilUs.php" title="Mira tu perfil">Mi perfil</a></li>
                    <?php if (($_SESSION['rol'] ?? '') === '2'): ?>
                        <li><a href="/Views/adminRegistro.php" title="Registrar usuarios y asignar roles">Administrar usuarios</a></li>
                    <?php endif; ?>
                    <li><a href="/Views/Especialistas.php" title="Conoce a nuestros especialistas">Especialistas</a></li>
                    <li><a href="/Views/citas.html" title="Agendar cita">Agendar citas</a></li>
                    <li><a href="/Views/calendario.html" title="Calendario">Calendario de citas</a></li>
                    <li><a href="/Views/Foro.php" title="Foro">Foro</a></li>
                    <li><a href="/Views/logout.php" title="Cerrar sesión">Cerrar sesión</a></li>
                <?php else: ?>
                    <li><a href="Views/login.html" title="Iniciar sesión">Iniciar sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="welcome" aria-labelledby="titulo-bienvenida">
            <h1 id="titulo-bienvenida">Bienvenido</h1>
            <img src="img/logo2.png" alt="Logo de Brote Psique" class="welcome-logo">
            <p>
            Brote Psique es una página que busca brindar ayuda a quienes necesitan hablar con alguien
            y ser un apoyo para su crecimiento, bienestar, acompañamiento y desarrollo personal. 
            En este sitio web podrás concertar citas psicologicas con alguno de nuestros especialistas, 
            puedes elegir el mas conveniente para ti.
            Tambien puedes ver los articulos que suben los psicologos y comentar en ellos. 
            Puedes ver en el calendario las citas programadas y el estado de las mismas.
            </p>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>
</body>
</html>