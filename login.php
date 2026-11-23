
<?php
session_start();

require_once __DIR__ . '/Data/Conexion.php';

if (isset($_POST['ok'])) {

    $emailIngresado = trim($_POST['id_email']);
    $passwordIngresada = $_POST['id_pass'];

    // Conectar con MongoDB
    $db = Conexion::conectar();

    // Buscar el documento que contiene el arreglo usuarios
    $documento = $db->usuarios->findOne([
        'usuarios.email' => $emailIngresado
    ]);

    $usuarioEncontrado = null;

    // Recorrer el arreglo de usuarios
    if ($documento && isset($documento['usuarios'])) {

        foreach ($documento['usuarios'] as $user) {

            if ($user['email'] === $emailIngresado) {
                $usuarioEncontrado = $user;
                break;
            }
        }
    }

    // Comprobar contraseña
    if (
        $usuarioEncontrado &&
        password_verify(
            $passwordIngresada,
            $usuarioEncontrado['password']
        )
    ) {

        // Regenerar el identificador de sesión
        session_regenerate_id(true);

        // Guardar información del usuario
        $_SESSION['id_usuario'] = $usuarioEncontrado['id'];
        $_SESSION['usuario'] = $usuarioEncontrado['usuario'];
        $_SESSION['email'] = $usuarioEncontrado['email'];
        $_SESSION['rol'] = $usuarioEncontrado['rol'];

        // Ir a la página principal
        header("Location: index.php");
        exit();

    } else {

        echo "Correo o contraseña incorrectos.";

    }
}
?>
