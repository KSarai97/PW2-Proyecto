<?php
namespace App\Controllers;

use App\Models\UsuarioModel;

class UsuarioController {
    
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                // 1. Calcular el rol ANTES de armar $datosUsuario
                $esRegistroAdmin = (($_POST['origen'] ?? '') === 'admin')
                    && isset($_SESSION['rol'])
                    && (string) $_SESSION['rol'] === '2';

                $rol = $esRegistroAdmin ? ($_POST['rol'] ?? '0') : '0';
                
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Recoger y limpiar datos del formulario
                $nombre = $_POST['fname'] ?? '';
                $apP = $_POST['id_apP'] ?? '';
                $apM = $_POST['id_apM'] ?? '';
                $usuario = $_POST['id_us'] ?? '';
                $correo = $_POST['id_email'] ?? '';
                $password = password_hash($_POST['id_pass'], PASSWORD_BCRYPT); // Encriptar contraseña
                $fechaNacimiento = $_POST['id_fech'] ?? '';
                $genero = $_POST['pref'] ?? '0';
                $estado = $_POST['est'] ?? '0';
                $rol = '0';

                // Procesar la imagen de perfil (avatar)
                $rutaAvatar = 'img/default.png'; // Valor por defecto
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['avatar']['tmp_name'];
                    $fileName = $_FILES['avatar']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    if (in_array($fileExtension, $allowedExtensions)) {
                        // Carpeta física donde se guardarán las fotos dentro de Public
                        $uploadFileDir = __DIR__ . '/../../Public/foto_perfil/';
                        if (!is_dir($uploadFileDir)) {
                            mkdir($uploadFileDir, 0755, true);
                        }
                        
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $dest_path = $uploadFileDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $rutaAvatar = 'foto_perfil/' . $newFileName;
                        }
                    }
                }

                // Estructura de datos para MongoDB
                $datosUsuario = [
                    'nombre' => $nombre,
                    'apellido_paterno' => $apP,
                    'apellido_materno' => $apM,
                    'usuario' => $usuario,
                    'correo' => $correo,
                    'password' => $password,
                    'avatar' => $rutaAvatar,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'genero' => $genero,
                    'estado' => $estado,
                    'rol' => $rol,
                    'creado_en' => new \MongoDB\BSON\UTCDateTime()
                ];

                // Instanciar el Modelo y guardar
                $modelo = new UsuarioModel();
                $respuesta = $modelo->registrarUsuario($datosUsuario);

                if ($respuesta['exito']) {
                    // Redirigir al login o página de éxito
                    header("Location: login.html?registro=exito");
                    exit();
                } else {
                    // Puedes retornar el error a la vista
                    echo "<script>alert('" . $respuesta['mensaje'] . "'); window.history.back();</script>";
                }
            }
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim($_POST['id_email'] ?? '');
            $password = $_POST['id_pass'] ?? '';

            $modelo = new UsuarioModel();
            $respuesta = $modelo->verificarCredenciales($correo, $password);

            if ($respuesta['exito']) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $user = $respuesta['usuario'];
                // Guardamos 'correo' porque es la variable que valida tu index.php
                $_SESSION['id'] = (string) $user['_id'];
                $_SESSION['correo'] = $user['correo'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['avatar'] = $user['avatar'] ?? 'img/logo2.png';

                // Redirigir al index.php ubicado en Public/
                header("Location: ../index.php");
                exit();
            } else {
                echo "<script>alert('" . $respuesta['mensaje'] . "'); window.history.back();</script>";
                exit();
            }
        }
    }

    public function obtenerDatosPerfil(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['correo'])) {
            return null;
        }

        $modelo = new UsuarioModel();
        $doc = $modelo->obtenerPorCorreo($_SESSION['correo']);

        if (!$doc) {
            return null;
        }

        $nombre = (string) ($doc['nombre'] ?? '');
        $apP = (string) ($doc['apellido_paterno'] ?? '');
        $apM = (string) ($doc['apellido_materno'] ?? '');
        $nombreCompleto = trim($nombre . ' ' . $apP . ' ' . $apM);

        $usuario = (string) ($doc['usuario'] ?? '');
        $correo = (string) ($doc['correo'] ?? '');
        $fechaNacStr = (string) ($doc['fecha_nacimiento'] ?? '');

        // Calcular edad si hay fecha de nacimiento válida
        $edadTexto = 'No especificada';
        $fechaFormateada = $fechaNacStr !== '' ? $fechaNacStr : 'No especificada';
        if ($fechaNacStr !== '') {
            try {
                $nacimiento = new \DateTime($fechaNacStr);
                $hoy = new \DateTime('today');
                $edadTexto = $nacimiento->diff($hoy)->y . ' años';
                $fechaFormateada = $nacimiento->format('d/m/Y');
            } catch (\Exception $e) {
                // Mantiene el texto original si el formato varía
            }
        }

        // Calcular días desde el registro usando creado_en (UTCDateTime)
        $diasRegistrado = 0;
        if (isset($doc['creado_en']) && $doc['creado_en'] instanceof \MongoDB\BSON\UTCDateTime) {
            $fechaCreacion = $doc['creado_en']->toDateTime();
            $hoy = new \DateTime('now');
            $diasRegistrado = max(0, (int) $fechaCreacion->diff($hoy)->days);
        }

        // Mapear códigos según tus <select> de registro
        $generos = [
            '0' => 'Masculino',
            '1' => 'Femenino'
        ];
        $estados = [
            '0' => 'Público',
            '1' => 'Privado'
        ];
        $roles = [
            '0' => 'Cliente',
            '1' => 'Especialista',
            '2' => 'Administrador'
        ];

        $generoCod = (string) ($doc['genero'] ?? '0');
        $estadoCod = (string) ($doc['estado'] ?? '0');
        $rolCod = (string) ($doc['rol'] ?? '0');

        // Normalizar ruta del avatar para que funcione desde Public/Views/
        $avatarRaw = (string) ($doc['avatar'] ?? 'img/logo2.png');
        if (str_starts_with($avatarRaw, 'http')) {
            $avatarUrl = $avatarRaw;
        } elseif (str_starts_with($avatarRaw, '../')) {
            $avatarUrl = $avatarRaw;
        } else {
            // En UsuarioController se guarda como 'foto_perfil/...' o 'img/...' dentro de Public/
            $avatarUrl = '../' . ltrim($avatarRaw, '/');
        }

        return [
            'nombre_completo' => $nombreCompleto !== '' ? $nombreCompleto : $usuario,
            'usuario' => $usuario,
            'correo' => $correo,
            'edad' => $edadTexto,
            'fecha_nacimiento_iso' => $fechaNacStr,
            'fecha_nacimiento_fmt' => $fechaFormateada,
            'genero' => $generos[$generoCod] ?? $generoCod,
            'estado' => $estados[$estadoCod] ?? $estadoCod,
            'rol' => $roles[$rolCod] ?? $rolCod,
            'avatar' => $avatarUrl,
            'dias_registrado' => $diasRegistrado,
        ];
    }

    public function obtenerUsuarioActual() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['correo'])) {
            return null;
        }

        $modelo = new UsuarioModel();
        return $modelo->obtenerPorCorreo($_SESSION['correo']);
    }

    public function editarPerfil() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['correo'])) {
            header("Location: login.html");
            exit();
        }

        $correoActual = $_SESSION['correo'];
        $modelo = new UsuarioModel();
        $usuarioActual = $modelo->obtenerPorCorreo($correoActual);

        if (!$usuarioActual) {
            header("Location: login.html");
            exit();
        }

        $nombre = trim($_POST['fname'] ?? '');
        $apP = trim($_POST['id_apP'] ?? '');
        $apM = trim($_POST['id_apM'] ?? '');
        $usuario = trim($_POST['id_us'] ?? '');
        $correoNuevo = trim($_POST['id_email'] ?? '');
        $fechaNacimiento = trim($_POST['id_fech'] ?? '');
        $genero = $_POST['pref'] ?? ($usuarioActual['genero'] ?? '0');
        $estado = $_POST['est'] ?? ($usuarioActual['estado'] ?? '0');

        $camposActualizar = [
            'nombre' => $nombre,
            'apellido_paterno' => $apP,
            'apellido_materno' => $apM,
            'usuario' => $usuario,
            'correo' => $correoNuevo,
            'fecha_nacimiento' => $fechaNacimiento,
            'genero' => $genero,
            'estado' => $estado,
        ];

        // Solo actualizar contraseña si se ingresó una nueva
        $nuevaPassword = $_POST['id_pass'] ?? '';
        if ($nuevaPassword !== '') {
            $camposActualizar['password'] = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        }

        // Solo reemplazar avatar si se subió una imagen válida
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = $_FILES['avatar']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadFileDir = __DIR__ . '/../../Public/foto_perfil/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $destPath = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $camposActualizar['avatar'] = 'foto_perfil/' . $newFileName;
                }
            }
        }

        $actualizado = $modelo->actualizarUsuario($correoActual, $camposActualizar);

        if ($actualizado) {
            $_SESSION['correo'] = $correoNuevo;
            $_SESSION['usuario'] = $usuario;
            $_SESSION['nombre'] = $nombre;
            if (isset($camposActualizar['avatar'])) {
                $_SESSION['avatar'] = $camposActualizar['avatar'];
            }

            header("Location: perfilUs.php?editado=1");
            exit();
        }

        echo "<script>alert('No se pudieron guardar los cambios en el perfil.'); window.history.back();</script>";
        exit();
    }
}