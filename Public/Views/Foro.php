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
    <link rel="stylesheet" href="../css/index.css">
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
                    <button type="button" class="btn-new-topic" onclick="toggleNuevoTema()">
                        + Crear Nuevo Tema
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($data['puedeCrearTema']): ?>
                <form id="form-nuevo-tema"
                    action="CrearTema.php"
                    method="POST"
                    class="foro-form-box"
                    style="display: none;">
                    <h3>Publicar nuevo tema</h3>

                    <div class="foro-field">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo" class="foro-input" required>
                    </div>

                    <div class="foro-field">
                        <label for="categoria">Categoría:</label>
                        <select id="categoria" name="categoria" class="foro-select">
                            <option value="Diagnósticos">Diagnósticos</option>
                            <option value="Consejos">Consejos</option>
                            <option value="Dudas y Aclaraciones">Dudas y Aclaraciones</option>
                        </select>
                    </div>

                    <div class="foro-field">
                        <label for="contenido">Contenido:</label>
                        <textarea id="contenido" name="contenido" rows="4" class="foro-textarea" required></textarea>
                    </div>

                    <button type="submit" class="btn-foro-primary">Publicar tema</button>
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

    <script>
        function toggleNuevoTema() {
            const formTema = document.getElementById('form-nuevo-tema');
            const listaTemas = document.getElementById('lista-temas');
            if (!formTema) return;

            const seVaAMostrar = formTema.style.display === 'none' || formTema.style.display === '';
            formTema.style.display = seVaAMostrar ? 'block' : 'none';

            if (listaTemas) {
                listaTemas.style.display = seVaAMostrar ? 'none' : 'block';
            }
        }
    </script>
</body>
</html>