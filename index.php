<?php
session_start();
$esEspecialista = false;

// Si hay una sesión activa, leemos el JSON para comprobar su rol
if (isset($_SESSION['usuario'])) {
    $usuarioLogueado = $_SESSION['usuario'];
    $archivoJson = 'datos.json';
    
    if (file_exists($archivoJson)) {
        $jsonData = file_get_contents($archivoJson);
        $data = json_decode($jsonData, true);
        
        foreach ($data['usuarios'] as $user) {
            if ($user['usuario'] === $usuarioLogueado) {
                // Comprobamos si el rol es '1' (Especialista)
                $esEspecialista = (isset($user['rol']) && $user['rol'] == '1');
                break;
            }
        }
    }
}
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

    <!-- Encabezado de la página -->
    <header class="site-header">
        <nav class="navbar" aria-label="Navegación principal">
            <ul>
                <li>
                    <a href="perfilUs.php" title="Mira tu perfil">Perfil de usuario</a>
                </li>

                <!-- 🔒 CONDICIÓN: Este enlace SOLO se muestra si el usuario logueado es especialista (rol 1) -->
                <?php if ($esEspecialista): ?>
                    <li>
                        <a href="perfilEspecialista.php" title="Perfil de especialista">Perfil especialista</a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="citas.html" title="¿Necesitas agendar una cita con un especialista? Aquí es">Agendar citas</a>
                </li>

                <li>
                    <a href="calendario.html" title="Comprueba las citas registradas">Calendario de citas</a>
                </li>

                <li>
                    <a href="foro.html" title="Mira los artículos creados por nuestros especialistas">Foro</a>
                </li>

                <li>
                    <a href="login.html" title="Cerrar sesión">Cerrar sesión</a>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main>
        <section class="welcome" aria-labelledby="titulo-bienvenida">
            <h1 id="titulo-bienvenida">Bienvenido</h1>
            <img src="img/logo2.png" alt="Logo de Brote Psique" class="welcome-logo">
            <p>
            Brote Psique es una página que busca brindar ayuda a quienes necesitan hablar con alguien
            y ser un apoyo para su crecimiento, bienestar, acompañamiento y desarrollo personal. 
            En este sitio web podrás concertar citas psicológicas con alguno de nuestros especialistas, 
            puedes elegir el más conveniente para ti.
            También puedes ver los artículos que suben los psicólogos y comentar en ellos. 
            Puedes ver en el calendario las citas programadas y el estado de las mismas.
            </p>
        </section>

        <!-- Sección de servicios y tarifas dinámica -->
        <section class="services" aria-labelledby="titulo-servicios">

            <div class="services-header">
                <h2 id="titulo-servicios">
                    Nuestros <em>servicios</em>
                </h2>

                <p>
                    Conoce a nuestros especialistas y consulta las opciones
                    disponibles para tu atención psicológica.
                </p>
            </div>

            <div class="services-container">

                <?php
                // Leer el archivo JSON para extraer los especialistas registrados
                $archivoJson = 'datos.json';
                $especialistasEncontrados = false;

                if (file_exists($archivoJson)) {
                    $jsonData = file_get_contents($archivoJson);
                    $data = json_decode($jsonData, true);

                    if (isset($data['usuarios'])) {
                        foreach ($data['usuarios'] as $user) {
                            // Verificamos si el usuario tiene rol de especialista (1)
                            if (isset($user['rol']) && $user['rol'] == '1') {
                                $especialistasEncontrados = true;
                                
                                // Obtenemos los datos del especialista (nombre, usuario, avatar, etc.)
                                $nombreCompleto = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido_paterno'] ?? ''));
                                if (empty($nombreCompleto)) {
                                    $nombreCompleto = $user['usuario'];
                                }
                                $avatar = !empty($user['avatar']) ? $user['avatar'] : 'foto_perfil/default.png';
                                $nombreUsuarioUrl = urlencode($user['usuario']);
                                ?>

                                <!-- Tarjeta dinámica de especialista -->
                                <article class="service-card">

                                    <div class="service-icon">
                                        ♡
                                    </div>

                                    <!-- Avatar o imagen del especialista -->
                                    <div style="text-align: center; margin-bottom: 10px;">
                                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Foto de <?php echo htmlspecialchars($nombreCompleto); ?>" width="90" height="90" style="border-radius: 50%; object-fit: cover; border: 2px solid #8bd4b6;">
                                    </div>

                                    <h3><?php echo htmlspecialchars($nombreCompleto); ?></h3>

                                    <p class="specialty">
                                        Psicología Clínica / Especialista Verificado
                                    </p>

                                    <div class="service-details">
                                        <div class="detail">
                                            <span>Modalidad</span>
                                            <strong>Presencial y En línea</strong>
                                        </div>

                                        <div class="detail">
                                            <span>Duración</span>
                                            <strong>50 minutos</strong>
                                        </div>
                                    </div>

                                    <div class="service-price">
                                        <small>Sesión desde</small>
                                        <strong>$500 MXN</strong>
                                    </div>

                                    <!-- Botones de acción: Agendar o Ver su perfil dinámico -->
                                    <div style="display: flex; gap: 8px; margin-top: 15px;">
                                        <a href="citas.html" class="service-button" style="flex: 1; text-align: center; padding: 8px; font-size: 14px;">
                                            Agendar cita
                                        </a>
                                        <a href="ver_perfil.php?usuario=<?php echo $nombreUsuarioUrl; ?>" class="service-button" style="flex: 1; text-align: center; padding: 8px; font-size: 14px; background-color: #4d6366;">
                                            Ver perfil
                                        </a>
                                    </div>

                                </article>

                                <?php
                            }
                        }
                    }
                }

                // Si no hay ningún especialista registrado todavía en el JSON
                if (!$especialistasEncontrados) {
                    echo '<p style="text-align: center; width: 100%; color: #4d6366; font-style: italic;">Próximamente tendremos especialistas disponibles en el sistema.</p>';
                }
                ?>

            </div>

            <div class="services-footer">
                <p>
                    Elige al especialista que mejor se adapte a tus necesidades.
                </p>
            </div>

        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>

</body>
</html>