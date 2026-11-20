<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Controllers\ForoController;

$controller = new ForoController();
$categoriaParam = $_GET['categoria'] ?? 'Todas las discusiones';
$data = $controller->cargarForo($categoriaParam);

$e = fn($val) => htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
$categorias = ['Todas las discusiones', 'Diagnósticos', 'Consejos', 'Dudas y Aclaraciones'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/foro.css">
    <title>Foro</title>
</head>
<body>

    <header class="site-header">
        <nav class="navbar">
            <ul>
                <li><a href="../index.php">Página principal</a></li>
                <li><a href="perfilUs.php">Mi perfil</a></li>
                <li><a href="Especialistas.php">Especialistas</a></li>
                <li><a href="citas.html">Agendar citas</a></li>
                <li><a href="calendario.html">Calendario de citas</a></li>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <aside>
            <h3>Categorías</h3>
            <ul>
                <?php foreach ($categorias as $cat): ?>
                    <li>
                        <a href="foro.php?categoria=<?= urlencode($cat) ?>"
                           class="<?= $data['categoriaActiva'] === $cat ? 'active' : '' ?>">
                            <?= $e($cat) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <main>
            <div class="actions-bar">
                <h2><?= $e($data['categoriaActiva']) ?></h2>
                <?php if ($data['puedeCrearTema']): ?>
                    <button type="button" class="btn-new-topic" onclick="document.getElementById('form-nuevo-tema').style.display = document.getElementById('form-nuevo-tema').style.display === 'none' ? 'block' : 'none'">
                        + Crear Nuevo Tema
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($data['puedeCrearTema']): ?>
                <form id="form-nuevo-tema" action="CrearTema.php" method="POST" style="display: none; margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 8px;">
                    <h3>Publicar nuevo tema</h3>
                    <div>
                        <label for="titulo">Título:</label><br>
                        <input type="text" id="titulo" name="titulo" required style="width: 100%; margin-bottom: 10px;">
                    </div>
                    <div>
                        <label for="categoria">Categoría:</label><br>
                        <select id="categoria" name="categoria" style="margin-bottom: 10px;">
                            <option value="Diagnósticos">Diagnósticos</option>
                            <option value="Consejos">Consejos</option>
                            <option value="Dudas y Aclaraciones">Dudas y Aclaraciones</option>
                        </select>
                    </div>
                    <div>
                        <label for="contenido">Contenido:</label><br>
                        <textarea id="contenido" name="contenido" rows="4" required style="width: 100%; margin-bottom: 10px;"></textarea>
                    </div>
                    <button type="submit">Publicar tema</button>
                </form>
            <?php endif; ?>

            <div class="topic-list">
                <?php if (empty($data['temas'])): ?>
                    <p>No hay temas publicados en esta categoría todavía.</p>
                <?php else: ?>
                    <?php foreach ($data['temas'] as $tema): ?>
                        <div class="topic-item">
                            <div class="topic-info">
                                <h4>
                                    <span class="badge badge-dev"><?= $e($tema['categoria']) ?></span>
                                    <a href="tema.php?id=<?= urlencode($tema['id']) ?>"><?= $e($tema['titulo']) ?></a>
                                </h4>
                                <div class="topic-meta">
                                    Creado por <strong><?= $e($tema['autor_usuario']) ?></strong>
                                </div>
                            </div>
                            <div class="topic-stats">
                                <div>
                                    <span><?= (int) $tema['respuestas_count'] ?></span> Respuestas
                                </div>
                                <div>
                                    <span><?= (int) $tema['vistas'] ?></span> Vistas
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>