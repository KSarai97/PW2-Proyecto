<?php
namespace App\Controllers;

use App\Models\UsuarioModel;

class UsuarioController {
    
    public function registrar() {
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
            $rol = $_POST['rol'] ?? '0';

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
}