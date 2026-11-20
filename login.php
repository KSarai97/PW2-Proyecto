<?php
session_start();

if (isset($_POST['ok'])) {
    $emailIngresado = $_POST['id_email'];
    $passwordIngresada = $_POST['id_pass'];

    // Leer el archivo JSON
    $jsonData = file_get_contents('datos.json');
    $data = json_decode($jsonData, true);

    $usuarioEncontrado = null;

    // Buscar el usuario por correo
    foreach ($data['usuarios'] as $user) {
        if ($user['email'] === $emailIngresado) {
            $usuarioEncontrado = $user;
            break;
        }
    }

    // Verificar si existe y si la contraseña coincide
    if ($usuarioEncontrado && password_verify($passwordIngresada, $usuarioEncontrado['password'])) {
        $_SESSION['usuario'] = $usuarioEncontrado['usuario'];
        header("Location: index.php");
        exit();
    } else {
        echo "Correo o contraseña incorrectos.";
    }
}
?>