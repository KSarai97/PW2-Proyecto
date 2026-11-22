<?php
namespace App\Controllers;

use App\Models\ForoModel;

class ForoController {
    private function asegurarSesion(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function puedeCrearTema(): bool {
        $this->asegurarSesion();
        $rol = (string) ($_SESSION['rol'] ?? '');
        // '1' = Especialista, '2' = Administrador
        return in_array($rol, ['1', '2'], true);
    }

    public function cargarForo(?string $categoria): array {
        $this->asegurarSesion();
        if (empty($_SESSION['correo'])) {
            header("Location: login.html");
            exit();
        }

        $modelo = new ForoModel();
        return [
            'temas' => $modelo->obtenerTemas($categoria),
            'categoriaActiva' => $categoria ?: 'Todas las discusiones',
            'puedeCrearTema' => $this->puedeCrearTema(),
            'usuarioActual' => $_SESSION['usuario'] ?? $_SESSION['correo'],
            'rolActual' => (string) ($_SESSION['rol'] ?? '0'),
        ];
    }

    public function guardarTema(): void {
        $this->asegurarSesion();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: foro.php");
            exit();
        }

        if (empty($_SESSION['correo']) || !$this->puedeCrearTema()) {
            echo "<script>alert('Solo los especialistas o administradores pueden crear nuevos temas.'); window.location.href='foro.php';</script>";
            exit();
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'Consejos');
        $contenido = trim($_POST['contenido'] ?? '');

        $categoriasValidas = ['Diagnósticos', 'Consejos', 'Dudas y Aclaraciones'];
        if (!in_array($categoria, $categoriasValidas, true)) {
            $categoria = 'Consejos';
        }

        if ($titulo === '' || $contenido === '') {
            echo "<script>alert('Completa el título y el contenido del tema.'); window.history.back();</script>";
            exit();
        }

        $modelo = new ForoModel();
        $ok = $modelo->crearTema([
            'titulo' => $titulo,
            'categoria' => $categoria,
            'contenido' => $contenido,
            'autor_id' => $_SESSION['id'] ?? '',
            'autor_usuario' => $_SESSION['usuario'] ?? $_SESSION['correo'],
            'autor_rol' => (string) ($_SESSION['rol'] ?? '1'),
        ]);

        if ($ok) {
            header("Location: foro.php?creado=1");
            exit();
        }

        echo "<script>alert('No se pudo publicar el tema.'); window.history.back();</script>";
        exit();
    }

    public function cargarTema(string $id): ?array {
        $this->asegurarSesion();
        if (empty($_SESSION['correo'])) {
            header("Location: login.html");
            exit();
        }

        $modelo = new ForoModel();
        $tema = $modelo->obtenerTemaPorId($id, true);
        if (!$tema) {
            return null;
        }

        return [
            'tema' => $tema,
            'respuestas' => $modelo->obtenerRespuestas($id),
            'puedeEliminarComentario' => $this->puedeEliminarComentario(),
        ];
    }

    public function guardarRespuesta(): void {
        $this->asegurarSesion();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: foro.php");
            exit();
        }

        if (empty($_SESSION['correo'])) {
            header("Location: login.html");
            exit();
        }

        $temaId = trim($_POST['tema_id'] ?? '');
        $comentario = trim($_POST['comentario'] ?? '');

        if ($temaId === '' || $comentario === '') {
            echo "<script>alert('Escribe una respuesta antes de enviar.'); window.history.back();</script>";
            exit();
        }

        $modelo = new ForoModel();
        $ok = $modelo->agregarRespuesta([
            'tema_id' => $temaId,
            'autor_id' => $_SESSION['id'] ?? '',
            'autor_usuario' => $_SESSION['usuario'] ?? $_SESSION['correo'],
            'autor_rol' => (string) ($_SESSION['rol'] ?? '0'),
            'comentario' => $comentario,
        ]);

        if ($ok) {
            header("Location: tema.php?id=" . urlencode($temaId));
            exit();
        }

        echo "<script>alert('No se pudo guardar la respuesta.'); window.history.back();</script>";
        exit();
    }

    public function puedeEliminarComentario(): bool {
        $this->asegurarSesion();
        $rol = (string) ($_SESSION['rol'] ?? '');
        // Solo Administrador ('2'). Si quieres incluir Especialista ('1'), usa ['1', '2'].
        return $rol === '2';
    }

    public function eliminarRespuesta(): void {
        $this->asegurarSesion();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: foro.php");
            exit();
        }

        if (empty($_SESSION['correo']) || !$this->puedeEliminarComentario()) {
            echo "<script>alert('No tienes permisos para eliminar comentarios.'); window.location.href='foro.php';</script>";
            exit();
        }

        $respuestaId = trim($_POST['respuesta_id'] ?? '');
        $temaId = trim($_POST['tema_id'] ?? '');

        if ($respuestaId === '') {
            header("Location: foro.php");
            exit();
        }

        $modelo = new ForoModel();
        $modelo->eliminarRespuesta($respuestaId);

        if ($temaId !== '') {
            header("Location: tema.php?id=" . urlencode($temaId));
            exit();
        }

        header("Location: foro.php");
        exit();
    }
}