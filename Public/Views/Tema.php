<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\ForoController;

$controller = new ForoController();
$id = $_GET['id'] ?? '';
$detalle = $id !== '' ? $controller->cargarTema($id) : null;

if (!$detalle) {
    header("Location: foro.php");
    exit();
}

$tema = $detalle['tema'];
$respuestas = $detalle['respuestas'];
$e = fn($val) => htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/foro.css">

    <title><?= $e($tema['titulo']) ?> - Foro</title>
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <ul>
                <li><a href="../index.php">Página principal</a></li>
                <li><a href="foro.php">Volver al foro</a></li>
                <li><a href="perfilUs.php">Perfil de usuario</a></li>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <main style="width: 100%;">
            <article class="topic-item" style="flex-direction: column; align-items: flex-start;">
                <span class="badge badge-dev"><?= $e($tema['categoria']) ?></span>
                <h2><?= $e($tema['titulo']) ?></h2>
                <p class="topic-meta">
                    Publicado por <strong><?= $e($tema['autor_usuario']) ?></strong> • <?= (int) $tema['vistas'] ?> vistas
                </p>
                <p><?= nl2br($e($tema['contenido'])) ?></p>
            </article>

            <section style="margin-top: 24px;">
                <h3>Respuestas (<?= count($respuestas) ?>)</h3>

                <?php if (empty($respuestas)): ?>
                    <p>Aún no hay respuestas en este tema.</p>
                <?php else: ?>
                  <?php foreach ($respuestas as $resp): ?>
                    <div class="topic-item" style="flex-direction: column; align-items: flex-start; margin-bottom: 12px;">
                        <strong><?= $e($resp['autor_usuario']) ?></strong>
                        <p><?= nl2br($e($resp['comentario'])) ?></p>

                        <?php if (!empty($detalle['puedeEliminarComentario'])): ?>
                            <form action="EliminarRespuesta.php"
                                method="POST"
                                onsubmit="return confirm('¿Deseas eliminar este comentario?');"
                                style="margin-top: 10px;">
                                <input type="hidden" name="respuesta_id" value="<?= $e($resp['id']) ?>">
                                <input type="hidden" name="tema_id" value="<?= $e($tema['id']) ?>">
                                <button type="submit" class="btn-foro-delete">Eliminar comentario</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
                <form action="ResponderTema.php" method="POST" class="foro-form-box">
                    <input type="hidden" name="tema_id" value="<?= $e($tema['id']) ?>">
                    <div class="foro-field">
                        <label for="comentario">Escribe una respuesta:</label>
                        <textarea id="comentario" name="comentario" rows="3" class="foro-textarea" required></textarea>
                    </div>
                    <button type="submit" class="btn-foro-primary">Responder</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>