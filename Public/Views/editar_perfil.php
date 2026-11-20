<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\UsuarioController;

$controller = new UsuarioController();
$doc = $controller->obtenerUsuarioActual();

if (!$doc) {
    header("Location: login.html");
    exit();
}

$e = fn($val) => htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');

$avatarRaw = (string) ($doc['avatar'] ?? 'img/logo2.png');
$avatarPreview = (str_starts_with($avatarRaw, 'http') || str_starts_with($avatarRaw, '../'))
    ? $avatarRaw
    : '../' . ltrim($avatarRaw, '/');

$generoActual = (string) ($doc['genero'] ?? '0');
$estadoActual = (string) ($doc['estado'] ?? '0');
$rolActual = (string) ($doc['rol'] ?? '0');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Editar perfil</title>
</head>
<body>
    <main class="container">
        <section class="form signup" aria-labelledby="titulo-editar">
            <header class="form-header">
                <h1 id="titulo-editar">Editar perfil</h1>
                <p>Modifica los datos que desees actualizar</p>
            </header>

            <form action="Editar_Post.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                <fieldset>
                    <legend>Información personal</legend>

                    <div class="field">
                        <label for="fname">Nombre(s):</label>
                        <input type="text" id="fname" name="fname" value="<?= $e($doc['nombre'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="id_apP">Apellido paterno:</label>
                        <input type="text" id="id_apP" name="id_apP" value="<?= $e($doc['apellido_paterno'] ?? '') ?>">
                    </div>

                    <div class="field">
                        <label for="id_apM">Apellido materno:</label>
                        <input type="text" id="id_apM" name="id_apM" value="<?= $e($doc['apellido_materno'] ?? '') ?>">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Información de acceso</legend>

                    <div class="field">
                        <label for="id_us">Nombre de usuario:</label>
                        <input type="text" id="id_us" name="id_us" value="<?= $e($doc['usuario'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="id_email">Correo electrónico:</label>
                        <input type="email" id="id_email" name="id_email" value="<?= $e($doc['correo'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="id_pass">Nueva contraseña (opcional):</label>
                        <input type="password" id="id_pass" name="id_pass" placeholder="Déjalo vacío para mantener la actual">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Foto de perfil</legend>
                    <div class="file-upload-container">
                        <label for="avatar">Cambiar imagen de perfil (opcional):</label>
                        <input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg, image/jpg, image/gif" onchange="previewImage(event)">
                        <figure class="avatar-preview">
                            <img id="avatar-preview" src="<?= $e($avatarPreview) ?>" alt="Vista previa de la imagen de perfil">
                            <figcaption>Foto actual / vista previa</figcaption>
                        </figure>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Datos adicionales</legend>

                    <div class="field">
                        <label for="id_fech">Fecha de nacimiento:</label>
                        <input type="date" id="id_fech" name="id_fech" value="<?= $e($doc['fecha_nacimiento'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="prefe">Género:</label>
                        <select name="pref" id="prefe">
                            <option value="0" <?= $generoActual === '0' ? 'selected' : '' ?>>Masculino</option>
                            <option value="1" <?= $generoActual === '1' ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="est">Estado:</label>
                        <select name="est" id="est">
                            <option value="0" <?= $estadoActual === '0' ? 'selected' : '' ?>>Público</option>
                            <option value="1" <?= $estadoActual === '1' ? 'selected' : '' ?>>Privado</option>
                        </select>
                    </div>
                </fieldset>

                <?php if ($rolActual === '1'): ?>
                    <fieldset>
                        <legend>Información profesional (Especialista)</legend>

                        <div class="field">
                            <label for="telefono">Teléfono:</label>
                            <input type="text"
                                   id="telefono"
                                   name="telefono"
                                   value="<?= $e($doc['telefono'] ?? '') ?>"
                                   placeholder="+52 81 0000 0000">
                        </div>

                        <div class="field">
                            <label for="especialidad">Especialidad:</label>
                            <input type="text"
                                   id="especialidad"
                                   name="especialidad"
                                   value="<?= $e($doc['especialidad'] ?? '') ?>"
                                   placeholder="Psicología Clínica / Terapia Cognitivo-Conductual">
                        </div>

                        <div class="field">
                            <label for="horarios">Horarios:</label>
                            <input type="text"
                                   id="horarios"
                                   name="horarios"
                                   value="<?= $e($doc['horarios'] ?? '') ?>"
                                   placeholder="Lunes a Viernes: 9:00 AM - 6:00 PM">
                        </div>

                        <div class="field">
                            <label for="modalidad">Modalidad:</label>
                            <select name="modalidad" id="modalidad">
                                <?php $modalidadActual = (string) ($doc['modalidad'] ?? 'Presencial y En línea'); ?>
                                <option value="Presencial" <?= $modalidadActual === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                                <option value="En línea" <?= $modalidadActual === 'En línea' ? 'selected' : '' ?>>En línea</option>
                                <option value="Presencial y En línea" <?= $modalidadActual === 'Presencial y En línea' ? 'selected' : '' ?>>Presencial y En línea</option>
                            </select>
                        </div>
                    </fieldset>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit">Guardar cambios</button>
                </div>
            </form>

            <footer class="form-footer">
                <p><a href="perfilUs.php">Volver al perfil</a></p>
            </footer>
        </section>
    </main>

    <script src="../js/registro_foto.js"></script>
</body>
</html>