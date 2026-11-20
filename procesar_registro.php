<?php
if (isset($_POST['registrar'])) {
    $nuevoUsuario = [
        "id" => time(), // Un ID único basado en el tiempo
        "usuario" => $_POST['id_us'],
        "email" => $_POST['id_email'],
        "password" => password_hash($_POST['id_pass'], PASSWORD_BCRYPT)
    ];

    // 1. Leer el archivo JSON actual
    $jsonData = file_get_contents('datos.json');
    $data = json_decode($jsonData, true);

    // 2. Agregar el nuevo usuario al arreglo
    $data['usuarios'][] = $nuevoUsuario;

    // 3. Guardar de nuevo el archivo JSON formateado
    file_put_contents('datos.json', json_encode($data, JSON_PRETTY_PRINT));

    // Redirigir al login
    header("Location: login.html");
    exit();
}
?>