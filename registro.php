<?php
if (isset($_POST['registrar'])) {
    
    // 1. Manejar la subida de la imagen
    $rutaImagen = "foto_perfil/default.png"; // Imagen por defecto por si ocurre un error
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $nombreTemporal = $_FILES['avatar']['tmp_name'];
        $nombreOriginal = $_FILES['avatar']['name'];
        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
        
        // Generar un nombre único para la imagen usando el tiempo actual
        $nombreUnico = "avatar_" . time() . "." . $extension;
        $rutaDestino = "foto_perfil/" . $nombreUnico;
        
        // Mover el archivo desde la ruta temporal de PHP hacia tu carpeta definitiva
        if (move_uploaded_file($nombreTemporal, $rutaDestino)) {
            $rutaImagen = $rutaDestino; // Guardamos la ruta relativa
        }
    }

    // 2. Capturamos todos los datos incluyendo la ruta de la imagen
    $nuevoUsuario = [
        "id" => time(), 
        "nombre" => $_POST['fname'],
        "apellido_paterno" => $_POST['id_apP'] ?? '',
        "apellido_materno" => $_POST['id_apM'] ?? '',
        "usuario" => $_POST['id_us'],
        "email" => $_POST['id_email'],
        "password" => password_hash($_POST['id_pass'], PASSWORD_BCRYPT),
        "fecha_nacimiento" => $_POST['id_fech'] ?? '',
        "genero" => $_POST['pref'] ?? '0',
        "estado" => $_POST['est'] ?? '0',
        "rol" => $_POST['rol'] ?? '0',
        "avatar" => $rutaImagen // <--- Aquí se guarda la ruta en el JSON
    ];

    $archivoJson = 'datos.json';
    
    if (file_exists($archivoJson)) {
        $jsonData = file_get_contents($archivoJson);
        $data = json_decode($jsonData, true);
    } else {
        $data = ["usuarios" => []];
    }

    // Agregar el nuevo usuario con su foto al arreglo
    $data['usuarios'][] = $nuevoUsuario;

    // Guardar en el archivo JSON
    file_put_contents($archivoJson, json_encode($data, JSON_PRETTY_PRINT));

    // Redirigir al login
    header("Location: login.html");
    exit();
}
?>