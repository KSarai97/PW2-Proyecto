
<?php

session_start();

require_once __DIR__ . '/Data/Conexion.php';

// Comprobar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: calendario.php");
    exit();
}

try {

    $db = Conexion::conectar();

    // =====================================
    // 1. IDENTIFICAR AL USUARIO LOGUEADO
    // =====================================

    $nombreUsuario = $_SESSION['usuario'];

    $documentoUsuario = $db->usuarios->findOne([
        'usuarios.usuario' => $nombreUsuario
    ]);

    if (!$documentoUsuario) {
        throw new Exception("Usuario no encontrado.");
    }

    $usuarioEncontrado = null;

    foreach ($documentoUsuario['usuarios'] as $usuario) {
        if ($usuario['usuario'] === $nombreUsuario) {
            $usuarioEncontrado = $usuario;
            break;
        }
    }

    if (!$usuarioEncontrado) {
        throw new Exception("No se encontraron los datos del usuario.");
    }

    $idUsuario = $usuarioEncontrado['id'];

    // =====================================
    // 2. OBTENER ID DE LA CITA
    // =====================================

    $idCita = $_POST['id_cita'] ?? '';

    if (!preg_match('/^[a-fA-F0-9]{24}$/', $idCita)) {
        throw new Exception("El identificador de la cita no es válido.");
    }

    $objectId = new MongoDB\BSON\ObjectId($idCita);

    // =====================================
    // 3. CANCELAR SOLAMENTE SU PROPIA CITA
    // =====================================

    $resultado = $db->citas->updateOne(
        [
            '_id' => $objectId,
            'id_usuario' => $idUsuario,
            'estado' => ['$ne' => 'cancelada']
        ],
        [
            '$set' => [
                'estado' => 'cancelada'
            ]
        ]
    );

    if ($resultado->getModifiedCount() === 1) {

        header("Location: calendario.php?cancelacion=exitosa");
        exit();

    } else {

        throw new Exception(
            "No puedes cancelar esta cita o ya se encuentra cancelada."
        );

    }

} catch (Throwable $e) {

    http_response_code(403);

    echo "Error al cancelar la cita: " .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');

}

?>
