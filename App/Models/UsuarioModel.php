<?php
namespace App\Models;

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class UsuarioModel {
    private $collection;

    public function __construct() {
        // Conexión a MongoDB local
        $client = new Client("mongodb://localhost:27017");
        // Selecciona tu base de datos (por ejemplo, 'brote_psique') y tu colección 'usuarios'
        $this->collection = $client->PW2_Proyecto->Usuarios;
    }

    public function registrarUsuario($datos) {
        // Verificar si el correo ya existe
        $existente = $this->collection->findOne(['correo' => $datos['correo']]);
        if ($existente) {
            return ["exito" => false, "mensaje" => "El correo electrónico ya está registrado."];
        }

        // Insertar en MongoDB
        $resultado = $this->collection->insertOne($datos);
        
        if ($resultado->getInsertedCount() > 0) {
            return ["exito" => true, "mensaje" => "Registro exitoso."];
        }

        return ["exito" => false, "mensaje" => "No se pudo completar el registro."];
    }

    public function verificarCredenciales($correo, $passwordPlano) {
        $usuario = $this->collection->findOne(['correo' => $correo]);

        if ($usuario && password_verify($passwordPlano, $usuario['password'])) {
            return [
                "exito" => true,
                "usuario" => $usuario
            ];
        }

        return [
            "exito" => false,
            "mensaje" => "Correo electrónico o contraseña incorrectos."
        ];

        
    }

    public function obtenerPorCorreo(string $correo) {
        return $this->collection->findOne(['correo' => $correo]);
    }

    public function obtenerPorId(string $id) {
        try {
            return $this->collection->findOne(['_id' => new ObjectId($id)]);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function actualizarUsuario(string $correoActual, array $datosActualizados): bool {
        if (empty($datosActualizados)) {
            return true;
        }

        $resultado = $this->collection->updateOne(
            ['correo' => $correoActual],
            ['$set' => $datosActualizados]
        );

        return $resultado->getMatchedCount() > 0;
    }

    public function obtenerEspecialistas(): array {
        $cursor = $this->collection->find([
            'rol' => ['$in' => ['1', 1]]
        ]);

        $especialistas = [];
        foreach ($cursor as $doc) {
            $especialistas[] = $doc;
        }

        return $especialistas;
    }
}