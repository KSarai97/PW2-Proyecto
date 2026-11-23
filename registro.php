<?php

require_once __DIR__ . '/Data/Conexion.php';

if (isset($_POST['registrar'])) {

    try {

        // 1. Conectar con MongoDB
        $db = Conexion::conectar();

        // 2. Obtener correo y nombre de usuario
        $email = trim($_POST['id_email']);
        $nombreUsuario = trim($_POST['id_us']);

        // 3. Comprobar si el correo ya existe
        $existente = $db->usuarios->findOne([
            'usuarios.email' => $email
        ]);

        if ($existente) {
            die("Este correo electrónico ya está registrado.");
        }

        // 4. Comprobar si el nombre de usuario ya existe
        $existente = $db->usuarios->findOne([
            'usuarios.usuario' => $nombreUsuario
        ]);

        if ($existente) {
            die("Este nombre de usuario ya está registrado.");
        }

        // 5. Manejar la fotografía
        $rutaImagen = "foto_perfil/default.png";

        if (
            isset($_FILES['avatar']) &&
            $_FILES['avatar']['error'] === UPLOAD_ERR_OK
        ) {

            $temporal = $_FILES['avatar']['tmp_name'];

            // Validar el tipo real de imagen
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipo = $finfo->file($temporal);

            $tiposPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif'
            ];

            if (!isset($tiposPermitidos[$tipo])) {
                die("El archivo debe ser una imagen JPG, PNG o GIF.");
            }

            $extension = $tiposPermitidos[$tipo];

            // Generar nombre único
            $nombreUnico = bin2hex(random_bytes(12)) . '.' . $extension;

            $carpeta = __DIR__ . '/foto_perfil/';

            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0755, true);
            }

            $destino = $carpeta . $nombreUnico;

            if (move_uploaded_file($temporal, $destino)) {
                $rutaImagen = "foto_perfil/" . $nombreUnico;
            } else {
                die("No se pudo guardar la fotografía.");
            }
        }

        // 6. Crear el nuevo usuario
        $nuevoUsuario = [
            "id" => random_int(1000000000, 9999999999),
            "nombre" => trim($_POST['fname']),
            "apellido_paterno" => trim($_POST['id_apP'] ?? ''),
            "apellido_materno" => trim($_POST['id_apM'] ?? ''),
            "usuario" => $nombreUsuario,
            "email" => $email,
            "password" => password_hash(
                $_POST['id_pass'],
                PASSWORD_BCRYPT
            ),
            "fecha_nacimiento" => $_POST['id_fech'] ?? '',
            "genero" => $_POST['pref'] ?? '0',
            "estado" => $_POST['est'] ?? '0',
            "rol" => $_POST['rol'] ?? '0',
            "avatar" => $rutaImagen
        ];

        // 7. Buscar el documento que contiene el arreglo usuarios
        $documento = $db->usuarios->findOne([]);

        if ($documento) {

            // Agregar el nuevo usuario al arreglo existente
            $db->usuarios->updateOne(
                ['_id' => $documento['_id']],
                [
                    '$push' => [
                        'usuarios' => $nuevoUsuario
                    ]
                ]
            );

        } else {

            // Si no existe ningún documento, crear el arreglo
            $db->usuarios->insertOne([
                'usuarios' => [$nuevoUsuario]
            ]);

        }

        // 8. Redirigir al inicio de sesión
        header("Location: login.html");
        exit();

    } catch (Throwable $e) {

        die("Error al registrar usuario: " . $e->getMessage());

    }
}

?>
