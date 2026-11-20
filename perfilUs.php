<?php
session_start();

// 1. Verificar si ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

$usuarioActual = $_SESSION['usuario'];
$archivoJson = 'datos.json';

// Leer datos del JSON
if (file_exists($archivoJson)) {
    $jsonData = file_get_contents($archivoJson);
    $data = json_decode($jsonData, true);
} else {
    $data = ["usuarios" => [], "citas" => []];
}

// Buscar al usuario actual en el JSON
$datosUsuario = null;
$indiceUsuario = -1;

foreach ($data['usuarios'] as $index => $user) {
    if ($user['usuario'] === $usuarioActual) {
        $datosUsuario = $user;
        $indiceUsuario = $index;
        break;
    }
}

if (!$datosUsuario) {
    session_destroy();
    header("Location: login.html");
    exit();
}

// 2. Procesar acciones de modificación o eliminación en el mismo archivo (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validar acción de modificar
    if (isset($_POST['modificar'])) {
        // Actualizar datos del usuario actual
        $data['usuarios'][$indiceUsuario]['nombre'] = $_POST['fname'] ?? $datosUsuario['nombre'];
        $data['usuarios'][$indiceUsuario]['apellido_paterno'] = $_POST['id_apP'] ?? $datosUsuario['apellido_paterno'];
        $data['usuarios'][$indiceUsuario]['apellido_materno'] = $_POST['id_apM'] ?? $datosUsuario['apellido_materno'];
        $data['usuarios'][$indiceUsuario]['email'] = $_POST['id_email'] ?? $datosUsuario['email'];
        
        // Si ingresó una nueva contraseña, actualizarla cifrada
        if (!empty($_POST['id_pass'])) {
            $data['usuarios'][$indiceUsuario]['password'] = password_hash($_POST['id_pass'], PASSWORD_BCRYPT);
        }

        // Guardar cambios en el JSON
        file_put_contents($archivoJson, json_encode($data, JSON_PRETTY_PRINT));
        $mensajeExito = "¡Datos modificados correctamente!";
        
        // Refrescar los datos actuales
        $datosUsuario = $data['usuarios'][$indiceUsuario];
    }

    // Validar acción de eliminar cuenta
    if (isset($_POST['eliminar'])) {
        // Eliminar del arreglo
        array_splice($data['usuarios'], $indiceUsuario, 1);
        file_put_contents($archivoJson, json_encode($data, JSON_PRETTY_PRINT));
        
        // Destruir sesión y sacar al usuario
        session_destroy();
        header("Location: login.html");
        exit();
    }
}

// Definir si es especialista (rol 1) u operativo (rol 0)
// Ajusta este valor según cómo manejes tus roles en el registro
$esEspecialista = (isset($datosUsuario['rol']) && $datosUsuario['rol'] == '1');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="icon" type="image/png" href="img/logo.png">
    <title>Mi Perfil - Brote Psique</title>
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
                    <h1>Perfil de <em><?php echo htmlspecialchars($datosUsuario['usuario']); ?></em></h1>
                    <p><?php echo $esEspecialista ? "Panel de Especialista Verificado" : "Panel de Usuario Operativo"; ?></p>
                </div>

                <?php if (isset($mensajeExito)): ?>
                    <p style="color: green; text-align: center; font-weight: bold;"><?php echo $mensajeExito; ?></p>
                <?php endif; ?>

                <!-- Vista y Formulario de Gestión Integrado en el mismo PHP -->
                <div class="profile-image">
                    <img src="<?php echo htmlspecialchars($datosUsuario['avatar'] ?? 'foto_perfil/default.png'); ?>" alt="Foto de perfil" width="150" height="150" style="border-radius: 50%; object-fit: cover;">
                </div>

                <form action="perfil.php" method="POST" autocomplete="off">
                    <fieldset>
                        <legend>Información personal</legend>
                        <div class="field">
                            <label for="fname">Nombre(s):</label>
                            <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($datosUsuario['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label for="id_apP">Apellido paterno:</label>
                            <input type="text" id="id_apP" name="id_apP" value="<?php echo htmlspecialchars($datosUsuario['apellido_paterno'] ?? ''); ?>">
                        </div>
                        <div class="field">
                            <label for="id_apM">Apellido materno:</label>
                            <input type="text" id="id_apM" name="id_apM" value="<?php echo htmlspecialchars($datosUsuario['apellido_materno'] ?? ''); ?>">
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Información de acceso</legend>
                        <div class="field">
                            <label for="id_email">Correo electrónico:</label>
                            <input type="email" id="id_email" name="id_email" value="<?php echo htmlspecialchars($datosUsuario['email'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label for="id_pass">Nueva contraseña (opcional):</label>
                            <input type="password" id="id_pass" name="id_pass" placeholder="Dejar en blanco para mantener la actual">
                        </div>
                    </fieldset>

                    <!-- Secciones personalizadas condicionales según el rol -->
                    <?php if ($esEspecialista): ?>
                        <fieldset>
                            <legend>Detalles Profesionales (Especialista)</legend>
                            <p><strong>Especialidad:</strong> Psicología Clínica / Terapia Cognitivo-Conductual</p>
                            <p><strong>Modalidad:</strong> Presencial y En línea</p>
                        </fieldset>
                    <?php endif; ?>

                    <fieldset>
                        <legend>Acciones de la cuenta</legend>
                        <div class="button-group" style="display: flex; gap: 10px; justify-content: center;">
                            <button type="submit" name="modificar" value="1" style="background-color: #3f6b5e; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">Guardar Cambios</button>
                            <button type="submit" name="eliminar" value="1" onclick="return confirm('¿Estás seguro de eliminar tu cuenta?');" style="background-color: #a85858; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">Eliminar Cuenta</button>
                        </div>
                    </fieldset>
                </form>

            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2026 Brote psique. Todos los derechos reservados.</p>
    </footer>

</body>
</html>