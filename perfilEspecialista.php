<?php
session_start();

// 1. Verificar si ha iniciado sesión general
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

$usuarioLogueado = $_SESSION['usuario'];
$archivoJson = 'datos.json';

// Leer datos del JSON
if (file_exists($archivoJson)) {
    $jsonData = file_get_contents($archivoJson);
    $data = json_decode($jsonData, true);
} else {
    $data = ["usuarios" => [], "citas" => []];
}

// 2. Determinar a qué usuario se va a consultar (si mandan ?usuario=X en la URL, se usa; si no, se muestra el propio)
$usuarioAconsultar = $_GET['usuario'] ?? $usuarioLogueado;

$datosPerfil = null;
foreach ($data['usuarios'] as $user) {
    if ($user['usuario'] === $usuarioAconsultar) {
        $datosPerfil = $user;
        break;
    }
}

// Si no existe el usuario buscado
if (!$datosPerfil) {
    echo "<h2 style='text-align:center; margin-top:50px;'>El perfil solicitado no existe.</h2>";
    echo "<p style='text-align:center;'><a href='index.html'>Volver a la página principal</a></p>";
    exit();
}

// Saber si es el propio perfil del usuario conectado y si es especialista
$esSuPropioPerfil = ($usuarioAconsultar === $usuarioLogueado);
$esEspecialista = (isset($datosPerfil['rol']) && $datosPerfil['rol'] == '1');

// Calcular edad si tiene fecha de nacimiento
$edad = "No especificada";
if (!empty($datosPerfil['fecha_nacimiento'])) {
    $nacimiento = new DateTime($datosPerfil['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento)->y . " años";
}

$generoTexto = (isset($datosPerfil['genero']) && $datosPerfil['genero'] == '1') ? 'Femenino' : 'Masculino';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="icon" type="image/png" href="img/logo.png">
    <title>Perfil de <?php echo htmlspecialchars($datosPerfil['usuario']); ?></title>
</head>
<body>

    <header class="site-header">
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Página principal</a></li>
                <li><a href="citas.html">Agendar citas</a></li>
                <li><a href="calendario.html">Calendario de citas</a></li>
                <li><a href="foro.html">Foro</a></li>
                <li><a href="login.html" title="Cerrar sesión">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="profile-box">
                
                <div class="profile-header">
                    <h1><?php echo $esEspecialista ? "Perfil de <em>Especialista</em>" : "Perfil de <em>" . htmlspecialchars($datosPerfil['usuario']) . "</em>"; ?></h1>
                    <p><?php echo $esEspecialista ? "Especialista Verificado" : "Usuario Operativo"; ?></p>
                </div>

                <!-- Imagen de perfil respetando el diseño exacto -->
                <div class="profile-image" style="text-align: center; margin-bottom: 20px;">
                    <img src="<?php echo htmlspecialchars($datosPerfil['avatar'] ?? 'foto_perfil/default.png'); ?>" alt="Foto de perfil" width="150" height="150" style="border-radius: 50%; object-fit: cover; border: 3px solid #8bd4b6;">
                </div>

                <!-- Estructura visual de solo lectura con diseño idéntico a perfilUs -->
                <div class="user-info">
                    <h2><?php echo $esEspecialista ? "Información profesional" : "Información del usuario"; ?></h2>
                    <dl>
                        <dt>Nombre completo</dt>
                        <dd><?php echo htmlspecialchars(($datosPerfil['nombre'] ?? '') . ' ' . ($datosPerfil['apellido_paterno'] ?? '') . ' ' . ($datosPerfil['apellido_materno'] ?? '')); ?></dd>

                        <dt>Nombre de usuario</dt>
                        <dd><?php echo htmlspecialchars($datosPerfil['usuario']); ?></dd>

                        <?php if (!$esEspecialista): ?>
                            <dt>Edad</dt>
                            <dd><?php echo $edad; ?></dd>
                        <?php endif; ?>

                        <dt>Correo electrónico</dt>
                        <dd><a href="mailto:<?php echo htmlspecialchars($datosPerfil['email'] ?? ''); ?>"><?php echo htmlspecialchars($datosPerfil['email'] ?? ''); ?></a></dd>

                        <?php if ($esEspecialista): ?>
                            <dt>Especialidad</dt>
                            <dd>Psicología Clínica / Terapia Cognitivo-Conductual</dd>

                            <dt>Modalidad</dt>
                            <dd>Presencial y En línea</dd>

                            <dt>Horarios</dt>
                            <dd>Lunes a Viernes: 9:00 AM - 6:00 PM</dd>
                        <?php else: ?>
                            <dt>Género</dt>
                            <dd><?php echo $generoTexto; ?></dd>
                        <?php endif; ?>

                        <dt>Rol</dt>
                        <dd><?php echo $esEspecialista ? "Especialista" : "Operativo"; ?></dd>
                    </dl>
                </div>

                <!-- BLOQUE CONDICIONAL: Si es su propio perfil, muestra las opciones de gestión. Si es el perfil de un especialista externo, muestra modo lectura o botón para agendar. -->
                <section class="profile-actions" style="margin-top: 25px;">
                    <?php if ($esSuPropioPerfil): ?>
                        <h2 style="font-size: 18px; margin-bottom: 10px;">Acciones del perfil</h2>
                        <ul class="button-group" style="list-style: none; display: flex; gap: 10px; justify-content: center; padding: 0;">
                            <li><a href="gestion.html" class="button" style="padding: 10px 20px; background-color: #3f6b5e; color: white; border-radius: 999px; text-decoration: none; font-weight: bold;">Gestión de cuenta</a></li>
                        </ul>
                    <?php else: ?>
                        <p style="text-align: center; color: #4d6366; font-style: italic; margin-bottom: 15px;">Estás viendo la información pública de este especialista.</p>
                        <?php if (!$esEspecialista): ?>
                            <!-- Si un especialista ve un perfil ajeno -->
                            <p style="text-align: center;"><a href="index.html" class="button" style="padding: 10px 20px; background-color: #3f6b5e; color: white; border-radius: 999px; text-decoration: none;">Volver al inicio</a></p>
                        <?php else: ?>
                            <!-- Si un usuario operativo ve el perfil de un especialista, puede mandarlo a agendar cita -->
                            <div style="text-align: center;">
                                <a href="citas.html" class="button" style="padding: 10px 20px; background-color: #3f6b5e; color: white; border-radius: 999px; text-decoration: none; font-weight: bold;">Agendar cita con este especialista</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>

            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>

</body>
</html>