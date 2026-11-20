<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Solo permitir acceso si ha iniciado sesión y tiene rol de Administrador ('2')
if (empty($_SESSION['correo']) || (string) ($_SESSION['rol'] ?? '') !== '2') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Registro de usuarios - Administrador</title>
</head>
<body>
    <main class="container">
        <section class="form signup" aria-labelledby="titulo-admin-registro">
            <header class="form-header">
                <h1 id="titulo-admin-registro">Registrar usuario</h1>
                <p>Panel de administración: asigna el rol correspondiente</p>
            </header>

            <form action="registro.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="origen" value="admin">

                <fieldset>
                    <legend>Información personal</legend>
                    <div class="field">
                        <label for="fname">Nombre(s):</label>
                        <input type="text" id="fname" name="fname" required>
                    </div>
                    <div class="field">
                        <label for="id_apP">Apellido paterno:</label>
                        <input type="text" id="id_apP" name="id_apP">
                    </div>
                    <div class="field">
                        <label for="id_apM">Apellido materno:</label>
                        <input type="text" id="id_apM" name="id_apM">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Información de acceso</legend>
                    <div class="field">
                        <label for="id_us">Nombre de usuario:</label>
                        <input type="text" id="id_us" name="id_us" required>
                    </div>
                    <div class="field">
                        <label for="id_email">Correo electrónico:</label>
                        <input type="email" id="id_email" name="id_email" required>
                    </div>
                    <div class="field">
                        <label for="id_pass">Contraseña:</label>
                        <input type="password" id="id_pass" name="id_pass" required>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Foto de perfil</legend>
                    <div class="file-upload-container">
                        <label for="avatar">Selecciona una imagen:</label>
                        <input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg, image/jpg, image/gif" onchange="previewImage(event)" required>
                        <figure class="avatar-preview">
                            <img id="avatar-preview" src="../img/logo2.png" alt="Vista previa">
                            <figcaption>Vista previa</figcaption>
                        </figure>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Datos adicionales y rol</legend>
                    <div class="field">
                        <label for="id_fech">Fecha de nacimiento:</label>
                        <input type="date" id="id_fech" name="id_fech" required>
                    </div>

                    <div class="field">
                        <label for="prefe">Género:</label>
                        <select name="pref" id="prefe">
                            <option value="0">Masculino</option>
                            <option value="1">Femenino</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="est">Estado:</label>
                        <select name="est" id="est">
                            <option value="0">Público</option>
                            <option value="1">Privado</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="rol">Rol:</label>
                        <select name="rol" id="rol">
                            <option value="0">Cliente</option>
                            <option value="1">Especialista</option>
                            <option value="2">Administrador</option>
                        </select>
                    </div>
                </fieldset>

                <div class="form-actions">
                    <button type="submit" name="Aceptar">Registrar usuario</button>
                </div>
            </form>

            <footer class="form-footer">
                <p><a href="../index.php">Volver a la página principal</a></p>
            </footer>
        </section>
    </main>

    <script src="../js/registro_foto.js"></script>
</body>
</html>